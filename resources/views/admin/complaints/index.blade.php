<x-layouts.app :pageTitle="__('Complaints')">
    <style>
        .card { background: var(--surface); border: 1px solid var(--border-soft); border-radius: 14px; box-shadow: var(--shadow-card); overflow: hidden; }
        .toolbar { padding: 16px 20px; border-bottom: 1px solid var(--border-soft); display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .complaint-item { padding: 20px; border-bottom: 1px solid var(--border-soft); }
        .complaint-item:last-child { border-bottom: 0; }
        .meta { font-size: 12px; color: var(--text-muted); margin-top: 5px; }
        .status { display: inline-flex; padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--warning-tint); color: var(--warning-text); }
        .status-resolved { background: var(--success-tint); color: var(--success-text); }
        .status-rejected { background: var(--danger-tint); color: var(--danger-text); }
        .review-form { margin-top: 16px; padding: 14px; border-radius: 10px; background: var(--surface-2); }
        .empty-state { padding: 52px 20px; text-align: center; color: var(--text-faint); }
    </style>

    <div class="page-actions">
        <div>
            <div class="page-title">{{ __('Complaints') }}</div>
            <div class="page-desc">{{ __('Review and respond to complaints submitted by users.') }}</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="toolbar">
            <div style="font-weight: 700; color: var(--text-primary);">{{ __('All Complaints') }}</div>
            <form method="GET" action="{{ route('admin.complaints.index') }}">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach(['pending' => __('Pending'), 'in_review' => __('In Review'), 'resolved' => __('Resolved'), 'rejected' => __('Rejected')] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @forelse($complaints as $complaint)
            <div class="complaint-item">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                    <div>
                        <div style="font-weight: 700; color: var(--text-primary);">{{ $complaint->subject }}</div>
                        <div class="meta">
                            {{ $complaint->submitter->name }} · {{ $complaint->submitter->email }} ·
                            {{ __(ucfirst($complaint->category)) }} · {{ __(ucfirst($complaint->priority)) }} · {{ $complaint->created_at->format('M d, Y H:i') }}
                        </div>
                    </div>
                    <span class="status {{ $complaint->status === 'resolved' ? 'status-resolved' : ($complaint->status === 'rejected' ? 'status-rejected' : '') }}">{{ __(ucwords(str_replace('_', ' ', $complaint->status))) }}</span>
                </div>
                <div style="margin-top: 12px; color: var(--text-secondary); font-size: 13px; white-space: pre-line;">{{ $complaint->description }}</div>

                <form method="POST" action="{{ route('admin.complaints.review', $complaint) }}" class="review-form">
                    @csrf
                    <div class="form-row">
                        <x-ui.form.field name="status" :label="__('Status')" required>
                            <select name="status" class="form-control" required>
                                @foreach(['in_review' => __('In Review'), 'resolved' => __('Resolved'), 'rejected' => __('Rejected')] as $value => $label)
                                    <option value="{{ $value }}" @selected($complaint->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-ui.form.field>
                        <div></div>
                    </div>
                    <x-ui.form.field name="admin_response" type="textarea" :label="__('Admin Response')" :value="old('admin_response', $complaint->admin_response)" required />
                    <div style="display: flex; justify-content: flex-end; margin-top: 8px;">
                        <button type="submit" class="btn btn-primary">{{ __('Save Review') }}</button>
                    </div>
                </form>
            </div>
        @empty
            <div class="empty-state">{{ __('No complaints found.') }}</div>
        @endforelse

        @if($complaints->hasPages())
            <div style="padding: 14px 20px; border-top: 1px solid var(--border-soft);">{{ $complaints->links() }}</div>
        @endif
    </div>
</x-layouts.app>
