<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Log;
use Stripe\Event;

class StripeWebhookProcessor
{
    public function process(Event $event): void
    {
        match ($event->type) {
            'checkout.session.completed' => $this->checkoutCompleted($event),
            'checkout.session.expired' => $this->checkoutExpired($event),
            'payment_intent.payment_failed' => $this->paymentFailed($event),
            'charge.refunded' => $this->chargeRefunded($event),
            default => Log::info('Stripe webhook: unhandled event type - '.$event->type),
        };
    }

    private function checkoutCompleted(Event $event): void
    {
        $session = $event->data->object;

        if ($session->payment_status !== 'paid') {
            return;
        }

        $payment = Payment::where('stripe_checkout_session_id', $session->id)->first();

        if (! $payment) {
            Log::warning('Stripe webhook: payment not found for session '.$session->id);

            return;
        }

        if ($payment->status === PaymentStatus::SUCCEEDED) {
            return;
        }

        $payment->update([
            'status' => PaymentStatus::SUCCEEDED,
            'stripe_payment_intent_id' => $session->payment_intent,
            'paid_at' => now(),
        ]);

        $payment->load(['parent', 'student']);
        $payment->parent?->notify(new SystemNotification(
            'Payment completed',
            'Your payment for :student was completed successfully.',
            route('parent.payments.history'),
            'payment',
            ['student' => $payment->student?->name ?? 'your student'],
        ));
    }

    private function checkoutExpired(Event $event): void
    {
        $session = $event->data->object;
        $payment = Payment::where('stripe_checkout_session_id', $session->id)->first();

        if (! $payment || ! $payment->isPending()) {
            return;
        }

        $payment->update([
            'status' => PaymentStatus::FAILED,
            'failure_reason' => 'Checkout session expired',
        ]);
    }

    private function paymentFailed(Event $event): void
    {
        $intent = $event->data->object;
        $payment = Payment::where('stripe_payment_intent_id', $intent->id)->first();

        if (! $payment || $payment->status === PaymentStatus::SUCCEEDED) {
            return;
        }

        $payment->update([
            'status' => PaymentStatus::FAILED,
            'failure_reason' => $intent->last_payment_error?->message ?? 'Payment failed',
        ]);
    }

    private function chargeRefunded(Event $event): void
    {
        $charge = $event->data->object;
        $payment = Payment::where('stripe_payment_intent_id', $charge->payment_intent)->first();

        if (! $payment) {
            return;
        }

        $payment->update(['status' => PaymentStatus::REFUNDED]);
    }
}
