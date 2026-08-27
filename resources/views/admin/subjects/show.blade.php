<x-layouts.app pageTitle="{{ $subject->name }}">
    <style>
        .back-link {
            font-size: 13px; color: var(--text-secondary); text-decoration: none;
            display: inline-flex; align-items: center; gap: 4px; margin-bottom: 20px;
        }
        .back-link:hover { color: var(--text-primary); }

        .top-row {
            display: flex; align-items: flex-start; justify-content: space-between;
            gap: 12px; flex-wrap: wrap; margin-bottom: 24px;
        }
        .page-title {
            font-family: var(--font-display);
            font-size: 24px; font-weight: 700; color: var(--text-primary);
        }
        .page-subtitle { font-size: 13px; color: var(--text-muted); margin-top: 3px; }
        .btn-edit {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 18px; background: var(--primary); color: var(--on-primary);
            border-radius: 10px; text-decoration: none; font-size: 13px;
            font-weight: 600; font-family: var(--font-body);
            transition: all 0.2s; box-shadow: 0 2px 8px color-mix(in srgb, var(--primary) 30%, transparent);
            white-space: nowrap;
        }
        .btn-edit:hover { background: var(--primary-dark); transform: translateY(-1px); }

        /* Stats strip */
        .stats-strip {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 14px; margin-bottom: 24px;
        }
        @media (max-width: 768px) { .stats-strip { grid-template-columns: 1fr; } }
        .stat-box {
            background: var(--surface); border-radius: 12px; border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-card);
            padding: 18px 20px; display: flex; align-items: center; gap: 14px;
        }
        .stat-icon {
            width: 40px; height: 40px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .stat-value { font-size: 22px; font-weight: 700; color: var(--text-primary); line-height: 1; }
        .stat-label { font-size: 12px; color: var(--text-muted); margin-top: 3px; }

        /* Detail card */
        .detail-card {
            background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-card); overflow: hidden;
            max-width: 560px; margin-bottom: 28px;
        }
        .detail-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 15px 24px; border-bottom: 1px solid var(--surface-2);
        }
        .detail-label { font-size: 13px; font-weight: 500; color: var(--text-muted); }
        .detail-value { font-size: 14px; font-weight: 600; color: var(--text-primary); }
        .code-chip {
            font-family: monospace; font-size: 13px; font-weight: 700;
            background: var(--border-soft); color: var(--text-primary); border: 1px solid var(--border);
            padding: 4px 12px; border-radius: 6px;
        }

        /* Assignments table */
        .section-title {
            font-family: var(--font-display);
            font-size: 17px; font-weight: 700; color: var(--text-primary); margin-bottom: 12px;
        }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: var(--surface-2); }
        th {
            padding: 11px 16px; text-align: start;
            font-size: 11px; font-weight: 700; color: var(--text-muted);
            letter-spacing: 0.8px; text-transform: uppercase;
        }
        th:first-child { padding-inline-start: 20px; }
        td {
            padding: 13px 16px; font-size: 13.5px; color: var(--text-primary);
            border-bottom: 1px solid var(--surface-2);
        }
        td:first-child { padding-inline-start: 20px; }
        tr:last-child td { border-bottom: none; }
        .avatar {
            width: 30px; height: 30px; border-radius: 8px;
            background: var(--primary-tint); color: var(--primary);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; margin-inline-end: 8px;
            vertical-align: middle;
        }
        .classroom-chip {
            display: inline-block; padding: 2px 9px;
            background: var(--success-tint); color: var(--success-dark);
            border: 1px solid var(--success-border); border-radius: 6px;
            font-size: 12px; font-weight: 600;
        }
        .year-chip {
            display: inline-block; padding: 2px 9px;
            background: var(--warning-tint); color: var(--warning-text);
            border: 1px solid var(--warning-border); border-radius: 6px;
            font-size: 12px; font-weight: 600;
        }
        .empty-state { text-align: center; padding: 40px; color: var(--text-faint); font-size: 14px; }
    </style>

    <a href="{{ route('admin.subjects.index') }}" class="back-link">
        &larr; Back to Subjects
    </a>

    <div class="top-row">
        <div>
            <div class="page-title">{{ $subject->name }}</div>
            <div class="page-subtitle">{{ $subject->school?->name }}</div>
        </div>
        <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn-edit">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Subject
        </a>
    </div>

    {{-- Stats --}}
    <div class="stats-strip">
        <div class="stat-box">
            <div class="stat-icon" style="background: var(--primary-tint);">
                <svg width="20" height="20" fill="none" stroke="var(--primary)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                </svg>
            </div>
            <div>
                <div class="stat-value">{{ $teacherCount }}</div>
                <div class="stat-label">Teacher{{ $teacherCount !== 1 ? 's' : '' }} assigned</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon" style="background: var(--success-tint);">
                <svg width="20" height="20" fill="none" stroke="var(--success)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <div class="stat-value">{{ $classroomCount }}</div>
                <div class="stat-label">Classroom{{ $classroomCount !== 1 ? 's' : '' }}</div>
            </div>
        </div>

        <div class="stat-box">
            <div class="stat-icon" style="background: var(--warning-tint);">
                <svg width="20" height="20" fill="none" stroke="var(--warning)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <div class="stat-value">{{ $subject->teacherAssignments->count() }}</div>
                <div class="stat-label">Total assignment{{ $subject->teacherAssignments->count() !== 1 ? 's' : '' }}</div>
            </div>
        </div>
    </div>

    {{-- Core details --}}
    <div class="detail-card">
        <div class="detail-row">
            <span class="detail-label">Subject Name</span>
            <span class="detail-value">{{ $subject->name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Subject Code</span>
            <span class="code-chip">{{ $subject->code }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">School</span>
            <span class="detail-value">{{ $subject->school?->name ?? '—' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Created</span>
            <span class="detail-value">{{ $subject->created_at->format('M d, Y') }}</span>
        </div>
    </div>

    {{-- Teacher assignments --}}
    <div class="section-title">{{ __("Teacher Assignments (:count)", ['count' => $subject->teacherAssignments->count()]) }}</div>
    <div class="table-card">
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>{{ __("Teacher") }}</th>
                    <th>{{ __("Classroom") }}</th>
                    <th>{{ __("Grade") }}</th>
                    <th>{{ __("Academic Year") }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subject->teacherAssignments as $assignment)
                    <tr>
                        <td>
                            <span class="avatar">
                                {{ collect(explode(' ', $assignment->teacher->name))->map(fn($w) => strtoupper($w[0]))->take(2)->join('') }}
                            </span>
                            {{ $assignment->teacher->name }}
                        </td>
                        <td><span class="classroom-chip">{{ $assignment->classroom->name }}</span></td>
                        <td style="color: var(--text-secondary);">{{ $assignment->classroom->grade->name }}</td>
                        <td><span class="year-chip">{{ $assignment->academicYear->name }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">No teacher assignments yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</x-layouts.app>
