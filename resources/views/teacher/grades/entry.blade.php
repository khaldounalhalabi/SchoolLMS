<x-layouts.app :pageTitle="__('Grade Entry')">
<style>
    .page-header { margin-bottom: 20px; }
    .page-title { font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary); }

    .filter-card {
        background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft);
        box-shadow: var(--shadow-card); padding: 20px; margin-bottom: 20px;
        display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;
    }
    .filter-select {
        padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 8px;
        font-size: 13.5px; font-family: var(--font-body); color: var(--text-strong);
        background: var(--surface-3); outline: none; transition: border 0.2s; min-width: 180px;
    }

    .btn-primary {
        padding: 9px 20px; background: var(--primary); color: var(--on-primary); border: none; border-radius: 8px;
        font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font-body); transition: background 0.2s;
    }
    .btn-primary:hover { background: var(--primary-dark); }

    table { width: 100%; border-collapse: collapse; }
    thead tr { background: var(--surface-2); }
    th { padding: 12px 16px; text-align: start; font-size: 11px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.8px; text-transform: uppercase; }
    td { padding: 10px 16px; border-bottom: 1px solid var(--surface-2); font-size: 13.5px; color: var(--text-strong); }
    tbody tr:last-child td { border-bottom: none; }

    .score-input {
        width: 90px; padding: 7px 10px; border: 1.5px solid var(--border); border-radius: 7px;
        font-size: 13px; font-family: var(--font-body); text-align: center;
        outline: none; transition: border 0.2s;
    }
    .score-input:focus { border-color: var(--primary); box-shadow: 0 0 0 2px color-mix(in srgb, var(--primary) 10%, transparent); }
    .score-input.invalid { border-color: var(--danger); }

    .max-score-wrap { display: flex; align-items: center; gap: 8px; padding: 18px 20px; border-top: 1px solid var(--border-soft); }
    .form-actions { padding: 16px 20px; border-top: 1px solid var(--border-soft); display: flex; justify-content: flex-end; }

    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; font-weight: 500; }
    .alert-success { background: var(--success-tint); color: var(--success-text); }
    .alert-error { background: var(--danger-tint); color: var(--danger-text); }

    .empty-state { padding: 48px 20px; text-align: center; color: var(--text-muted); }
    .empty-icon { font-size: 40px; margin-bottom: 10px; }
</style>

<div class="page-header">
    <div class="page-title">{{ __("Grade Entry") }}</div>
    <div class="page-desc">{{ __("Enter student scores per subject and exam type") }}</div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

{{-- Filters --}}
<form method="GET" action="{{ route('teacher.grades.entry') }}" id="filterForm">
    <div class="filter-card">
        <div class="filter-group">
            <label class="filter-label">{{ __("Semester") }}</label>
            <select class="filter-select" name="semester_id" data-auto-submit>
                <option value="">{{ __("Select semester…") }}</option>
                @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }}>
                        {{ $sem->academicYear->name ?? '' }} — {{ $sem->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">{{ __("Subject") }}</label>
            <select class="filter-select" name="subject_id" data-auto-submit>
                <option value="">{{ __("Select subject…") }}</option>
                @foreach($assignments->unique('subject_id') as $a)
                    <option value="{{ $a->subject_id }}" {{ $selectedSubjectId == $a->subject_id ? 'selected' : '' }}>
                        {{ $a->subject->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">{{ __("Classroom") }}</label>
            <select class="filter-select" name="classroom_id" data-auto-submit>
                <option value="">{{ __("Select classroom…") }}</option>
                @foreach($assignments as $a)
                    <option value="{{ $a->classroom_id }}" {{ $selectedClassroomId == $a->classroom_id ? 'selected' : '' }}>
                        {{ $a->classroom->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">{{ __("Exam Type") }}</label>
            <select class="filter-select" name="exam_type_id" data-auto-submit>
                <option value="">{{ __("Select exam type…") }}</option>
                @foreach($examTypes as $et)
                    <option value="{{ $et->id }}" {{ $selectedExamTypeId == $et->id ? 'selected' : '' }}>
                        {{ $et->name }} ({{ $et->weight_percent }}%)
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</form>

{{-- Grade Table --}}
@if($selectedSubjectId && $selectedClassroomId && $selectedExamTypeId)
    <form method="POST" action="{{ route('teacher.grades.store') }}" id="gradeForm" data-invalid-message="{{ __('Some scores exceed the max score. Please correct before saving.') }}">
        @csrf
        <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
        <input type="hidden" name="exam_type_id" value="{{ $selectedExamTypeId }}">

        <div class="table-card">
            <div class="table-header">
                <span class="table-title">{{ __("Student Scores") }}</span>
                <span class="table-meta">{{ __(":count students", ['count' => $students->count()]) }}</span>
            </div>

            <div class="max-score-wrap">
                <label style="font-size:13px; font-weight:600; color:var(--text-strong);">{{ __("Max Score:") }}</label>
                <input type="number" name="max_score" id="maxScore" value="{{ $existingGrades->first()?->max_score ?? 100 }}"
                    step="0.01" min="0.01" style="width:90px; padding:7px 10px; border:1.5px solid var(--border); border-radius:7px; font-size:13px; font-family: var(--font-body); outline:none;">
            </div>

            @if($students->isNotEmpty())
                <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __("Student") }}</th>
                            <th>{{ __("Score") }}</th>
                            <th>{{ __("Status") }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $student)
                            @php $existing = $existingGrades->get($student->id); @endphp
                            <tr>
                                <td style="color:var(--text-muted);">{{ $i + 1 }}</td>
                                <td style="font-weight:600;">{{ $student->name }}</td>
                                <td>
                                    <input type="number" class="score-input" step="0.01" min="0"
                                        name="scores[{{ $student->id }}]"
                                        value="{{ $existing?->score }}"
                                        placeholder="—"
                                        data-student="{{ $student->id }}">
                                </td>
                                <td>
                                    @if($existing)
                                        <span style="font-size:11px; background:var(--success-tint); color:var(--success-text); padding:2px 8px; border-radius:99px; font-weight:700;">
                                            {{ __("Saved") }} ({{ $existing->score }}/{{ $existing->max_score }})
                                        </span>
                                    @else
                                        <span style="font-size:11px; background:var(--border-soft); color:var(--text-muted); padding:2px 8px; border-radius:99px; font-weight:700;">
                                            {{ __("Not entered") }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">{{ __("Save Grades") }}</button>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">👥</div>
                    <div>{{ __("No students in this classroom.") }}</div>
                </div>
            @endif
        </div>
    </form>
@else
    <div class="table-card">
        <div class="empty-state">
            <div class="empty-icon">📝</div>
            <div style="font-size:14px; font-weight:600; color:var(--text-strong); margin-bottom:6px;">{{ __("Select filters above") }}</div>
            <div>{{ __("Choose a subject, classroom, and exam type to start entering grades.") }}</div>
        </div>
    </div>
@endif

</x-layouts.app>
