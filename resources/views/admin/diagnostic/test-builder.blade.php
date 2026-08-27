<x-layouts.app :pageTitle="__('Test Builder')">
<style>
    .page-title { font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }

    .filter-card {
        background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft);
        box-shadow: var(--shadow-card); padding: 18px 20px; margin-bottom: 20px;
        display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;
    }
    .filter-select, .form-input, .form-textarea {
        padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 8px;
        font-size: 13.5px; font-family: var(--font-body); color: var(--text-strong);
        background: var(--surface-3); outline: none;
    }
    .filter-select { min-width: 220px; }
    .form-input { width: 100%; }
    .form-textarea { width: 100%; min-height: 80px; resize: vertical; }
    .filter-select:focus, .form-input:focus, .form-textarea:focus {
        border-color: var(--primary); box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent);
    }

    .grid-two { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    @media (max-width: 768px) { .grid-two { grid-template-columns: 1fr; } }
    .card { background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft); box-shadow: var(--shadow-card); }
    .card-header { padding: 16px 20px; border-bottom: 1px solid var(--border-soft); font-size: 14px; font-weight: 700; color: var(--text-primary); }
    .card-body { padding: 20px; }

    .form-label { font-size: 12px; font-weight: 600; color: var(--text-strong); margin-bottom: 5px; display: block; }
    .form-group { margin-bottom: 14px; }
    .btn { padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: background 0.2s; }
    .btn-primary { background: var(--primary); color: var(--on-primary); }
    .btn-primary:hover { background: var(--primary-dark); }
    .btn-danger { background: var(--danger-tint); color: var(--danger-text); }
    .btn-danger:hover { background: var(--danger-border); }
    .btn-sm { padding: 5px 12px; font-size: 12px; }

    table { width: 100%; border-collapse: collapse; }
    thead tr { background: var(--primary); color: var(--on-primary); }
    th { padding: 10px 12px; text-align: start; font-size: 11px; font-weight: 700; }
    td { padding: 9px 12px; border-bottom: 1px solid var(--border-soft); font-size: 12px; }
    tbody tr:nth-child(even) { background: var(--surface-2); }

    .tag { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 600; background: var(--primary-tint); color: var(--violet-strong); }
    .empty-state { padding: 40px; text-align: center; color: var(--text-muted); font-size: 13px; }

    .alert-success { background: var(--success-tint); color: var(--success-text); padding: 10px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
    .alert-error { background: var(--danger-tint); color: var(--danger-text); padding: 10px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }

    .options-list { display: flex; flex-direction: column; gap: 8px; }
    .option-row { display: flex; gap: 8px; align-items: center; }
    .option-row input[type=text] { flex: 1; }
    .option-row input[type=radio] { width: 16px; height: 16px; accent-color: var(--primary); }
</style>

<div class="page-title">{{ __('Test Builder') }}</div>
<div class="page-desc">{{ __('Create learning objectives and diagnostic questions for each subject.') }}</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

{{-- Subject selector --}}
<form method="GET" action="{{ route('admin.diagnostic.test-builder') }}">
    <div class="filter-card">
        <div class="filter-group">
            <label class="filter-label">{{ __('Subject') }}</label>
            <select class="filter-select" name="subject_id" data-auto-submit>
                <option value="">-- {{ __('Select Subject') }} --</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" {{ $subject?->id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</form>

@if($subject)
<div class="grid-two">
    {{-- Add Learning Objective --}}
    <div class="card">
        <div class="card-header">{{ __('Add Learning Objective') }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.diagnostic.objectives.store') }}">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                <div class="form-group">
                    <label class="form-label">{{ __('Objective Name') }}</label>
                    <input type="text" name="name" class="form-input" required placeholder="{{ __('e.g. Algebra Basics') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Description (optional)') }}</label>
                    <textarea name="description" class="form-textarea" placeholder="{{ __('Brief description...') }}"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Parent Objective (optional)') }}</label>
                    <select name="parent_id" class="filter-select" style="min-width: unset; width: 100%;">
                        <option value="">-- {{ __('None (root)') }} --</option>
                        @foreach($objectives as $obj)
                            <option value="{{ $obj->id }}">{{ $obj->name }}</option>
                            @foreach($obj->children as $child)
                                <option value="{{ $child->id }}">— {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Add Objective') }}</button>
            </form>
        </div>
    </div>

    {{-- Add Question --}}
    <div class="card">
        <div class="card-header">{{ __('Add Question') }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.diagnostic.questions.store') }}" id="questionForm">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                <div class="form-group">
                    <label class="form-label">{{ __('Learning Objective') }}</label>
                    <select name="learning_objective_id" class="filter-select" style="min-width: unset; width: 100%;" required>
                        <option value="">-- {{ __('Select Objective') }} --</option>
                        @foreach($objectives as $obj)
                            <option value="{{ $obj->id }}">{{ $obj->name }}</option>
                            @foreach($obj->children as $child)
                                <option value="{{ $child->id }}">— {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Question Text') }}</label>
                    <textarea name="question_text" class="form-textarea" required placeholder="{{ __('Enter the question...') }}"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Question Type') }}</label>
                    <select name="type" class="filter-select" style="min-width: unset; width: 100%;" data-question-type>
                        <option value="mcq">{{ __('Multiple Choice (MCQ)') }}</option>
                        <option value="true_false">{{ __('True / False') }}</option>
                    </select>
                </div>
                <div id="optionsSection" class="form-group">
                    <label class="form-label">{{ __('Options') }} <span style="font-size:11px; color:var(--text-muted);">({{ __('select the correct one') }})</span></label>
                    <div class="options-list" id="optionsList">
                        <div class="option-row">
                            <input type="radio" name="correct_option" value="0" checked>
                            <input type="text" name="options[0][option_text]" class="form-input" placeholder="Option A" required>
                        </div>
                        <div class="option-row">
                            <input type="radio" name="correct_option" value="1">
                            <input type="text" name="options[1][option_text]" class="form-input" placeholder="Option B" required>
                        </div>
                        <div class="option-row">
                            <input type="radio" name="correct_option" value="2">
                            <input type="text" name="options[2][option_text]" class="form-input" placeholder="Option C (optional)">
                        </div>
                        <div class="option-row">
                            <input type="radio" name="correct_option" value="3">
                            <input type="text" name="options[3][option_text]" class="form-input" placeholder="Option D (optional)">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="options[0][is_correct]" value="0">
                <input type="hidden" name="options[1][is_correct]" value="0">
                <input type="hidden" name="options[2][is_correct]" value="0">
                <input type="hidden" name="options[3][is_correct]" value="0">
                <button type="submit" class="btn btn-primary">{{ __('Add Question') }}</button>
            </form>
        </div>
    </div>
</div>

{{-- Questions Table --}}
<div class="card">
    <div class="card-header">{{ __('Questions for :name (:count)', ['name' => $subject->name, 'count' => $questions->count()]) }}</div>
    @if($questions->isEmpty())
        <div class="empty-state">{{ __('No questions yet. Add your first question above.') }}</div>
    @else
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __("Question") }}</th>
                    <th>{{ __("Objective") }}</th>
                    <th>{{ __("Type") }}</th>
                    <th>{{ __("Options") }}</th>
                    <th>{{ __("Action") }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($questions as $i => $q)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="max-width:250px;">{{ Str::limit($q->question_text, 80) }}</td>
                        <td><span class="tag">{{ $q->learningObjective->name ?? '—' }}</span></td>
                        <td>{{ $q->type->value === 'true_false' ? __('True / False') : __('Multiple Choice (MCQ)') }}</td>
                        <td>
                            @foreach($q->options as $opt)
                                <div style="font-size:11px; {{ $opt->is_correct ? 'color:var(--success-text); font-weight:700;' : 'color:var(--text-secondary);' }}">
                                    {{ $opt->is_correct ? '✓ ' : '' }}{{ $opt->option_text }}
                                </div>
                            @endforeach
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.diagnostic.questions.destroy', $q) }}" onsubmit="return confirm('{{ __('Delete this question?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>
@endif

</x-layouts.app>
