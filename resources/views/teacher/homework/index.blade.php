<x-layouts.app :pageTitle="__('Homework')">
    <style>
        .page-header { margin-bottom: 24px; }
        .page-header h2 { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .page-header p { font-size: 13px; color: var(--text-secondary); }
        .card { background: var(--surface); border: 1px solid var(--border-soft); border-radius: 14px; box-shadow: var(--shadow-card); overflow: hidden; margin-bottom: 24px; }
        .card-header { padding: 18px 20px; border-bottom: 1px solid var(--border-soft); font-weight: 700; color: var(--text-primary); }
        .card-body { padding: 20px; }
        .homework-item { padding: 16px 20px; border-bottom: 1px solid var(--border-soft); display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .homework-item:last-child { border-bottom: 0; }
        .homework-meta { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
        .homework-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .btn-small { display: inline-flex; padding: 7px 11px; border-radius: 8px; background: var(--primary-tint); color: var(--primary-dark); text-decoration: none; font-size: 12px; font-weight: 600; }
        .empty-state { padding: 44px 20px; text-align: center; color: var(--text-faint); }
    </style>

    <div class="page-header">
        <h2>{{ __('Homework') }}</h2>
        <p>{{ __('Create homework and review student submissions.') }}</p>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">{{ __('Create Homework') }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('teacher.homework.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <x-ui.form.field name="teacher_assignment_id" :label="__('Subject and Classroom')" required>
                        <select name="teacher_assignment_id" class="form-control" required>
                            <option value="">{{ __('Select subject and classroom…') }}</option>
                            @foreach($assignments as $assignment)
                                <option value="{{ $assignment->id }}" @selected(old('teacher_assignment_id') == $assignment->id)>
                                    {{ $assignment->subject->name }} — {{ $assignment->classroom->grade->name }} / {{ $assignment->classroom->name }} ({{ $assignment->academicYear->name }})
                                </option>
                            @endforeach
                        </select>
                    </x-ui.form.field>
                    <x-ui.form.field name="title" :label="__('Homework Title')" :placeholder="__('e.g. Algebra exercises')" required />
                </div>
                <div class="form-row">
                    <x-ui.form.field name="due_date" type="date" :label="__('Due Date')" required />
                    <x-ui.form.field name="max_score" type="number" :label="__('Max Score')" :value="old('max_score', 100)" min="0.01" step="0.01" required />
                </div>
                <x-ui.form.field name="description" type="textarea" :label="__('Instructions')" :placeholder="__('Describe what students should submit...')" />
                <x-ui.form.field name="attachment" type="file" :label="__('Reference File')" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.zip" />
                <div style="display: flex; justify-content: flex-end; margin-top: 8px;">
                    <button type="submit" class="btn btn-primary">{{ __('Publish Homework') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">{{ __('My Homework') }}</div>
        @forelse($homeworks as $homework)
            <div class="homework-item">
                <div>
                    <div style="font-weight: 700; color: var(--text-primary);">{{ $homework->title }}</div>
                    <div class="homework-meta">
                        {{ $homework->subject->name }} · {{ $homework->classroom->grade->name }} / {{ $homework->classroom->name }} ·
                        {{ __('Due :date', ['date' => $homework->due_date->format('M d, Y')]) }} ·
                        {{ __(':count submissions', ['count' => $homework->submissions_count]) }}
                    </div>
                </div>
                <div class="homework-actions">
                    @if($homework->attachment_path)
                        <a class="btn-small" href="{{ route('teacher.homework.attachment', $homework) }}">{{ __('Reference File') }}</a>
                    @endif
                    <a class="btn-small" href="{{ route('teacher.homework.submissions', $homework) }}">{{ __('Review Submissions') }}</a>
                </div>
            </div>
        @empty
            <div class="empty-state">{{ __('No homework created yet.') }}</div>
        @endforelse
        @if($homeworks->hasPages())
            <div style="padding: 14px 20px; border-top: 1px solid var(--border-soft);">{{ $homeworks->links() }}</div>
        @endif
    </div>
</x-layouts.app>
