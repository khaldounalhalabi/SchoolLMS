<x-layouts.app :pageTitle="__('Homework')">
    <style>
        .page-header { margin-bottom: 24px; }
        .page-header h2 { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .page-header p { font-size: 13px; color: var(--text-secondary); }
        .card { background: var(--surface); border: 1px solid var(--border-soft); border-radius: 14px; box-shadow: var(--shadow-card); overflow: hidden; }
        .homework-item { padding: 20px; border-bottom: 1px solid var(--border-soft); }
        .homework-item:last-child { border-bottom: 0; }
        .meta { font-size: 12px; color: var(--text-muted); margin: 5px 0 12px; }
        .submission { padding: 12px; border-radius: 10px; background: var(--surface-2); margin-top: 12px; }
        .status { display: inline-flex; padding: 3px 9px; border-radius: 20px; background: var(--primary-tint); color: var(--primary-dark); font-size: 11px; font-weight: 700; }
        .empty-state { padding: 52px 20px; text-align: center; color: var(--text-faint); }
    </style>

    <div class="page-header">
        <h2>{{ __('Homework') }}</h2>
        <p>{{ __('View your homework and upload submissions.') }}</p>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        @forelse($homeworks as $homework)
            @php($submission = $homework->submissions->first())
            <div class="homework-item">
                <div style="display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; flex-wrap: wrap;">
                    <div>
                        <div style="font-weight: 700; color: var(--text-primary);">{{ $homework->title }}</div>
                        <div class="meta">
                            {{ $homework->subject->name }} · {{ $homework->classroom->grade->name }} / {{ $homework->classroom->name }} ·
                            {{ __('Due :date', ['date' => $homework->due_date->format('M d, Y')]) }} ·
                            {{ __('Max Score: :score', ['score' => $homework->max_score]) }}
                        </div>
                        @if($homework->description)
                            <div style="font-size: 13px; color: var(--text-secondary); white-space: pre-line;">{{ $homework->description }}</div>
                        @endif
                    </div>
                    @if($homework->attachment_path)
                        <a class="btn btn-outline" href="{{ route('student.homework.attachment', $homework) }}">{{ __('Download Reference File') }}</a>
                    @endif
                </div>

                @if($submission)
                    <div class="submission">
                        <div style="display: flex; justify-content: space-between; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <div style="font-size: 13px; color: var(--text-primary);">
                                {{ __('Your submission') }}: <a href="{{ route('student.homework.submissions.download', $submission) }}">{{ $submission->original_filename }}</a>
                            </div>
                            <span class="status">{{ __(ucfirst($submission->status)) }}</span>
                        </div>
                        @if($submission->grade !== null || $submission->feedback)
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 8px;">
                                @if($submission->grade !== null) {{ __('Grade: :grade / :max', ['grade' => $submission->grade, 'max' => $homework->max_score]) }} @endif
                                @if($submission->feedback) · {{ $submission->feedback }} @endif
                            </div>
                        @endif
                    </div>
                @endif

                @if(today()->lte($homework->due_date))
                    <form method="POST" action="{{ route('student.homework.submit', $homework) }}" enctype="multipart/form-data" style="display: flex; align-items: end; gap: 10px; flex-wrap: wrap; margin-top: 14px;">
                        @csrf
                        <div style="flex: 1; min-width: 220px;">
                            <label class="form-label" for="submission_{{ $homework->id }}">{{ $submission ? __('Replace Submission') : __('Upload Submission') }}</label>
                            <input id="submission_{{ $homework->id }}" type="file" name="submission" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.zip" required>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('Submit Homework') }}</button>
                    </form>
                @else
                    <div style="font-size: 12px; color: var(--danger-text); margin-top: 12px;">{{ __('The homework deadline has passed.') }}</div>
                @endif
            </div>
        @empty
            <div class="empty-state">{{ __('No homework is available for you yet.') }}</div>
        @endforelse
        @if($homeworks->hasPages())
            <div style="padding: 14px 20px; border-top: 1px solid var(--border-soft);">{{ $homeworks->links() }}</div>
        @endif
    </div>
</x-layouts.app>
