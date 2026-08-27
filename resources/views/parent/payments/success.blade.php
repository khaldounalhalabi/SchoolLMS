<x-layouts.app :pageTitle="__('Payment Success')">
    <style>
        .success-container { max-width: 560px; margin: 40px auto; text-align: center; }
        .success-icon {
            width: 80px; height: 80px; background: var(--success-border); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;
        }
        .success-icon svg { width: 40px; height: 40px; color: var(--success-dark); }
        .success-title { font-family: var(--font-display); font-size: 26px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; }
        .success-subtitle { font-size: 14px; color: var(--text-secondary); margin-bottom: 32px; }

        .receipt-card {
            background: var(--surface); border-radius: 14px; padding: 28px;
            border: 1px solid var(--border-soft); box-shadow: var(--shadow-card);
            text-align: start; margin-bottom: 24px;
        }
        .receipt-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 0; border-bottom: 1px solid var(--surface-2);
        }
        .receipt-row:last-child { border-bottom: none; }
        .receipt-label { font-size: 13px; color: var(--text-muted); font-weight: 500; }
        .receipt-value { font-size: 14px; color: var(--text-primary); font-weight: 600; }

        .receipt-total {
            display: flex; justify-content: space-between; align-items: center;
            padding: 16px 0 4px; margin-top: 8px; border-top: 2px solid var(--border-soft);
        }
        .receipt-total .receipt-label { font-size: 14px; font-weight: 700; color: var(--text-primary); }
        .receipt-total .receipt-value { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--primary); }

        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .badge-succeeded { background: var(--success-border); color: var(--success-dark); }
        .badge-pending { background: var(--warning-tint); color: var(--warning-text); }

        .btn-actions { display: flex; gap: 12px; justify-content: center; }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 11px 24px; background: var(--primary); color: var(--on-primary); border: none;
            border-radius: 10px; font-size: 13.5px; font-weight: 600;
            font-family: var(--font-body); cursor: pointer; transition: all 0.2s;
            text-decoration: none;
        }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 11px 24px; background: var(--surface-2); color: var(--text-secondary); border: 1px solid var(--border);
            border-radius: 10px; font-size: 13.5px; font-weight: 600;
            font-family: var(--font-body); cursor: pointer; transition: all 0.2s;
            text-decoration: none;
        }
        .btn-secondary:hover { background: var(--border-soft); }

        .pending-note {
            background: var(--warning-tint); border: 1px solid var(--warning-border);
            padding: 14px 18px; border-radius: 12px; margin-bottom: 24px;
            font-size: 13px; color: var(--warning-text); text-align: center;
        }
    </style>

    <div class="success-container">
        <div class="success-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div class="success-title">{{ __("Payment Submitted!") }}</div>
        <div class="success-subtitle">
            @if($payment->isSucceeded())
                {{ __("Your payment has been confirmed successfully.") }}
            @else
                {{ __("Your payment is being processed. You will receive confirmation shortly.") }}
            @endif
        </div>

        @if(!$payment->isSucceeded())
            <div class="pending-note">
                {{ __("Payment status:") }}
                <span class="badge badge-{{ $payment->status->value }}">{{ __(ucfirst($payment->status->value)) }}</span>
                <br><br>
                {{ __("We are waiting for confirmation from the payment provider. This usually takes a few moments.") }}
            </div>
        @endif

        <div class="receipt-card">
            <div class="receipt-row">
                <span class="receipt-label">{{ __("Academic Year") }}</span>
                <span class="receipt-value">{{ $payment->academicYear?->name }}</span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">{{ __("Student") }}</span>
                <span class="receipt-value">{{ $payment->student?->name ?? '—' }}</span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">{{ __("Date") }}</span>
                <span class="receipt-value">{{ $payment->created_at?->format('M j, Y H:i') }}</span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">{{ __("Status") }}</span>
                <span class="receipt-value"><span class="badge badge-{{ $payment->status->value }}">{{ __(ucfirst($payment->status->value)) }}</span></span>
            </div>
            <div class="receipt-total">
                <span class="receipt-label">{{ __("Total") }}</span>
                <span class="receipt-value">{{ strtoupper($payment->currency) }} {{ number_format((float) $payment->amount, 2) }}</span>
            </div>
        </div>

        <div class="btn-actions">
            <a href="{{ route('parent.payments.history') }}" class="btn-primary">{{ __("View Payment History") }}</a>
            <a href="{{ route('parent.payments.index') }}" class="btn-secondary">{{ __("Back to Payments") }}</a>
        </div>
    </div>
</x-layouts.app>
