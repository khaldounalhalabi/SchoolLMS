<x-layouts.app :pageTitle="__('Payments')">
    <style>
        .page-header { margin-bottom: 24px; }
        .page-header h2 { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .page-header p { font-size: 13px; color: var(--text-secondary); }

        .info-banner {
            display: flex; align-items: center; gap: 10px;
            background: var(--primary-tint); border: 1px solid var(--primary-light);
            padding: 14px 18px; border-radius: 12px; margin-bottom: 24px;
            font-size: 13px; color: var(--primary-dark);
        }
        .info-banner svg { width: 20px; height: 20px; flex-shrink: 0; }

        .fees-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(360px, 100%), 1fr)); gap: 16px; }

        .fee-card {
            background: var(--surface); border-radius: 14px; padding: 24px;
            border: 1px solid var(--border-soft); box-shadow: var(--shadow-card);
            transition: all 0.2s;
        }
        .fee-card:hover { border-color: var(--primary-light); box-shadow: 0 4px 14px color-mix(in srgb, var(--primary) 8%, transparent); }

        .fee-year { font-family: var(--font-display); font-size: 18px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .fee-dates { font-size: 12px; color: var(--text-muted); margin-bottom: 20px; }

        .fee-amount-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 20px; }
        .fee-amount { font-family: var(--font-display); font-size: 32px; font-weight: 700; color: var(--primary); }
        .fee-currency { font-size: 14px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; }

        .child-select-group { margin-bottom: 16px; }
        .child-select-label { font-size: 11.5px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.7px; margin-bottom: 6px; display: block; }
        .child-select {
            width: 100%; padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13.5px; font-family: var(--font-body); color: var(--text-strong);
            background: var(--surface-3); outline: none; transition: border 0.2s; box-sizing: border-box;
            cursor: pointer;
        }
        .child-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent); }

        .fee-status {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 500;
            margin-bottom: 16px;
        }
        .fee-status-paid { background: var(--success-border); color: var(--success-dark); }
        .fee-status-pending { background: var(--warning-tint); color: var(--warning-text); }
        .fee-status-available { background: var(--surface-2); color: var(--text-secondary); }

        .btn-pay {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 12px; background: var(--primary); color: var(--on-primary); border: none;
            border-radius: 10px; font-size: 14px; font-weight: 600;
            font-family: var(--font-body); cursor: pointer; transition: all 0.2s;
            text-decoration: none;
        }
        .btn-pay:hover { background: var(--primary-dark); }
        .btn-pay:disabled { background: var(--border); color: var(--text-muted); cursor: not-allowed; }
        .btn-pay svg { width: 18px; height: 18px; }

        .btn-history {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 8px 16px; background: var(--primary-tint); color: var(--primary-dark); border: 1px solid var(--primary-light);
            border-radius: 8px; font-size: 12.5px; font-weight: 600;
            font-family: var(--font-body); text-decoration: none; transition: all 0.2s;
        }
        .btn-history:hover { background: var(--primary-dark); color: var(--on-primary); }

        .empty-state { text-align: center; padding: 80px 20px; color: var(--text-faint); background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft); }
        .empty-state svg { margin-bottom: 16px; }
        .empty-state h3 { font-family: var(--font-display); font-size: 18px; color: var(--text-muted); margin-bottom: 8px; }
        .empty-state p { font-size: 13px; color: var(--text-faint); }

        .history-link { text-align: center; margin-top: 24px; }

        .test-banner {
            display: flex; align-items: center; gap: 10px;
            background: var(--warning-tint); border: 1px solid var(--warning-border);
            padding: 14px 18px; border-radius: 12px; margin-bottom: 24px;
            font-size: 13px; color: var(--warning-text);
        }
        .test-banner svg { width: 20px; height: 20px; flex-shrink: 0; }

        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: var(--overlay); z-index: 1000;
            align-items: center; justify-content: center; padding: 20px;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: var(--surface); border-radius: 16px; padding: 32px;
            max-width: 440px; width: 100%; box-shadow: var(--shadow-modal);
            /* Scroll inside the modal instead of off the viewport. */
            max-height: calc(100dvh - 40px); overflow-y: auto;
        }
        @media (max-width: 520px) { .modal { padding: 24px 20px; } }
        .modal-title { font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .modal-subtitle { font-size: 13px; color: var(--text-secondary); margin-bottom: 24px; }
        .modal-summary {
            background: var(--surface-2); border-radius: 10px; padding: 16px;
            margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;
        }
        .modal-summary-label { font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .modal-summary-value { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--primary); }
        .modal-summary-currency { font-size: 13px; color: var(--text-muted); font-weight: 600; }

        .form-group { margin-bottom: 14px; }
        .form-label { font-size: 11.5px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.7px; margin-bottom: 6px; display: block; }
        .form-input {
            width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 14px; font-family: var(--font-body); color: var(--text-strong);
            background: var(--surface-3); outline: none; transition: border 0.2s; box-sizing: border-box;
        }
        .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent); }
        .form-row { display: flex; gap: 12px; }
        .form-row .form-group { flex: 1; }
        .test-card-hint {
            font-size: 11.5px; color: var(--text-muted); margin-top: 4px; padding: 8px 12px;
            background: var(--success-tint); border: 1px solid var(--success-border); border-radius: 6px; color: var(--success-dark);
        }
        .modal-actions { display: flex; gap: 12px; margin-top: 20px; }
        .btn-submit {
            flex: 1; padding: 12px; background: var(--primary); color: var(--on-primary); border: none;
            border-radius: 10px; font-size: 14px; font-weight: 600;
            font-family: var(--font-body); cursor: pointer; transition: all 0.2s;
        }
        .btn-submit:hover { background: var(--primary-dark); }
        .btn-cancel {
            padding: 12px 20px; background: var(--surface-2); color: var(--text-secondary); border: 1px solid var(--border);
            border-radius: 10px; font-size: 14px; font-weight: 600;
            font-family: var(--font-body); cursor: pointer; transition: all 0.2s;
        }
        .btn-cancel:hover { background: var(--border-soft); }
    </style>

    <div
        data-payment-page
        data-checkout-template="{{ route('parent.payments.checkout', '__FEE__') }}"
        data-test-process-template="{{ route('parent.payments.test-process', '__FEE__') }}"
        data-paid-label="{{ __('Paid successfully') }}"
        data-pending-label="{{ __('Payment in progress...') }}"
        data-complete-label="{{ __('Complete Payment') }}"
        data-awaiting-label="{{ __('Awaiting payment') }}"
        data-pay-label="{{ __('Pay Now') }}"
    >
    <div class="page-header">
        <h2>{{ __("Tuition Payments") }}</h2>
        <p>{{ __("Pay tuition fees securely via Stripe") }}</p>
    </div>

    <div class="test-banner">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ __("Test Mode: Payments are simulated. Use card number 4242 4242 4242 4242, any future date, and any CVC.") }}</span>
    </div>

    @if($academicYears->isEmpty())
        <div class="empty-state">
            <svg width="56" height="56" fill="none" stroke="var(--border)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <h3>{{ __("No Tuition Fees Available") }}</h3>
            <p>{{ __("There are no academic years with tuition fees configured yet. Please check back later.") }}</p>
        </div>
    @else
        <div class="fees-grid">
            @foreach($academicYears as $year)
                @php
                    $fee = $year->tuitionFee;
                @endphp
                @if($fee)
                    <div class="fee-card">
                        <div class="fee-year">{{ $year->name }}</div>
                        <div class="fee-dates">{{ $year->start_date?->format('M Y') }} — {{ $year->end_date?->format('M Y') }}</div>

                        <div class="fee-amount-row">
                            <span class="fee-amount">{{ number_format((float) $fee->amount, 2) }}</span>
                            <span class="fee-currency">{{ strtoupper($fee->currency) }}</span>
                        </div>

                        @if($children->isEmpty())
                            <div class="fee-status fee-status-available">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                                {{ __("No children linked to your account") }}
                            </div>
                        @else
                            <div class="child-select-group">
                                <label class="child-select-label">{{ __("Pay for student") }}</label>
                                <select class="child-select" data-fee-id="{{ $fee->id }}">
                                    @foreach($children as $child)
                                        @php
                                            $key = $year->id . '-' . $child->id;
                                            $existingPayment = $existingPayments->get($key);
                                            $childStatus = $existingPayment?->status?->value ?? 'available';
                                        @endphp
                                        <option value="{{ $child->id }}" data-status="{{ $childStatus }}" data-name="{{ $child->name }}">
                                            {{ $child->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="status-box-{{ $fee->id }}" class="fee-status fee-status-available">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <span id="status-text-{{ $fee->id }}">{{ __("Awaiting payment") }}</span>
                            </div>

                            <button id="pay-btn-{{ $fee->id }}"
                               type="button"
                               data-year="{{ $year->name }}"
                               data-amount="{{ number_format((float) $fee->amount, 2, '.', '') }}"
                               data-currency="{{ strtoupper($fee->currency) }}"
                               data-student-id="{{ $children->first()->id }}"
                               data-student-name="{{ $children->first()->name }}"
                               class="btn-pay">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                <span id="pay-label-{{ $fee->id }}">{{ __("Pay Now") }}</span>
                            </button>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>

        <div class="history-link">
            <a href="{{ route('parent.payments.history') }}" class="btn-history" style="padding: 10px 24px; font-size: 13px;">
                {{ __("View Payment History") }}
            </a>
        </div>
    @endif

@if(app()->environment(['local', 'testing']) && config('services.stripe.test_mode'))
    {{-- Test Payment Modal --}}
    <div class="modal-overlay" id="payment-modal">
        <div class="modal">
            <div class="modal-title">{{ __("Complete Payment") }}</div>
            <div class="modal-subtitle" id="modal-subtitle"></div>

            <div class="modal-summary">
                <div>
                    <div class="modal-summary-label">{{ __("Amount Due") }}</div>
                    <div><span class="modal-summary-value" id="modal-amount"></span> <span class="modal-summary-currency" id="modal-currency"></span></div>
                </div>
            </div>

            <form id="test-payment-form" method="POST" action="">
                @csrf
                <input type="hidden" name="student_user_id" id="modal-student-id">

                <div class="form-group">
                    <label class="form-label">{{ __("Card Number") }}</label>
                    <input type="text" class="form-input" id="card-number" placeholder="4242 4242 4242 4242" maxlength="19" value="4242 4242 4242 4242">
                    <div class="test-card-hint">{{ __("Test card: 4242 4242 4242 4242") }}</div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __("Expiry") }}</label>
                        <input type="text" class="form-input" placeholder="12/30" maxlength="5" value="12/30">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __("CVC") }}</label>
                        <input type="text" class="form-input" placeholder="123" maxlength="4" value="123">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" id="cancel-payment-modal">{{ __("Cancel") }}</button>
                    <button type="submit" class="btn-submit">{{ __("Pay Now") }}</button>
                </div>
            </form>
        </div>
    </div>

@endif
</div>
</x-layouts.app>
