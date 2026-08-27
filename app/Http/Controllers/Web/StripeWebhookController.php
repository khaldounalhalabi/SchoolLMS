<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Payment\StripeService;
use App\Services\Payment\StripeWebhookProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function __construct(
        private StripeService $stripeService,
        private StripeWebhookProcessor $processor,
    ) {}

    public function handle(Request $request): Response
    {
        $signature = $request->header('Stripe-Signature');

        if (! $signature) {
            return response('Missing signature', 400);
        }

        try {
            $event = $this->stripeService->constructWebhookEvent(
                $request->getContent(),
                $signature,
            );
        } catch (\UnexpectedValueException $exception) {
            Log::warning('Stripe webhook: invalid payload - '.$exception->getMessage());

            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $exception) {
            Log::warning('Stripe webhook: signature verification failed - '.$exception->getMessage());

            return response('Invalid signature', 400);
        }

        try {
            $this->processor->process($event);
        } catch (\Throwable $exception) {
            Log::error('Stripe webhook processing error: '.$exception->getMessage());

            return response('Webhook processing error', 500);
        }

        return response('Webhook handled', 200);
    }
}
