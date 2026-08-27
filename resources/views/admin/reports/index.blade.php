<x-layouts.app :pageTitle="__('Reports')">
    @php
        $maxGradeDistribution = max(1, (int) $gradeDistribution->max());
        $selectedPeriod = $filters['semester']
            ? (($filters['academic_year']?->name ?? '') . ' — ' . $filters['semester']->name)
            : __('All available periods');
    @endphp

    <style>
        .reports-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:24px; }
        .reports-title { font-family:var(--font-display); font-size:24px; font-weight:700; color:var(--text-primary); margin-bottom:5px; }
        .reports-subtitle { color:var(--text-secondary); font-size:13px; line-height:1.5; }
        .header-actions { display:flex; gap:8px; flex-wrap:wrap; }
        .btn-report { display:inline-flex; align-items:center; gap:7px; padding:9px 14px; border-radius:8px; text-decoration:none; font-size:12.5px; font-weight:700; transition:all .2s; }
        .btn-report svg { width:15px; height:15px; }
        .btn-primary-report { background:var(--primary); color:var(--on-primary); }
        .btn-primary-report:hover { background:var(--primary-dark); }
        .btn-secondary-report { background:var(--surface); border:1px solid var(--border); color:var(--text-secondary); }
        .btn-secondary-report:hover { border-color:var(--primary-light); color:var(--primary-dark); }

        .filter-card { background:var(--surface); border:1px solid var(--border-soft); border-radius:14px; box-shadow:var(--shadow-card); padding:18px 20px; margin-bottom:24px; }
        .filter-heading { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:15px; }
        .filter-title { font-size:14px; font-weight:700; color:var(--text-primary); }
        .filter-period { font-size:11.5px; color:var(--text-muted); }
        .filter-grid { display:grid; grid-template-columns:repeat(4, minmax(150px, 1fr)); gap:12px; }
        .filter-group { display:flex; flex-direction:column; gap:5px; }
        .filter-label { font-size:10.5px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.55px; }
        .filter-select, .filter-input { width:100%; min-height:38px; padding:8px 11px; border:1.5px solid var(--border); border-radius:8px; background:var(--surface-3); color:var(--text-strong); font:inherit; font-size:12.5px; outline:none; }
        .filter-select:focus, .filter-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent); }
        .filter-actions { display:flex; align-items:center; gap:10px; margin-top:14px; }
        .filter-submit { border:0; cursor:pointer; }
        .clear-link { color:var(--text-secondary); font-size:12px; text-decoration:none; }
        .clear-link:hover { color:var(--primary-dark); }

        .metric-grid { display:grid; grid-template-columns:repeat(6, minmax(130px, 1fr)); gap:12px; margin-bottom:24px; }
        .metric-card { background:var(--surface); border:1px solid var(--border-soft); border-radius:13px; box-shadow:var(--shadow-card); padding:16px; min-width:0; }
        .metric-label { color:var(--text-muted); font-size:10.5px; font-weight:700; line-height:1.35; text-transform:uppercase; letter-spacing:.45px; }
        .metric-value { color:var(--text-primary); font-family:var(--font-display); font-size:24px; font-weight:700; margin-top:8px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .metric-value.warning { color:var(--warning-dark); }
        .metric-value.danger { color:var(--danger-dark); }
        .metric-caption { color:var(--text-muted); font-size:10.5px; margin-top:5px; }

        .section-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px; margin-bottom:16px; }
        .report-card { background:var(--surface); border:1px solid var(--border-soft); border-radius:14px; box-shadow:var(--shadow-card); overflow:hidden; }
        .report-card-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding:18px 20px; border-bottom:1px solid var(--border-soft); }
        .report-card-title { color:var(--text-primary); font-family:var(--font-display); font-size:16px; font-weight:700; }
        .report-card-meta { color:var(--text-muted); font-size:11.5px; line-height:1.4; margin-top:3px; }
        .report-card-body { padding:18px 20px; }
        .bar-list { display:flex; flex-direction:column; gap:13px; }
        .bar-row { display:grid; grid-template-columns:minmax(90px, 1fr) minmax(100px, 2fr) 48px; align-items:center; gap:10px; font-size:12px; }
        .bar-label { color:var(--text-secondary); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .bar-track { height:8px; background:var(--surface-2); border-radius:99px; overflow:hidden; }
        .bar-fill { height:100%; background:linear-gradient(90deg, var(--primary), var(--primary-light)); border-radius:99px; }
        .bar-value { color:var(--text-primary); font-size:11.5px; font-weight:700; text-align:end; }
        .distribution-grid { display:grid; grid-template-columns:repeat(5, 1fr); align-items:end; gap:10px; height:150px; }
        .distribution-item { display:flex; flex-direction:column; align-items:center; justify-content:end; gap:7px; height:100%; }
        .distribution-count { color:var(--text-primary); font-size:12px; font-weight:700; }
        .distribution-bar-wrap { height:100px; width:100%; display:flex; align-items:end; justify-content:center; }
        .distribution-bar { width:min(34px, 70%); min-height:4px; border-radius:6px 6px 2px 2px; background:var(--primary-light); }
        .distribution-item:nth-child(1) .distribution-bar { background:var(--success); }
        .distribution-item:nth-child(2) .distribution-bar { background:var(--primary-light); }
        .distribution-item:nth-child(3) .distribution-bar { background:var(--warning); }
        .distribution-item:nth-child(4) .distribution-bar { background:var(--accent); }
        .distribution-item:nth-child(5) .distribution-bar { background:var(--danger); }
        .distribution-label { color:var(--text-muted); font-size:11px; font-weight:700; }
        .status-grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:10px; }
        .status-item { background:var(--surface-2); border-radius:9px; padding:12px 10px; text-align:center; }
        .status-value { color:var(--text-primary); font-family:var(--font-display); font-size:20px; font-weight:700; }
        .status-label { color:var(--text-muted); font-size:10.5px; margin-top:3px; }
        .empty-report { color:var(--text-muted); font-size:12.5px; padding:20px 0 4px; text-align:center; }

        .table-card { margin-bottom:16px; }
        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; min-width:720px; }
        thead tr { background:var(--surface-2); }
        th { padding:11px 14px; color:var(--text-muted); font-size:10.5px; font-weight:700; letter-spacing:.65px; text-align:start; text-transform:uppercase; white-space:nowrap; }
        th:first-child, td:first-child { padding-inline-start:20px; }
        td { padding:13px 14px; border-top:1px solid var(--border-soft); color:var(--text-strong); font-size:12.5px; vertical-align:middle; }
        tbody tr:hover { background:var(--surface-2); }
        .student-name { color:var(--text-primary); font-weight:700; }
        .student-email { color:var(--text-muted); font-size:10.5px; margin-top:2px; }
        .score { color:var(--text-primary); font-weight:700; }
        .muted { color:var(--text-muted); }
        .badge { display:inline-flex; align-items:center; justify-content:center; min-width:30px; padding:4px 8px; border-radius:20px; font-size:11px; font-weight:800; }
        .badge-a, .badge-done { background:var(--success-tint); color:var(--success-dark); }
        .badge-b { background:var(--primary-tint); color:var(--primary-dark); }
        .badge-c { background:var(--warning-tint); color:var(--warning-text); }
        .badge-d { background:var(--accent-tint); color:var(--accent-dark); }
        .badge-f, .badge-attention { background:var(--danger-tint); color:var(--danger-dark); }
        .badge-none { background:var(--surface-2); color:var(--text-muted); }
        .small-action { color:var(--primary-dark); font-size:11.5px; font-weight:700; text-decoration:none; white-space:nowrap; }
        .small-action:hover { text-decoration:underline; }
        .table-footer { padding:13px 20px; border-top:1px solid var(--border-soft); color:var(--text-muted); font-size:11.5px; }

        @media (max-width:1100px) { .metric-grid { grid-template-columns:repeat(3, 1fr); } .filter-grid { grid-template-columns:repeat(3, minmax(150px, 1fr)); } }
        @media (max-width:760px) { .metric-grid, .section-grid { grid-template-columns:repeat(2, 1fr); } .filter-grid { grid-template-columns:repeat(2, minmax(140px, 1fr)); } .status-grid { grid-template-columns:repeat(2, 1fr); } }
        @media (max-width:500px) { .metric-grid, .section-grid, .filter-grid { grid-template-columns:1fr; } .reports-title { font-size:21px; } .filter-heading { flex-direction:column; align-items:flex-start; } .header-actions { width:100%; } .btn-report { flex:1; justify-content:center; } }
    </style>

    <div class="reports-header">
        <div>
            <div class="reports-title">{{ __('Reports & Analytics') }}</div>
            <div class="reports-subtitle">{{ __('Understand academic performance, attendance, and students who need attention.') }}</div>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.reports.export', request()->query()) }}" class="btn-report btn-secondary-report">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a2 2 0 01.707.293l5.414 5.414a2 2 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                {{ __('Export Students CSV') }}
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports.index') }}" class="filter-card">
        <div class="filter-heading">
            <div class="filter-title">{{ __('Report Filters') }}</div>
            <div class="filter-period">{{ __('Selected period: :period', ['period' => $selectedPeriod]) }}</div>
        </div>
        <div class="filter-grid">
            <div class="filter-group">
                <label class="filter-label" for="academic_year_id">{{ __('Academic Year') }}</label>
                <select class="filter-select" name="academic_year_id" id="academic_year_id">
                    <option value="">{{ __('All Academic Years') }}</option>
                    @foreach($options['academicYears'] as $year)
                        <option value="{{ $year->id }}" @selected($filters['academic_year_id'] == $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label" for="semester_id">{{ __('Semester') }}</label>
                <select class="filter-select" name="semester_id" id="semester_id">
                    <option value="">{{ __('All Semesters') }}</option>
                    @foreach($options['semesters'] as $semester)
                        <option value="{{ $semester->id }}" @selected($filters['semester_id'] == $semester->id)>{{ $semester->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label" for="grade_id">{{ __('Grade') }}</label>
                <select class="filter-select" name="grade_id" id="grade_id">
                    <option value="">{{ __('All Grades') }}</option>
                    @foreach($options['grades'] as $grade)
                        <option value="{{ $grade->id }}" @selected($filters['grade_id'] == $grade->id)>{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label" for="classroom_id">{{ __('Classroom') }}</label>
                <select class="filter-select" name="classroom_id" id="classroom_id">
                    <option value="">{{ __('All Classrooms') }}</option>
                    @foreach($options['classrooms'] as $classroom)
                        <option value="{{ $classroom->id }}" @selected($filters['classroom_id'] == $classroom->id)>{{ $classroom->name }} — {{ $classroom->grade?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label" for="subject_id">{{ __('Subject') }}</label>
                <select class="filter-select" name="subject_id" id="subject_id">
                    <option value="">{{ __('All Subjects') }}</option>
                    @foreach($options['subjects'] as $subject)
                        <option value="{{ $subject->id }}" @selected($filters['subject_id'] == $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label" for="search">{{ __('Student Search') }}</label>
                <input class="filter-input" type="search" name="search" id="search" value="{{ $filters['search'] }}" placeholder="{{ __('Name or email') }}">
            </div>
            <div class="filter-group">
                <label class="filter-label" for="date_from">{{ __('From Date') }}</label>
                <input class="filter-input" type="date" name="date_from" id="date_from" value="{{ $filters['date_from'] }}">
            </div>
            <div class="filter-group">
                <label class="filter-label" for="date_to">{{ __('To Date') }}</label>
                <input class="filter-input" type="date" name="date_to" id="date_to" value="{{ $filters['date_to'] }}">
            </div>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn-report btn-primary-report filter-submit">{{ __('Apply Filters') }}</button>
            @if(request()->query())
                <a href="{{ route('admin.reports.index') }}" class="clear-link">{{ __('Clear Filters') }}</a>
            @endif
        </div>
    </form>

    <div class="metric-grid">
        <div class="metric-card"><div class="metric-label">{{ __('Total Students') }}</div><div class="metric-value">{{ number_format($metrics['students']) }}</div><div class="metric-caption">{{ __('Matching selected filters') }}</div></div>
        <div class="metric-card"><div class="metric-label">{{ __('Average Score') }}</div><div class="metric-value">{{ $metrics['average_score'] === null ? '—' : $metrics['average_score'].'%' }}</div><div class="metric-caption">{{ __('Across subject summaries') }}</div></div>
        <div class="metric-card"><div class="metric-label">{{ __('Subject Pass Rate') }}</div><div class="metric-value">{{ $metrics['pass_rate'] === null ? '—' : $metrics['pass_rate'].'%' }}</div><div class="metric-caption">{{ __('Passing grade is D or higher') }}</div></div>
        <div class="metric-card"><div class="metric-label">{{ __('Attendance Rate') }}</div><div class="metric-value">{{ $metrics['attendance_rate'] === null ? '—' : $metrics['attendance_rate'].'%' }}</div><div class="metric-caption">{{ __('Present and late; excused excluded') }}</div></div>
        <div class="metric-card"><div class="metric-label">{{ __('Grade Completion') }}</div><div class="metric-value">{{ $metrics['grade_completion'] === null ? '—' : $metrics['grade_completion'].'%' }}</div><div class="metric-caption">{{ $completedSummaryCount }} / {{ $expectedSummaryCount }} {{ __('subject summaries') }}</div></div>
        <div class="metric-card"><div class="metric-label">{{ __('Need Attention') }}</div><div class="metric-value {{ $metrics['attention'] > 0 ? 'danger' : 'warning' }}">{{ number_format($metrics['attention']) }}</div><div class="metric-caption">{{ __('Low scores, missing grades, or attendance') }}</div></div>
    </div>

    <div class="section-grid">
        <div class="report-card">
            <div class="report-card-header"><div><div class="report-card-title">{{ __('Classroom Performance') }}</div><div class="report-card-meta">{{ __('Average score and subject pass rate by classroom') }}</div></div></div>
            <div class="report-card-body">
                @if($classroomPerformance->isEmpty())
                    <div class="empty-report">{{ __('No academic data is available for the selected filters.') }}</div>
                @else
                    <div class="bar-list">
                        @foreach($classroomPerformance as $classroom)
                            <div class="bar-row"><div class="bar-label" title="{{ $classroom['classroom'] }}">{{ $classroom['classroom'] }}</div><div class="bar-track"><div class="bar-fill" style="width:{{ min(100, $classroom['average']) }}%"></div></div><div class="bar-value">{{ $classroom['average'] }}%</div></div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="report-card">
            <div class="report-card-header"><div><div class="report-card-title">{{ __('Grade Distribution') }}</div><div class="report-card-meta">{{ __('Subject summaries in the selected period') }}</div></div></div>
            <div class="report-card-body">
                @if($gradeDistribution->sum() === 0)
                    <div class="empty-report">{{ __('No academic data is available for the selected filters.') }}</div>
                @else
                    <div class="distribution-grid">
                        @foreach($gradeDistribution as $letter => $count)
                            <div class="distribution-item"><div class="distribution-count">{{ $count }}</div><div class="distribution-bar-wrap"><div class="distribution-bar" style="height:{{ max(4, $count / $maxGradeDistribution * 100) }}%"></div></div><div class="distribution-label">{{ $letter }}</div></div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="report-card">
            <div class="report-card-header"><div><div class="report-card-title">{{ __('Attendance Breakdown') }}</div><div class="report-card-meta">{{ __('Recorded attendance in the selected period') }}</div></div><div class="metric-caption">{{ $attendanceStats['total'] }} {{ __('records') }}</div></div>
            <div class="report-card-body">
                <div class="status-grid">
                    <div class="status-item"><div class="status-value">{{ $attendanceStats['present'] }}</div><div class="status-label">{{ __('Present') }}</div></div>
                    <div class="status-item"><div class="status-value">{{ $attendanceStats['absent'] }}</div><div class="status-label">{{ __('Absent') }}</div></div>
                    <div class="status-item"><div class="status-value">{{ $attendanceStats['late'] }}</div><div class="status-label">{{ __('Late') }}</div></div>
                    <div class="status-item"><div class="status-value">{{ $attendanceStats['excused'] }}</div><div class="status-label">{{ __('Excused') }}</div></div>
                </div>
                <div class="table-footer" style="padding:14px 0 0; border-top:0;">{{ __('Pending justifications') }}: <strong>{{ $metrics['pending_justifications'] }}</strong></div>
            </div>
        </div>
        <div class="report-card">
            <div class="report-card-header"><div><div class="report-card-title">{{ __('Attendance by Classroom') }}</div><div class="report-card-meta">{{ __('Attendance rate and recorded entries') }}</div></div></div>
            <div class="report-card-body">
                @if($attendanceByClassroom->isEmpty())
                    <div class="empty-report">{{ __('No attendance data is available for the selected filters.') }}</div>
                @else
                    <div class="bar-list">
                        @foreach($attendanceByClassroom as $classroom)
                            <div class="bar-row"><div class="bar-label" title="{{ $classroom['classroom'] }}">{{ $classroom['classroom'] }}</div><div class="bar-track"><div class="bar-fill" style="width:{{ min(100, $classroom['rate'] ?? 0) }}%"></div></div><div class="bar-value">{{ $classroom['rate'] === null ? '—' : $classroom['rate'].'%' }}</div></div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="report-card table-card">
        <div class="report-card-header"><div><div class="report-card-title">{{ __('Students Requiring Attention') }}</div><div class="report-card-meta">{{ __('Students with low scores, missing grades, failed subjects, or attendance below 90%.') }}</div></div><span class="badge {{ $metrics['attention'] > 0 ? 'badge-attention' : 'badge-done' }}">{{ $metrics['attention'] }}</span></div>
        @if($attentionRows->isEmpty())
            <div class="empty-report">{{ __('No students currently require attention.') }}</div>
        @else
            <div class="table-wrap"><table><thead><tr><th>{{ __('Student') }}</th><th>{{ __('Classroom') }}</th><th>{{ __('Average Score') }}</th><th>{{ __('Grade') }}</th><th>{{ __('Failed Subjects') }}</th><th>{{ __('Missing Subjects') }}</th><th>{{ __('Attendance') }}</th><th>{{ __('Action') }}</th></tr></thead><tbody>
                @foreach($attentionRows as $row)
                    <tr><td><div class="student-name">{{ $row['name'] }}</div><div class="student-email">{{ $row['email'] }}</div></td><td>{{ $row['classroom'] }}</td><td class="score">{{ $row['average'] === null ? '—' : $row['average'].'%' }}</td><td><span class="badge badge-{{ strtolower($row['letter_grade']) === '—' ? 'none' : strtolower($row['letter_grade']) }}">{{ $row['letter_grade'] }}</span></td><td>{{ $row['failed_subjects'] }}</td><td>{{ $row['missing_subjects'] }}</td><td>{{ $row['attendance_rate'] === null ? '—' : $row['attendance_rate'].'%' }}</td><td>
                        @if($filters['semester_id'])
                            <a class="small-action" target="_blank" href="{{ route('admin.reports.student-report-card', ['student' => $row['id'], 'semester_id' => $filters['semester_id']]) }}">{{ __('Report Card') }}</a>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </div>

    <div class="report-card table-card">
        <div class="report-card-header"><div><div class="report-card-title">{{ __('Student Detail') }}</div><div class="report-card-meta">{{ __('Showing :count students ordered by attention priority.', ['count' => $studentRows->count()]) }}</div></div></div>
        @if($studentRows->isEmpty())
            <div class="empty-report">{{ __('No students match the selected filters.') }}</div>
        @else
            <div class="table-wrap"><table><thead><tr><th>{{ __('Student') }}</th><th>{{ __('Classroom') }}</th><th>{{ __('Average Score') }}</th><th>{{ __('Letter Grade') }}</th><th>{{ __('Failed Subjects') }}</th><th>{{ __('Missing Subjects') }}</th><th>{{ __('Attendance') }}</th><th>{{ __('Action') }}</th></tr></thead><tbody>
                @foreach($studentRows as $row)
                    <tr><td><div class="student-name">{{ $row['name'] }}</div><div class="student-email">{{ $row['email'] }}</div></td><td>{{ $row['classroom'] }}</td><td class="score">{{ $row['average'] === null ? '—' : $row['average'].'%' }}</td><td><span class="badge badge-{{ strtolower($row['letter_grade']) === '—' ? 'none' : strtolower($row['letter_grade']) }}">{{ $row['letter_grade'] }}</span></td><td>{{ $row['failed_subjects'] }}</td><td>{{ $row['missing_subjects'] }}</td><td>{{ $row['attendance_rate'] === null ? '—' : $row['attendance_rate'].'%' }}</td><td>
                        @if($filters['semester_id'])
                            <a class="small-action" target="_blank" href="{{ route('admin.reports.student-report-card', ['student' => $row['id'], 'semester_id' => $filters['semester_id']]) }}">{{ __('Report Card') }}</a>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td></tr>
                @endforeach
            </tbody></table></div>
            <div class="table-footer">{{ __('Export the filtered student table using the button above.') }}</div>
        @endif
    </div>
</x-layouts.app>
