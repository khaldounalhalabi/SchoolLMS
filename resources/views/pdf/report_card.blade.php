<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVuSans, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background: #fff;
            direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
        }
        .page { padding: 32px 40px; }
        .header-table { width: 100%; border-bottom: 3px solid #4F46E5; padding-bottom: 18px; margin-bottom: 24px; }
        .header-table td { vertical-align: middle; border: none; padding: 0; }
        .school-name { font-size: 20px; font-weight: 700; color: #4F46E5; }
        .report-title { font-size: 14px; color: #64748b; margin-top: 4px; }
        .header-meta { text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; color: #64748b; font-size: 11px; }
        .student-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }
        .info-grid { width: 100%; }
        .info-grid td { padding: 6px 15px 6px 0; vertical-align: top; border: none; }
        .info-item label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; }
        .info-item span { display: block; font-size: 13px; font-weight: 600; color: #1e293b; margin-top: 2px; }
        table.results { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead tr { background: #4F46E5; color: white; }
        th { padding: 10px 12px; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; font-size: 11px; font-weight: 700; }
        td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-a { background: #dcfce7; color: #166534; }
        .badge-b { background: #dbeafe; color: #1e40af; }
        .badge-c { background: #fef9c3; color: #854d0e; }
        .badge-d { background: #fed7aa; color: #9a3412; }
        .badge-f { background: #fee2e2; color: #991b1b; }
        .footer { border-top: 1px solid #e2e8f0; padding-top: 12px; text-align: center; color: #94a3b8; font-size: 10px; }
        .section-title { font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="page">
    <table class="header-table">
        <tr>
            <td>
                <div class="school-name">SchoolLMS</div>
                <div class="report-title">{{ __('Academic Report Card') }}</div>
            </td>
            <td class="header-meta">
                <div>{{ __('Issued:') }} {{ now()->format('Y-m-d') }}</div>
                @if($semester)
                    <div>{{ __('Semester:') }} {{ $semester->name }} — {{ $semester->academicYear->name ?? '' }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="student-info">
        <table class="info-grid">
            <tr>
                <td class="info-item">
                    <label>{{ __('Student Name') }}</label>
                    <span>{{ $student->name }}</span>
                </td>
                <td class="info-item">
                    <label>{{ __('Email Address') }}</label>
                    <span>{{ $student->email }}</span>
                </td>
                @if($student->studentProfile?->classroom)
                    <td class="info-item">
                        <label>{{ __('Classroom') }}</label>
                        <span>{{ $student->studentProfile->classroom->name }}</span>
                    </td>
                    @if($student->studentProfile->classroom->grade)
                        <td class="info-item">
                            <label>{{ __('Grade Level') }}</label>
                            <span>{{ $student->studentProfile->classroom->grade->name }}</span>
                        </td>
                    @endif
                @endif
            </tr>
        </table>
    </div>

    <div class="section-title">{{ __('Results by Subject') }}</div>
    <table class="results">
        <thead>
            <tr>
                <th>{{ __('Subject') }}</th>
                @foreach($examTypes as $et)
                    <th>{{ $et->name }} ({{ $et->weight_percent }}%)</th>
                @endforeach
                <th>{{ __('Weighted Avg') }}</th>
                <th>{{ __('Grade') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summaries as $summary)
                @php
                    $subjectGrades = $grades->get($summary->subject_id, collect());
                    $badgeClass = match($summary->letter_grade) {
                        'A' => 'badge-a', 'B' => 'badge-b', 'C' => 'badge-c',
                        'D' => 'badge-d', default => 'badge-f'
                    };
                @endphp
                <tr>
                    <td>{{ $summary->subject->name }}</td>
                    @foreach($examTypes as $et)
                        @php $g = $subjectGrades->firstWhere('exam_type_id', $et->id); @endphp
                        <td>{{ $g ? $g->score . '/' . $g->max_score : '—' }}</td>
                    @endforeach
                    <td>{{ $summary->weighted_average }}%</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $summary->letter_grade }}</span></td>
                </tr>
            @endforeach
            @if($summaries->isEmpty())
                <tr>
                    <td colspan="{{ 3 + $examTypes->count() }}" style="text-align:center; color:#94a3b8; padding:20px;">
                        {{ __('No results for this semester.') }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        {{ __('Generated automatically by SchoolLMS') }} — {{ now()->format('Y-m-d H:i') }}
    </div>
</div>
</body>
</html>
