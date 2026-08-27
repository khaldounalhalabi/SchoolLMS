<x-layouts.app :pageTitle="__('Tuition Fees')">
    <style>
        .page-header { margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; }
        .page-header h2 { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .page-header p { font-size: 13px; color: var(--text-secondary); }

        .btn-primary {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 18px; background: var(--primary); color: var(--on-primary); border: none;
            border-radius: 10px; font-size: 13px; font-weight: 600;
            font-family: var(--font-body); cursor: pointer; transition: all 0.2s;
            text-decoration: none;
        }
        .btn-primary:hover { background: var(--primary-dark); }

        .grid { display: grid; grid-template-columns: 1fr 380px; gap: 20px; }

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
        th { padding: 12px 16px; text-align: start; font-size: 11px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.8px; text-transform: uppercase; }
        th:first-child { padding-inline-start: 20px; }
        td { padding: 14px 16px; border-bottom: 1px solid var(--surface-2); font-size: 13.5px; color: var(--text-strong); vertical-align: middle; }
        td:first-child { padding-inline-start: 20px; }
        tbody tr:hover { background: var(--surface-2); }
        tbody tr:last-child td { border-bottom: none; }

        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .badge-active { background: var(--success-border); color: var(--success-dark); }
        .badge-inactive { background: var(--border-soft); color: var(--text-muted); }

        .form-card {
            background: var(--surface); border-radius: 14px;
            border: 1px solid var(--border-soft); box-shadow: var(--shadow-card);
            padding: 24px;
        }
        .form-title { font-family: var(--font-display); font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; }
        .form-group { margin-bottom: 14px; }
        .form-label { font-size: 11.5px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.7px; margin-bottom: 6px; display: block; }
        .form-input, .form-select {
            width: 100%; padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 8px;
            font-size: 13.5px; font-family: var(--font-body); color: var(--text-strong);
            background: var(--surface-3); outline: none; transition: border 0.2s; box-sizing: border-box;
        }
        .form-input:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent); }
        .checkbox-group { display: flex; align-items: center; gap: 8px; }
        .checkbox-group input { width: 18px; height: 18px; accent-color: var(--primary); }
        .checkbox-group label { font-size: 13px; color: var(--text-strong); font-weight: 500; }
        .btn-submit {
            width: 100%; padding: 10px; background: var(--primary); color: var(--on-primary); border: none;
            border-radius: 10px; font-size: 13.5px; font-weight: 600; font-family: var(--font-body);
            cursor: pointer; transition: all 0.2s; margin-top: 4px;
        }
        .btn-submit:hover { background: var(--primary-dark); }

        .empty-state { text-align: center; padding: 56px 20px; }
        .empty-icon { width: 56px; height: 56px; background: var(--border-soft); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }

        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }

        .wallet-tabs { display: flex; gap: 4px; margin-bottom: 24px; background: var(--surface); border-radius: 12px; padding: 6px; border: 1px solid var(--border-soft); width: fit-content; }
        .wallet-tab { padding: 10px 20px; border-radius: 8px; font-size: 13.5px; font-weight: 600; font-family: var(--font-body); color: var(--text-secondary); text-decoration: none; transition: all 0.2s; display: flex; align-items: center; gap: 6px; }
        .wallet-tab:hover { background: var(--surface-2); color: var(--text-primary); }
        .wallet-tab.active { background: var(--primary); color: var(--on-primary); }
        .wallet-tab svg { width: 16px; height: 16px; }
    </style>

    <div class="page-header">
        <div>
            <h2>{{ __("Tuition Fees") }}</h2>
            <p>{{ __("Set tuition fees for each academic year") }}</p>
        </div>
    </div>

    <div class="wallet-tabs">
        <a href="{{ route('admin.wallet.index') }}" class="wallet-tab">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            {{ __("Payments") }}
        </a>
        <a href="{{ route('admin.wallet.tuition-fees') }}" class="wallet-tab active">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.121 4.884 7.5 4 5 4v13c2.5 0 5.121.884 7 2.253M12 6.253C13.879 4.884 16.5 4 19 4v13c-2.5 0-5.121.884-7 2.253"/></svg>
            {{ __("Tuition Fees") }}
        </a>
        <a href="{{ route('admin.wallet.salaries') }}" class="wallet-tab">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            {{ __("Salary Transfers") }}
        </a>
    </div>

    <div class="grid">
        {{-- Existing tuition fees --}}
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">{{ __("Configured Fees") }}</div>
                <div class="table-meta">{{ __(":count academic years", ['count' => $tuitionFees->count()]) }}</div>
            </div>

            @if($tuitionFees->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="24" height="24" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div class="rtl-display" style="font-family: var(--font-display); font-size: 16px; color: var(--text-muted); margin-bottom: 6px;">{{ __("No Tuition Fees Set") }}</div>
                    <div style="font-size: 13px; color: var(--text-faint);">{{ __("Use the form to set tuition fees for academic years.") }}</div>
                </div>
            @else
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __("Academic Year") }}</th>
                                <th>{{ __("Amount") }}</th>
                                <th>{{ __("Currency") }}</th>
                                <th>{{ __("Status") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tuitionFees as $fee)
                                <tr>
                                    <td style="font-weight: 600; color: var(--text-primary);">{{ $fee->academicYear?->name ?? '—' }}</td>
                                    <td style="font-weight: 600;">{{ number_format((float) $fee->amount, 2) }}</td>
                                    <td>{{ strtoupper($fee->currency) }}</td>
                                    <td><span class="badge badge-{{ $fee->is_active ? 'active' : 'inactive' }}">{{ $fee->is_active ? __('Active') : __('Inactive') }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Create form --}}
        <div class="form-card">
            <div class="form-title">{{ __("Set Tuition Fee") }}</div>
            <form method="POST" action="{{ route('admin.wallet.tuition-fees.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">{{ __("Academic Year") }}</label>
                    <select name="academic_year_id" class="form-select" required>
                        <option value="">{{ __("Select...") }}</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __("Amount") }}</label>
                    <input type="number" name="amount" class="form-input" step="0.01" min="0.01" placeholder="5000.00" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __("Currency") }}</label>
                    <input type="text" name="currency" class="form-input" value="{{ strtoupper(config('services.stripe.currency', 'usd')) }}" maxlength="3" required>
                </div>
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <label>{{ __("Active (available for payment)") }}</label>
                    </div>
                </div>
                <button type="submit" class="btn-submit">{{ __("Save Tuition Fee") }}</button>
            </form>
        </div>
    </div>
</x-layouts.app>
