<x-layouts.app :pageTitle="__('Payment History')">
    <style>
        .page-header { margin-bottom: 24px; }
        .page-header h2 { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .page-header p { font-size: 13px; color: var(--text-secondary); }

        .table-card { background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft); box-shadow: var(--shadow-card); overflow: hidden; }
        .table-header { padding: 20px; border-bottom: 1px solid var(--border-soft); }
        .table-title { font-family: var(--font-display); font-size: 16px; font-weight: 700; color: var(--text-primary); }
        .table-meta { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

        table { width: 100%; border-collapse: collapse; }
        thead tr { background: var(--surface-2); }
        th { padding: 12px 16px; text-align: start; font-size: 11px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.8px; text-transform: uppercase; }
        th:first-child { padding-inline-start: 20px; }
        td { padding: 14px 16px; border-bottom: 1px solid var(--surface-2); font-size: 13.5px; color: var(--text-strong); vertical-align: middle; }
        td:first-child { padding-inline-start: 20px; }
        tbody tr:hover { background: var(--surface-2); }
        tbody tr:last-child td { border-bottom: none; }

        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .badge-succeeded { background: var(--success-border); color: var(--success-dark); }
        .badge-pending { background: var(--warning-tint); color: var(--warning-text); }
        .badge-failed { background: var(--danger-tint); color: var(--danger-dark); }
        .badge-refunded { background: var(--primary-tint); color: var(--primary); }

        .empty-state { text-align: center; padding: 56px 20px; }
        .empty-icon { width: 56px; height: 56px; background: var(--border-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }

        .pagination-row { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-top: 1px solid var(--border-soft); font-size: 12px; color: var(--text-secondary); }

        .back-link { margin-bottom: 16px; }
        .back-link a { font-size: 13px; color: var(--text-secondary); text-decoration: none; font-weight: 500; }
        .back-link a:hover { color: var(--primary); }
    </style>

    <div class="back-link">
        <a href="{{ route('parent.payments.index') }}">&larr; {{ __("Back to Payments") }}</a>
    </div>

    <div class="page-header">
        <h2>{{ __("Payment History") }}</h2>
        <p>{{ __("View all your payment receipts and their statuses") }}</p>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">{{ __("Your Payments") }}</div>
            <div class="table-meta">{{ __(":count payments", ['count' => $payments->total()]) }}</div>
        </div>

        @if($payments->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="24" height="24" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div class="rtl-display" style="font-family: var(--font-display); font-size: 16px; color: var(--text-muted); margin-bottom: 6px;">{{ __("No Payments Yet") }}</div>
                <div style="font-size: 13px; color: var(--text-faint);">{{ __("You haven't made any payments yet.") }}</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __("Academic Year") }}</th>
                            <th>{{ __("Student") }}</th>
                            <th>{{ __("Amount") }}</th>
                            <th>{{ __("Date") }}</th>
                            <th>{{ __("Status") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td style="font-weight: 600; color: var(--text-primary);">{{ $payment->academicYear?->name ?? '—' }}</td>
                                <td>{{ $payment->student?->name ?? '—' }}</td>
                                <td style="font-weight: 600;">{{ strtoupper($payment->currency) }} {{ number_format((float) $payment->amount, 2) }}</td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);">{{ $payment->created_at?->format('M j, Y') }}</div>
                                    <div style="font-size: 11.5px; color: var(--text-muted);">{{ $payment->created_at?->format('H:i') }}</div>
                                </td>
                                <td><span class="badge badge-{{ $payment->status->value }}">{{ __(ucfirst($payment->status->value)) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="pagination-row">
                    <div>{{ __("Page :current of :last", ['current' => $payments->currentPage(), 'last' => $payments->lastPage()]) }}</div>
                    <div style="display: flex; gap: 6px;">
                        @if($payments->onFirstPage())
                            <span style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); color: var(--text-faint); font-size: 12px; font-weight: 600;">&larr; {{ __("Prev") }}</span>
                        @else
                            <a href="{{ $payments->previousPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); border: 1px solid var(--border); color: var(--text-strong); text-decoration: none; font-size: 12px; font-weight: 600;">&larr; {{ __("Prev") }}</a>
                        @endif
                        @if($payments->hasMorePages())
                            <a href="{{ $payments->nextPageUrl() }}" style="padding: 6px 12px; border-radius: 6px; background: var(--primary); color: var(--on-primary); text-decoration: none; font-size: 12px; font-weight: 600;">{{ __("Next") }} &rarr;</a>
                        @else
                            <span style="padding: 6px 12px; border-radius: 6px; background: var(--surface-2); color: var(--text-faint); font-size: 12px; font-weight: 600;">{{ __("Next") }} &rarr;</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-layouts.app>
