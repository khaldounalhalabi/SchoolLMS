<x-layouts.app :pageTitle="__('My Results')">
<style>
    .page-header { margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .page-title { font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--text-primary); }

    .filter-card {
        background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft);
        box-shadow: var(--shadow-card); padding: 18px 20px; margin-bottom: 20px;
        display: flex; gap: 14px; flex-wrap: wrap; align-items: flex-end;
    }
    .filter-select {
        padding: 9px 14px; border: 1.5px solid var(--border); border-radius: 8px;
        font-size: 13.5px; font-family: var(--font-body); color: var(--text-strong);
        background: var(--surface-3); outline: none; min-width: 200px;
    }

    .btn-download {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 20px; background: var(--primary); color: var(--on-primary); border-radius: 8px;
        font-size: 13px; font-weight: 600; text-decoration: none; transition: background 0.2s;
    }
    .btn-download:hover { background: var(--primary-dark); }

    .subject-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(320px, 100%), 1fr)); gap: 16px; }

    .subject-card {
        background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft);
        box-shadow: var(--shadow-card); overflow: hidden;
    }
    .subject-card-header {
        padding: 16px 18px; border-bottom: 1px solid var(--border-soft);
        display: flex; align-items: center; justify-content: space-between;
    }
    .subject-name { font-size: 14px; font-weight: 700; color: var(--text-primary); }
    .grade-badge {
        width: 42px; height: 42px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; font-weight: 800;
    }
    .grade-a { background: var(--success-tint); color: var(--success-text); }
    .grade-b { background: var(--info-tint-2); color: var(--info-strong); }
    .grade-c { background: var(--warning-tint); color: var(--warning-text); }
    .grade-d { background: var(--warning-border); color: var(--ember-text); }
    .grade-f { background: var(--danger-tint); color: var(--danger-text); }

    .subject-card-body { padding: 14px 18px; }
    .exam-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 7px 0; border-bottom: 1px solid var(--surface-2); font-size: 13px;
    }
    .exam-row:last-child { border-bottom: none; }
    .exam-name { color: var(--text-secondary); font-weight: 500; }
    .exam-score { font-weight: 700; color: var(--text-primary); }

    .subject-card-footer {
        padding: 12px 18px; background: var(--surface-2); border-top: 1px solid var(--border-soft);
        display: flex; justify-content: space-between; align-items: center;
    }
    .avg-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
    .avg-value { font-size: 16px; font-weight: 800; color: var(--primary); }

    .empty-state { padding: 64px 20px; text-align: center; color: var(--text-muted); }
    .empty-icon { font-size: 48px; margin-bottom: 12px; }

    .progress-bar-wrap { height: 6px; background: var(--border-soft); border-radius: 99px; margin-top: 8px; overflow: hidden; }
    .progress-bar { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--primary), var(--primary-light)); transition: width 0.6s ease; }
</style>

<div class="page-header">
    <div>
        <div class="page-title">{{ __("My Results") }}</div>
        <div class="page-desc">{{ __("Your academic scores per subject") }}</div>
    </div>
    @if($selectedSemesterId)
        <a href="{{ route('student.results.pdf', ['semester_id' => $selectedSemesterId]) }}" class="btn-download" target="_blank">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            {{ __("Download Report Card") }}
        </a>
    @endif
</div>

{{-- Semester filter --}}
<form method="GET" action="{{ route('student.results') }}">
    <div class="filter-card">
        <div class="filter-group">
            <label class="filter-label">{{ __("Semester") }}</label>
            <select class="filter-select" name="semester_id" data-auto-submit>
                @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }}>
                        {{ $sem->academicYear->name ?? '' }} — {{ $sem->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</form>

{{-- Subject cards --}}
@if($summaries->isNotEmpty())
    <div class="subject-cards">
        @foreach($summaries as $summary)
            @php
                $subjectGrades = $grades->get($summary->subject_id, collect());
                $badgeClass = match($summary->letter_grade) {
                    'A' => 'grade-a', 'B' => 'grade-b', 'C' => 'grade-c',
                    'D' => 'grade-d', default => 'grade-f'
                };
            @endphp
            <div class="subject-card">
                <div class="subject-card-header">
                    <div class="subject-name">{{ $summary->subject->name }}</div>
                    <div class="grade-badge {{ $badgeClass }}">{{ $summary->letter_grade }}</div>
                </div>
                <div class="subject-card-body">
                    @foreach($examTypes as $et)
                        @php $g = $subjectGrades->firstWhere('exam_type_id', $et->id); @endphp
                        <div class="exam-row">
                            <span class="exam-name">{{ $et->name }} <span style="color:#c4b5fd; font-size:11px;">({{ $et->weight_percent }}%)</span></span>
                            <span class="exam-score">
                                @if($g)
                                    {{ $g->score }} / {{ $g->max_score }}
                                    <span style="font-size:11px; color:var(--text-muted); font-weight:500;">({{ round($g->score/$g->max_score*100, 1) }}%)</span>
                                @else
                                    <span style="color:var(--text-faint);">—</span>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="subject-card-footer">
                    <div>
                        <div class="avg-label">{{ __("Weighted Average") }}</div>
                        <div class="progress-bar-wrap" style="width:160px; margin-top:6px;">
                            <div class="progress-bar" style="width:{{ min($summary->weighted_average, 100) }}%"></div>
                        </div>
                    </div>
                    <div class="avg-value">{{ $summary->weighted_average }}%</div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div style="background:var(--surface); border-radius:14px; border:1px solid var(--border-soft); box-shadow: var(--shadow-card);">
        <div class="empty-state">
            <div class="empty-icon">📊</div>
            <div style="font-size:14px; font-weight:600; color:var(--text-strong); margin-bottom:6px;">{{ __("No results yet") }}</div>
            <div>{{ __("No grades have been entered for this semester.") }}</div>
        </div>
    </div>
@endif
</x-layouts.app>
