<x-layouts.app :pageTitle="__('Diagnostic Test')">
<style>
    /* Progress bar */
    .progress-wrap { background: var(--border-soft); border-radius: 99px; height: 8px; overflow: hidden; margin-bottom: 20px; }
    .progress-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--primary), var(--primary-light)); transition: width 0.4s ease; }
    .progress-label { font-size: 12px; color: var(--text-secondary); margin-bottom: 6px; display: flex; justify-content: space-between; }

    /* Question card */
    .question-card { margin-bottom: 24px; padding: 20px; background: var(--surface-3); border-radius: 12px; border: 1px solid var(--border); }
    .question-number { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.7px; margin-bottom: 8px; }
    .question-text { font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 14px; line-height: 1.5; }
    .options-list { display: flex; flex-direction: column; gap: 8px; }
    .option-label {
        display: flex; align-items: center; gap: 10px; padding: 10px 14px;
        border-radius: 8px; border: 1.5px solid var(--border); cursor: pointer;
        transition: border-color 0.15s, background 0.15s; font-size: 13px; color: var(--text-strong);
    }
    .option-label:has(input:checked) { border-color: var(--primary); background: var(--primary-tint); color: var(--primary-dark); font-weight: 600; }
    .option-label input[type=radio] { accent-color: var(--primary); width: 16px; height: 16px; }

</style>

<x-page-header
    :title="__('Diagnostic Test')"
    :description="__('Test your knowledge and discover learning gaps.')"
/>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

{{-- Subject selector --}}
<div class="filter-card">
    <form method="GET" action="{{ route('student.diagnostic.test') }}">
        <div class="filter-group">
            <label class="filter-label">{{ __("Subject") }}</label>
            <select class="filter-select" name="subject_id" data-auto-submit>
                <option value="">{{ __("-- Select Subject --") }}</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" {{ $subject?->id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
    </form>

    @if($subject)
        <div style="margin-inline-start: auto; display: flex; gap: 10px; align-items: flex-end;">
            <form method="POST" action="{{ route('student.diagnostic.start') }}" data-confirm="{{ __('Start a new test? Any in-progress test will end.') }}">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                <button type="submit" class="btn btn-outline" >
                    {{ __("Start New Test") }}
                </button>
            </form>
            <a href="{{ route('student.diagnostic.knowledge-map', ['subject_id' => $subject->id]) }}" class="btn btn-primary">
                {{ __("View Knowledge Map") }}
            </a>
        </div>
    @endif
</div>

@if($subject)
    @if($attempt && $questions->isNotEmpty())
        {{-- Progress --}}
        @php
            $pct = $totalForSubject > 0 ? round(($answered / $totalForSubject) * 100) : 0;
        @endphp
        <div class="progress-label">
            <span>{{ __("Progress") }}</span>
            <span>{{ __(":answered / :total questions answered", ['answered' => $answered, 'total' => $totalForSubject]) }}</span>
        </div>
        <div class="progress-wrap">
            <div class="progress-fill" style="width: {{ $pct }}%"></div>
        </div>

        <form method="POST" action="{{ route('student.diagnostic.submit', $attempt) }}" id="testForm" data-unanswered-message="{{ __('You have :count unanswered question(s). Submit anyway?') }}">
            @csrf
            @foreach($questions as $qi => $q)
                <div class="question-card">
                    <div class="question-number">{{ __("Question :number", ['number' => $answered + $qi + 1]) }}</div>
                    <div class="question-text">{{ $q->question_text }}</div>
                    <div class="options-list">
                        @foreach($q->options as $opt)
                            <label class="option-label">
                                <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt->id }}">
                                {{ $opt->option_text }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div style="display:flex; justify-content:flex-end; margin-top: 16px;">
                <button type="submit" class="btn btn-primary">
                    {{ __("Submit Answers & See Knowledge Map") }}
                </button>
            </div>
        </form>

    @elseif($attempt && $questions->isEmpty())
        <div class="card">
            <x-empty-state
                icon="🎉"
                :title="__('All questions answered!')"
                :description="__('You have completed all available questions for :subject.', ['subject' => $subject->name])"
            />
            <a href="{{ route('student.diagnostic.knowledge-map', ['subject_id' => $subject->id]) }}" class="btn btn-primary">
                {{ __('View Your Knowledge Map') }}
            </a>
        </div>
    @else
        <div class="card">
            <x-empty-state
                icon="📝"
                :title="__('Ready to test your knowledge?')"
                :description="__('Click \'Start New Test\' to begin a diagnostic test for :subject.', ['subject' => $subject->name])"
            />
            <form method="POST" action="{{ route('student.diagnostic.start') }}" data-confirm="{{ __('Start a new test? Any in-progress test will end.') }}">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                <button type="submit" class="btn btn-primary">{{ __('Start Test') }}</button>
            </form>
        </div>
    @endif
@endif

</x-layouts.app>
