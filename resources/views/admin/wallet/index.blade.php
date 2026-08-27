<x-layouts.app :pageTitle="__('Wallet')">
    <style>
        .page-header { margin-bottom: 24px; }
        .page-header h2 { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .page-header p { font-size: 13px; color: var(--text-secondary); }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(240px, 100%), 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: var(--surface); border-radius: 14px; padding: 20px;
            border: 1px solid var(--border-soft); box-shadow: var(--shadow-card);
        }
        .stat-label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px; }
        .stat-value { font-family: var(--font-display); font-size: 26px; font-weight: 700; color: var(--text-primary); }
        .stat-card.success .stat-value { color: var(--success-dark); }
        .stat-card.pending .stat-value { color: var(--warning-dark); }
        .stat-card.failed .stat-value { color: var(--danger-dark); }

        .filter-bar {
            background: var(--surface); border-radius: 14px;
            border: 1px solid var(--border-soft); box-shadow: var(--shadow-card);
            padding: 16px 20px; margin-bottom: 20px;
            display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;
        }
        .filter-group { display: flex; flex-direction: column; gap: 4px; }
        .filter-label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .filter-select, .filter-input {
            padding: 8px 12px; border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13px; font-family: var(--font-body); color: var(--text-strong);
            background: var(--surface-3); outline: none; transition: border 0.2s; min-width: 160px;
        }
        .filter-select:focus, .filter-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent); }
        .btn-filter {
            padding: 8px 18px; background: var(--primary); color: var(--on-primary); border: none;
            border-radius: 8px; font-size: 13px; font-weight: 600;
            font-family: var(--font-body); cursor: pointer; transition: all 0.2s;
        }
        .btn-filter:hover { background: var(--primary-dark); }
        .btn-clear {
            padding: 8px 14px; font-size: 12.5px; color: var(--text-secondary); text-decoration: none; font-weight: 500;
        }

        .table-card {
            background: var(--surface); border-radius: 14px;
            border: 1px solid var(--border-soft); box-shadow: var(--shadow-card);
            overflow: hidden;
        }
        .table-header { padding: 20px; border-bottom: 1px solid var(--border-soft); }
        .table-title { font-family: var(--font-display); font-size: 16px; font-weight: 700; color: var(--text-primary); }
        .table-meta { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

        table { width: 100%; border-collapse: collapse; }
        thead tr { background: var(--surface-2); }
        th {
            padding: 12px 16px; text-align: start;
            font-size: 11px; font-weight: 700; color: var(--text-muted);
            letter-spacing: 0.8px; text-transform: uppercase;
        }
        th:first-child { padding-inline-start: 20px; }
        td {
            padding: 14px 16px; border-bottom: 1px solid var(--surface-2);
            font-size: 13.5px; color: var(--text-strong); vertical-align: middle;
        }
        td:first-child { padding-inline-start: 20px; }
        tbody tr:hover { background: var(--surface-2); }
        tbody tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11.5px; font-weight: 600;
        }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .badge-succeeded { background: var(--success-border); color: var(--success-dark); }
        .badge-pending   { background: var(--warning-tint); color: var(--warning-text); }
        .badge-failed    { background: var(--danger-tint); color: var(--danger-dark); }
        .badge-refunded  { background: var(--primary-tint); color: var(--primary); }

        .empty-state { text-align: center; padding: 56px 20px; }
        .empty-icon {
            width: 56px; height: 56px; background: var(--border-soft); border-radius: 50%;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;
        }

        .pagination-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 16px 20px; border-top: 1px solid var(--border-soft);
            font-size: 12px; color: var(--text-secondary);
        }

        .wallet-tabs { display: flex; gap: 4px; margin-bottom: 24px; background: var(--surface); border-radius: 12px; padding: 6px; border: 1px solid var(--border-soft); width: fit-content; }
        .wallet-tab { padding: 10px 20px; border-radius: 8px; font-size: 13.5px; font-weight: 600; font-family: var(--font-body); color: var(--text-secondary); text-decoration: none; transition: all 0.2s; display: flex; align-items: center; gap: 6px; }
        .wallet-tab:hover { background: var(--surface-2); color: var(--text-primary); }
        .wallet-tab.active { background: var(--primary); color: var(--on-primary); }
        .wallet-tab svg { width: 16px; height: 16px; }
    </style>

    <div class="page-header">
        <h2>{{ __("Payment Wallet") }}</h2>
        <p>{{ __("View all payments made by parents and track collected amounts") }}</p>
    </div>

    <div class="wallet-tabs">
        <a href="{{ route('admin.wallet.index') }}" class="wallet-tab active">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            {{ __("Payments") }}
        </a>
        <a href="{{ route('admin.wallet.tuition-fees') }}" class="wallet-tab">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.121 4.884 7.5 4 5 4v13c2.5 0 5.121.884 7 2.253M12 6.253C13.879 4.884 16.5 4 19 4v13c-2.5 0-5.121.884-7 2.253"/></svg>
            {{ __("Tuition Fees") }}
        </a>
        <a href="{{ route('admin.wallet.salaries') }}" class="wallet-tab">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            {{ __("Salary Transfers") }}
        </a>
    </div>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card success">
            <div class="stat-label">{{ __("Total Collected") }}</div>
            <div class="stat-value">{{ strtoupper($currency) }} {{ number_format((float) $totalCollected, 2) }}</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-label">{{ __("Pending Payments") }}</div>
            <div class="stat-value">{{ strtoupper($currency) }} {{ number_format((float) $totalPending, 2) }}</div>
        </div>
        <div class="stat-card failed">
            <div class="stat-label">{{ __("Failed Payments") }}</div>
            <div class="stat-value">{{ strtoupper($currency) }} {{ number_format((float) $totalFailed, 2) }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.wallet.index') }}">
        <div class="filter-bar">
            <div class="filter-group">
                <label class="filter-label">{{ __("Search") }}</label>
                <input type="text" name="search" class="filter-input" placeholder="{{ __('Parent or student name...') }}" value="{{ request('search') }}">
            </div>
            <div class="filter-group">
                <label class="filter-label">{{ __("Status") }}</label>
                <select name="status" class="filter-select">
                    <option value="">{{ __("All") }}</option>
                    <option value="succeeded" @selected(request('status') === 'succeeded')>{{ __("Succeeded") }}</option>
                    <option value="pending" @selected(request('status') === 'pending')>{{ __("Pending") }}</option>
                    <option value="failed" @selected(request('status') === 'failed')>{{ __("Failed") }}</option>
                    <option value="refunded" @selected(request('status') === 'refunded')>{{ __("Refunded") }}</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">{{ __("From Date") }}</label>
                <input type="date" name="date_from" class="filter-input" value="{{ request('date_from') }}">
            </div>
            <div class="filter-group">
                <label class="filter-label">{{ __("To Date") }}</label>
                <input type="date" name="date_to" class="filter-input" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn-filter">{{ __("Filter") }}</button>
            @if(request('search') || request('status') || request('date_from') || request('date_to'))
                <a href="{{ route('admin.wallet.index') }}" class="btn-clear">{{ __("Clear") }}</a>
            @endif
        </div>
    </form>

    {{-- Payments Table --}}
    <div class="table-card">
        <div class="table-header">
            <div class="table-title">{{ __("All Payments") }}</div>
            <div class="table-meta">{{ __(":count payments", ['count' => $payments->total()]) }}</div>
        </div>

        @if($payments->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="24" height="24" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div class="rtl-display" style="font-family: var(--font-display); font-size: 16px; color: var(--text-muted); margin-bottom: 6px;">{{ __("No Payments Found") }}</div>
                <div style="font-size: 13px; color: var(--text-faint);">{{ __("No payments have been made yet.") }}</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __("Parent") }}</th>
                            <th>{{ __("Student") }}</th>
                            <th>{{ __("Academic Year") }}</th>
                            <th>{{ __("Amount") }}</th>
                            <th>{{ __("Date") }}</th>
                            <th>{{ __("Status") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary);">{{ $payment->parent?->name ?? '—' }}</div>
                                    <div style="font-size: 11.5px; color: var(--text-muted);">{{ $payment->parent?->email ?? '' }}</div>
                                </td>
                                <td>{{ $payment->student?->name ?? '—' }}</td>
                                <td>{{ $payment->academicYear?->name ?? '—' }}</td>
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
