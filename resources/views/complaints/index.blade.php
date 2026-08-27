<x-layouts.app :pageTitle="__('Complaints')">
    <style>
        .card { background: var(--surface); border: 1px solid var(--border-soft); border-radius: 14px; box-shadow: var(--shadow-card); overflow: hidden; margin-bottom: 24px; }
        .card-header { padding: 18px 20px; border-bottom: 1px solid var(--border-soft); font-weight: 700; color: var(--text-primary); }
        .card-body { padding: 20px; }
        .complaint-item { padding: 18px 20px; border-bottom: 1px solid var(--border-soft); }
        .complaint-item:last-child { border-bottom: 0; }
        .meta { font-size: 12px; color: var(--text-muted); margin-top: 5px; }
        .status { display: inline-flex; padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 700; background: var(--warning-tint); color: var(--warning-text); }
        .status-resolved { background: var(--success-tint); color: var(--success-text); }
        .status-rejected { background: var(--danger-tint); color: var(--danger-text); }
        .response { margin-top: 12px; padding: 12px; border-radius: 9px; background: var(--surface-2); font-size: 13px; color: var(--text-secondary); }
        .empty-state { padding: 48px 20px; text-align: center; color: var(--text-faint); }
    </style>

    <div class="page-header">
        <div>
            <div class="page-title">{{ __('Complaints') }}</div>
            <div class="page-desc">{{ __('Submit a complaint and follow its review status.') }}</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">{{ __('Submit a Complaint') }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('complaints.store') }}">
                @csrf
                <div class="form-row">
                    <x-ui.form.field name="subject" :label="__('Complaint Subject')" :placeholder="__('Briefly describe the issue')" required />
                    <x-ui.form.field name="category" :label="__('Category')" required>
                        <select name="category" class="form-control" required>
                            <option value="">{{ __('Select category') }}</option>
                            @foreach(['academic' => __('Academic'), 'behavior' => __('Behavior'), 'technical' => __('Technical'), 'financial' => __('Financial'), 'general' => __('General')] as $value => $label)
                                <option value="{{ $value }}" @selected(old('category', 'general') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-ui.form.field>
                </div>
                <div class="form-row">
                    <x-ui.form.field name="priority" :label="__('Priority')" required>
                        <select name="priority" class="form-control" required>
                            @foreach(['low' => __('Low'), 'normal' => __('Normal'), 'high' => __('High'), 'urgent' => __('Urgent')] as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-ui.form.field>
                    <div></div>
                </div>
                <x-ui.form.field name="description" type="textarea" :label="__('Description')" :placeholder="__('Explain the complaint in detail...')" required />
                <div style="display: flex; justify-content: flex-end; margin-top: 8px;">
                    <button type="submit" class="btn btn-primary">{{ __('Submit Complaint') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">{{ __('My Complaints') }}</div>
        @forelse($complaints as $complaint)
            <div class="complaint-item">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                    <div>
                        <div style="font-weight: 700; color: var(--text-primary);">{{ $complaint->subject }}</div>
                        <div class="meta">{{ __(ucfirst($complaint->category)) }} · {{ __(ucfirst($complaint->priority)) }} · {{ $complaint->created_at->format('M d, Y') }}</div>
                    </div>
                    <span class="status {{ $complaint->status === 'resolved' ? 'status-resolved' : ($complaint->status === 'rejected' ? 'status-rejected' : '') }}">{{ __(ucwords(str_replace('_', ' ', $complaint->status))) }}</span>
                </div>
                <div style="margin-top: 12px; color: var(--text-secondary); font-size: 13px; white-space: pre-line;">{{ $complaint->description }}</div>
                @if($complaint->admin_response)
                    <div class="response"><strong>{{ __('Admin Response') }}:</strong> {{ $complaint->admin_response }}</div>
                @endif
            </div>
        @empty
            <div class="empty-state">{{ __('You have not submitted any complaints yet.') }}</div>
        @endforelse
        @if($complaints->hasPages())
            <div style="padding: 14px 20px; border-top: 1px solid var(--border-soft);">{{ $complaints->links() }}</div>
        @endif
    </div>
</x-layouts.app>
