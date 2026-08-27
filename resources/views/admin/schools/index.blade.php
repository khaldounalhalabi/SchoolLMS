<x-layouts.app :pageTitle="__('Schools')">
    <style>
        .form-card { max-width: 620px; margin-bottom: 24px; }
        .card { background: var(--surface); border: 1px solid var(--border-soft); border-radius: 14px; box-shadow: var(--shadow-card); overflow: hidden; }
        .card-header { padding: 18px 20px; border-bottom: 1px solid var(--border-soft); font-weight: 700; color: var(--text-primary); }
        .card-body { padding: 20px; }
        .school-item { padding: 16px 20px; border-bottom: 1px solid var(--border-soft); }
        .school-item:last-child { border-bottom: 0; }
        .school-meta { margin-top: 5px; color: var(--text-muted); font-size: 12px; }
        .empty-state { padding: 48px 20px; text-align: center; color: var(--text-faint); }
    </style>

    <div class="page-actions">
        <div>
            <div class="rtl-display" style="font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary);">{{ __('Schools') }}</div>
            <div class="page-desc">{{ __('Manage the school profile used by academic years and subjects.') }}</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-warning">{{ session('error') }}</div>
    @endif

    <div class="card form-card">
        <div class="card-header">{{ __('Create School') }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.schools.store') }}">
                @csrf
                <x-ui.form.field name="name" :label="__('School Name')" :placeholder="__('e.g. Al-Nahda School')" required />
                <div class="form-row">
                    <x-ui.form.field name="address" :label="__('Address')" />
                    <x-ui.form.field name="phone" :label="__('Phone')" />
                </div>
                <div style="display: flex; justify-content: flex-end; margin-top: 6px;">
                    <button type="submit" class="btn btn-primary">{{ __('Create School') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">{{ __('Existing Schools') }}</div>
        @forelse($schools as $school)
            <div class="school-item">
                <div style="font-weight: 700; color: var(--text-primary);">{{ $school->name }}</div>
                <div class="school-meta">
                    {{ $school->address ?? __('No address') }} · {{ $school->phone ?? __('No phone') }} ·
                    {{ __(':years academic years', ['years' => $school->academic_years_count]) }} ·
                    {{ __(':grades grades', ['grades' => $school->grades_count]) }} ·
                    {{ __(':subjects subjects', ['subjects' => $school->subjects_count]) }}
                </div>
            </div>
        @empty
            <div class="empty-state">{{ __('No schools found. Create one to get started.') }}</div>
        @endforelse
    </div>
</x-layouts.app>
