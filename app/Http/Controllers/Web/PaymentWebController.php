<?php

namespace App\Http\Controllers\Web;

use App\Data\PaymentCheckoutData;
use App\Enums\PaymentCheckoutStatus;
use App\Http\Controllers\Controller;
use App\Models\TuitionFee;
use App\Services\Payment\ParentPaymentQueryService;
use App\Services\Payment\PaymentCheckoutService;
use App\Services\Payment\PaymentGatewayException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentWebController extends Controller
{
    public function __construct(
        private ParentPaymentQueryService $queries,
        private PaymentCheckoutService $checkout,
    ) {}

    public function index(): View
    {
        $data = $this->queries->index(Auth::user());
        $data['currency'] = config('services.stripe.currency', 'usd');

        return view('parent.payments.index', $data);
    }

    public function checkout(Request $request, TuitionFee $tuitionFee): RedirectResponse
    {
        try {
            $result = $this->checkout->start(
                Auth::user(),
                $tuitionFee,
                new PaymentCheckoutData(
                    studentId: $request->query('student'),
                    successUrl: route('parent.payments.success').'?session_id={CHECKOUT_SESSION_ID}',
                    cancelUrl: route('parent.payments.index'),
                ),
            );
        } catch (PaymentGatewayException) {
            return redirect()->route('parent.payments.index')
                ->with('error', __('Unable to initiate payment. Please try again later.'));
        }

        return match ($result->status) {
            PaymentCheckoutStatus::INACTIVE_FEE => redirect()
                ->route('parent.payments.index')
                ->with('error', __('This tuition fee is no longer available.')),
            PaymentCheckoutStatus::INVALID_CHILD => redirect()
                ->route('parent.payments.index')
                ->with('error', __('Please select a valid student to pay for.')),
            PaymentCheckoutStatus::ALREADY_PAID => redirect()
                ->route('parent.payments.history')
                ->with('error', __('You have already paid for :name for this academic year.', [
                    'name' => $result->childName,
                ])),
            PaymentCheckoutStatus::PENDING,
            PaymentCheckoutStatus::CREATED => redirect()->away($result->url),
        };
    }

    public function testProcess(Request $request, TuitionFee $tuitionFee): RedirectResponse
    {
        abort_unless(
            app()->environment(['local', 'testing']) && config('services.stripe.test_mode'),
            404,
        );

        $validated = $request->validate([
            'student_user_id' => ['required', 'integer'],
        ]);

        $result = $this->checkout->createTestPayment(
            Auth::user(),
            $tuitionFee,
            (int) $validated['student_user_id'],
        );

        return match ($result->status) {
            PaymentCheckoutStatus::INACTIVE_FEE => redirect()
                ->route('parent.payments.index')
                ->with('error', __('This tuition fee is no longer available.')),
            PaymentCheckoutStatus::INVALID_CHILD => redirect()
                ->route('parent.payments.index')
                ->with('error', __('Invalid student selection.')),
            PaymentCheckoutStatus::ALREADY_PAID => redirect()
                ->route('parent.payments.history')
                ->with('error', __('You have already paid for :name for this academic year.', [
                    'name' => $result->childName,
                ])),
            PaymentCheckoutStatus::CREATED => redirect()->route('parent.payments.success', [
                'session_id' => $result->sessionId,
            ]),
            default => redirect()->route('parent.payments.index'),
        };
    }

    public function success(Request $request): View|RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('parent.payments.index');
        }

        $payment = $this->queries->success(Auth::user(), $sessionId);

        if (! $payment) {
            return redirect()->route('parent.payments.index')
                ->with('error', __('Payment session not found.'));
        }

        return view('parent.payments.success', compact('payment'));
    }

    public function history(): View
    {
        $parent = Auth::user();
        $payments = $this->queries->history($parent);
        $currency = config('services.stripe.currency', 'usd');

        return view('parent.payments.history', compact('parent', 'payments', 'currency'));
    }
}
