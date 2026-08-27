<x-layouts.app :pageTitle="__('Review Submissions')">
    <style>
        .back-link { display: inline-flex; margin-bottom: 20px; color: var(--text-secondary); text-decoration: none; font-size: 13px; }
        .page-header { margin-bottom: 22px; }
        .page-header h2 { font-family: var(--font-display); font-size: 22px; color: var(--text-primary); margin-bottom: 4px; }
        .page-header p { font-size: 13px; color: var(--text-secondary); }
        .card { background: var(--surface); border: 1px solid var(--border-soft); border-radius: 14px; box-shadow: var(--shadow-card); overflow: hidden; }
        .submission-item { padding: 18px 20px; border-bottom: 1px solid var(--border-soft); }
        .submission-item:last-child { border-bottom: 0; }
        .review-form { display: flex; align-items: end; gap: 10px; flex-wrap: wrap; margin-top: 14px; padding: 14px; background: var(--surface-2); border-radius: 10px; }
        .review-form .form-field { margin-bottom: 0; }
        .status { display: inline-flex; padding: 3px 9px; border-radius: 20px; background: var(--primary-tint); color: var(--primary-dark); font-size: 11px; font-weight: 700; }
        .empty-state { padding: 52px 20px; text-align: center; color: var(--text-faint); }
    </style>

    <a href="{{ route('teacher.homework') }}" class="back-link">&larr; {{ __('Back to Homework') }}</a>

    <div class="page-header">
        <h2>{{ $homework->title }}</h2>
        <p>{{ $homework->subject->name }} · {{ $homework->classroom->grade->name }} / {{ $homework->classroom->name }} · {{ __('Due :date', ['date' => $homework->due_date->format('M d, Y')]) }}</p>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        @forelse($homework->submissions as $submission)
            <div class="submission-item">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                    <div>
                        <div style="font-weight: 700; color: var(--text-primary);">{{ $submission->student->name }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $submission->student->email }} · {{ $submission->submitted_at->format('M d, Y H:i') }}</div>
                    </div>
                    <span class="status">{{ __(ucfirst($submission->status)) }}</span>
                </div>
                <div style="margin-top: 10px; font-size: 13px;"><a href="{{ route('teacher.homework.submissions.download', $submission) }}">{{ $submission->original_filename }}</a></div>
                <form method="POST" action="{{ route('teacher.homework.submissions.review', $submission) }}" class="review-form">
                    @csrf
                    <div class="form-field" style="width: 130px;">
                        <label class="form-label" for="grade_{{ $submission->id }}">{{ __('Grade') }}</label>
                        <input id="grade_{{ $submission->id }}" type="number" name="grade" class="form-control" min="0" max="{{ $homework->max_score }}" step="0.01" value="{{ $submission->grade }}">
                    </div>
                    <div class="form-field" style="flex: 1; min-width: 220px;">
                        <label class="form-label" for="feedback_{{ $submission->id }}">{{ __('Feedback') }}</label>
                        <input id="feedback_{{ $submission->id }}" type="text" name="feedback" class="form-control" value="{{ $submission->feedback }}" placeholder="{{ __('Optional feedback') }}">
                    </div>
                    <div class="form-field" style="width: 145px;">
                        <label class="form-label" for="status_{{ $submission->id }}">{{ __('Status') }}</label>
                        <select id="status_{{ $submission->id }}" name="status" class="form-control" required>
                            <option value="reviewed" @selected($submission->status === 'reviewed')>{{ __('Reviewed') }}</option>
                            <option value="returned" @selected($submission->status === 'returned')>{{ __('Return for revision') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('Save Review') }}</button>
                </form>
            </div>
        @empty
            <div class="empty-state">{{ __('No submissions yet.') }}</div>
        @endforelse
    </div>
</x-layouts.app>
