<x-layouts.app :pageTitle="__('Take Attendance')">
    <style>
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: var(--surface-2); }
        th {
            padding: 12px 16px;
            text-align: start;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        th:first-child { padding-inline-start: 20px; }
        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--surface-2);
            font-size: 13.5px;
            color: var(--text-strong);
        }
        td:first-child { padding-inline-start: 20px; }
        tbody tr:hover { background: var(--surface-2); }
        tbody tr:last-child td { border-bottom: none; }

        .student-cell { display: flex; align-items: center; gap: 10px; }
        .student-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--violet-dark));
            display: flex; align-items: center; justify-content: center;
            color: var(--on-primary); font-size: 12px; font-weight: 700; flex-shrink: 0;
        }
        .student-name { font-weight: 600; color: var(--text-primary); font-size: 13.5px; }

        .radio-group { display: flex; gap: 8px; flex-wrap: wrap; }
        .radio-label {
            display: flex; align-items: center; gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            border: 1.5px solid var(--border);
            font-size: 12px; font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .radio-label input[type="radio"] { display: none; }
        .radio-label.present { border-color: var(--success); color: var(--success-text); }
        .radio-label.present:has(input:checked) { background: var(--success-border); border-color: var(--success); }
        .radio-label.absent  { border-color: var(--danger); color: var(--danger-text); }
        .radio-label.absent:has(input:checked) { background: var(--danger-tint); border-color: var(--danger); }
        .radio-label.late    { border-color: var(--warning); color: var(--warning-text); }
        .radio-label.late:has(input:checked) { background: var(--warning-tint); border-color: var(--warning); }
        .radio-label.excused { border-color: var(--primary-light); color: var(--primary-dark); }
        .radio-label.excused:has(input:checked) { background: var(--primary-tint); border-color: var(--primary-light); }

        .submit-bar {
            padding: 16px 20px;
            border-top: 1px solid var(--border-soft);
            display: flex; align-items: center; justify-content: space-between;
        }
        .btn-submit {
            padding: 10px 28px;
            background: var(--primary);
            color: var(--on-primary);
            border: none;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 8px color-mix(in srgb, var(--primary) 30%, transparent);
        }
        .btn-submit:hover { background: var(--primary-dark); transform: translateY(-1px); }

        .empty-state {
            text-align: center; padding: 56px 20px;
        }
        .empty-icon {
            width: 56px; height: 56px;
            background: var(--border-soft); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
        }

        .bulk-select-bar {
            display: flex; gap: 8px; align-items: center;
        }
        .btn-quick {
            padding: 5px 12px;
            border-radius: 20px;
            border: 1.5px solid var(--border);
            font-size: 12px; font-weight: 600;
            cursor: pointer;
            background: var(--surface);
            font-family: var(--font-body);
            transition: all 0.15s;
        }
        .btn-quick:hover { border-color: var(--primary); color: var(--primary); }
    </style>

    <x-page-header
        :title="__('Take Attendance')"
        :description="__('Select a classroom and date to record student attendance')"
    />

    @if(session('success'))
        <div style="background: var(--success-border); border: 1px solid var(--success-border); color: var(--success-text); padding: 12px 16px; border-radius: 10px; font-size: 13.5px; font-weight: 500; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('teacher.attendance') }}">
        <div class="filter-card">
            <div class="filter-group">
                <label class="filter-label">{{ __("Classroom") }}</label>
                <select name="classroom_id" class="filter-select" data-auto-submit>
                    <option value="">{{ __("— Select Classroom —") }}</option>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" @selected($selectedClassroomId == $classroom->id)>
                            {{ $classroom->name }} ({{ $classroom->grade->name ?? '—' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">{{ __("Date") }}</label>
                <input type="date" name="date" class="filter-input" value="{{ $selectedDate }}" data-auto-submit>
            </div>
        </div>
    </form>

    @if($selectedClassroomId && $students->isEmpty())
        <div class="table-card">
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="24" height="24" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                </div>
                <div class="empty-title">{{ __("No Students Found") }}</div>
                <div class="empty-desc">{{ __("This classroom has no enrolled students.") }}</div>
            </div>
        </div>
    @elseif($students->isNotEmpty())
        <form method="POST" action="{{ route('teacher.attendance.store') }}">
            @csrf
            <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
            <input type="hidden" name="date" value="{{ $selectedDate }}">

            @error('statuses')
                <div style="background: var(--danger-tint); border: 1px solid var(--danger-border); color: var(--danger-text); padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 12px;">{{ $message }}</div>
            @enderror

            <div class="table-card">
                <div class="table-header">
                    <div>
                        <div class="table-title">
                            {{ $classrooms->firstWhere('id', $selectedClassroomId)?->name ?? __('Classroom') }}
                            — {{ \Carbon\Carbon::parse($selectedDate)->format('D, M j, Y') }}
                        </div>
                        <div class="table-meta">{{ __(":count students", ['count' => $students->count()]) }}</div>
                    </div>
                    <div class="bulk-select-bar">
                        <span style="font-size: 12px; color: var(--text-muted); margin-inline-end: 4px;">{{ __("Mark all:") }}</span>
                        <button type="button" class="btn-quick" data-mark-all="present">{{ __("Present") }}</button>
                        <button type="button" class="btn-quick" data-mark-all="absent">{{ __("Absent") }}</button>
                        <button type="button" class="btn-quick" data-mark-all="late">{{ __("Late") }}</button>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __("Student") }}</th>
                                <th>{{ __("Status") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $i => $profile)
                                @php $existing = $existingAttendance->get($profile->user_id); @endphp
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 12.5px;">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="student-avatar">{{ strtoupper(substr($profile->student->name ?? '?', 0, 2)) }}</div>
                                            <div class="student-name">{{ $profile->student->name ?? '—' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="radio-group" data-student="{{ $profile->user_id }}">
                                            @foreach(['present' => __('Present'), 'absent' => __('Absent'), 'late' => __('Late'), 'excused' => __('Excused')] as $val => $label)
                                                <label class="radio-label {{ $val }}">
                                                    <input type="radio"
                                                           name="statuses[{{ $profile->user_id }}]"
                                                           value="{{ $val }}"
                                                           @checked(($existing?->status?->value ?? 'present') === $val)>
                                                    {{ $label }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="submit-bar">
                    <div style="font-size: 13px; color: var(--text-secondary);">
                        @if($existingAttendance->isNotEmpty())
                            <span style="color: var(--success); font-weight: 600;">{{ __("✓ Attendance already recorded") }}</span> {{ __("— submitting will update existing records.") }}
                        @else
                            {{ __("Recording attendance for") }} <strong>{{ $students->count() }}</strong> {{ __("students.") }}
                        @endif
                    </div>
                    <button type="submit" class="btn-submit">{{ __("Save Attendance") }}</button>
                </div>
            </div>
        </form>
    @else
        <div class="table-card">
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="24" height="24" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="empty-title">{{ __("Select a Classroom") }}</div>
                <div class="empty-desc">{{ __("Choose a classroom and date above to start recording attendance.") }}</div>
            </div>
        </div>
    @endif

</x-layouts.app>
