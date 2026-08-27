<x-layouts.app :pageTitle="__('Schedule Builder')">
    <style>
        .controls-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .controls-bar select {
            padding: 9px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 13.5px;
            font-family: var(--font-body);
            background: var(--surface);
            color: var(--text-primary);
            cursor: pointer;
            min-width: 200px;
        }
        .controls-bar select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 12%, transparent);
        }

        .schedule-grid { overflow-x: auto; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        table.grid-table { width: 100%; border-collapse: collapse; background: var(--surface); }
        .grid-table th, .grid-table td { border: 1px solid var(--border); }
        .grid-table thead th {
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            padding: 13px 18px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            text-align: center;
        }
        .grid-table tbody th {
            background: var(--surface-2);
            color: var(--text-secondary);
            padding: 10px 16px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            min-width: 90px;
            text-align: center;
        }
        .grid-cell {
            min-width: 170px;
            height: 80px;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }
        .grid-cell.empty {
            cursor: pointer;
            transition: background 0.15s;
        }
        .grid-cell.empty:hover { background: var(--primary-tint); }
        .grid-cell.filled { background: var(--success-tint); }
        .cell-add { font-size: 24px; color: var(--primary-light); line-height: 1; }
        .cell-content { display: flex; flex-direction: column; gap: 2px; align-items: center; }
        .cell-subject { color: var(--primary-dark); font-size: 12.5px; font-weight: 700; }
        .cell-teacher { color: var(--text-secondary); font-size: 11px; }
        .cell-time { color: var(--text-muted); font-size: 10.5px; }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.5);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--surface);
            border-radius: 18px;
            padding: 32px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.18);
            /* A tall form can exceed a phone screen; scroll inside the modal
               rather than letting it run off the viewport. */
            max-height: calc(100dvh - 32px);
            overflow-y: auto;
        }
        /* Gutter so the modal never sits flush against the screen edges. */
        @media (max-width: 520px) {
            .modal-overlay { padding: 16px; }
            .modal { padding: 24px 20px; }
        }
        .modal-header { margin-bottom: 24px; }
        .modal-header h3 {
            font-family: var(--font-display);
            font-size: 19px;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        .modal-header p { font-size: 13px; color: var(--text-muted); }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-group select,
        .form-group input[type="time"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 13.5px;
            font-family: var(--font-body);
            color: var(--text-primary);
            background: var(--surface);
        }
        .form-group select:focus,
        .form-group input[type="time"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 12%, transparent);
        }
        /* Local .form-row is unlayered, so it beats app.css's @layer components
           collapse rule — it needs its own breakpoint here. */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 640px) { .form-row { grid-template-columns: 1fr; } }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; }
        .btn-cancel {
            padding: 10px 20px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 13.5px;
            font-family: var(--font-body);
            background: var(--surface);
            color: var(--text-secondary);
            cursor: pointer;
        }
        .btn-cancel:hover { border-color: var(--text-muted); }
        .btn-save {
            padding: 10px 24px;
            background: var(--primary);
            color: var(--on-primary);
            border: none;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            box-shadow: 0 2px 8px color-mix(in srgb, var(--primary) 30%, transparent);
        }
        .btn-save:hover { background: var(--primary-dark); }
        .error-banner {
            margin-bottom: 16px;
            padding: 12px 16px;
            background: var(--danger-tint);
            border: 1px solid var(--danger-border);
            border-radius: 10px;
            color: var(--danger-dark);
            font-size: 13px;
        }
        .hint-box {
            padding: 48px 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
            background: var(--surface);
            border-radius: 14px;
            border: 1px solid var(--border);
        }
    </style>

    {{-- Page header --}}
    <div style="margin-bottom: 24px;">
        <div class="rtl-display" style="font-family: var(--font-display); font-size:22px; font-weight:700; color:var(--text-primary); margin-bottom:4px;">
            {{ __("Schedule Builder") }}
        </div>
        <div style="font-size:13px; color:var(--text-secondary);">
            {{ __("Build and manage weekly class timetables. Select a classroom and semester to view or edit.") }}
        </div>
    </div>

    {{-- Validation error --}}
    @if($errors->any())
        <div class="error-banner">{{ $errors->first() }}</div>
    @endif

    {{-- Classroom + Semester selectors — submits as GET to reload page with data --}}
    <form method="GET" action="{{ route('admin.schedule.index') }}" class="controls-bar">
        <select name="classroom_id" data-auto-submit>
            <option value="">{{ __("— Select Classroom —") }}</option>
            @foreach($classrooms as $cr)
                <option value="{{ $cr->id }}" @selected($classroomId == $cr->id)>
                    {{ $cr->name }} ({{ $cr->grade->name }})
                </option>
            @endforeach
        </select>
        <select name="semester_id" data-auto-submit>
            <option value="">{{ __('— Select Semester —') }}</option>
            @foreach($semesters as $sem)
                <option value="{{ $sem->id }}" @selected($semesterId == $sem->id)>
                    {{ $sem->name }} — {{ $sem->academicYear->name }}
                </option>
            @endforeach
        </select>
        <a href="{{ route('admin.semesters.create', ['academic_year_id' => request('academic_year_id')]) }}" class="btn btn-outline" style="white-space: nowrap;">+ {{ __('Add Semester') }}</a>
    </form>

    @if(!$classroomId || !$semesterId)
        <div class="hint-box">{{ __("Select a classroom and semester above to view or build the schedule.") }}</div>
    @else
        {{-- Schedule grid --}}
        <div class="schedule-grid">
            <table class="grid-table">
                <thead>
                    <tr>
                        <th style="min-width:90px;">{{ __("Period") }}</th>
                        <th>{{ __("Sunday") }}</th>
                        <th>{{ __("Monday") }}</th>
                        <th>{{ __("Tuesday") }}</th>
                        <th>{{ __("Wednesday") }}</th>
                        <th>{{ __("Thursday") }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(range(1, 8) as $period)
                        <tr>
                            <th>P{{ $period }}</th>
                            @foreach(['sunday','monday','tuesday','wednesday','thursday'] as $day)
                                @php $slot = $slots[$day . '_' . $period] ?? null; @endphp
                                <td class="grid-cell {{ $slot ? 'filled' : 'empty' }}"
                                    @if(!$slot) data-slot-day="{{ $day }}" data-slot-period="{{ $period }}" @endif>
                                    @if($slot)
                                        <div class="cell-content">
                                            <span class="cell-subject">{{ $slot->subject->name }}</span>
                                            <span class="cell-teacher">{{ $slot->teacher->name }}</span>
                                            <span class="cell-time">{{ substr($slot->start_time, 0, 5) }} – {{ substr($slot->end_time, 0, 5) }}</span>
                                        </div>
                                    @else
                                        <div class="cell-content">
                                            <span class="cell-add">+</span>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Assignment modal --}}
    <div class="modal-overlay" id="slotModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle">{{ __("Assign Slot") }}</h3>
                <p id="modalSubtitle">{{ __("Choose a teacher and subject for this period.") }}</p>
            </div>

            <form method="POST" action="{{ route('admin.schedule.store') }}">
                @csrf
                <input type="hidden" name="classroom_id" value="{{ $classroomId }}">
                <input type="hidden" name="semester_id"  value="{{ $semesterId }}">
                <input type="hidden" name="day_of_week"  id="modalDay">
                <input type="hidden" name="period_number" id="modalPeriod">

                <div class="form-group">
                    <label>{{ __("Teacher") }}</label>
                    <select name="teacher_user_id" required>
                        <option value="">{{ __("— Select Teacher —") }}</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ __("Subject") }}</label>
                    <select name="subject_id" required>
                        <option value="">{{ __("— Select Subject —") }}</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }} ({{ $subject->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>{{ __("Start Time") }}</label>
                        <input type="time" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label>{{ __("End Time") }}</label>
                        <input type="time" name="end_time" required>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" data-close-slot-modal>{{ __("Cancel") }}</button>
                    <button type="submit" class="btn-save">{{ __("Save Slot") }}</button>
                </div>
            </form>
        </div>
    </div>

    @if($errors->any() && old('day_of_week'))
        <div data-reopen-slot data-day="{{ old('day_of_week') }}" data-period="{{ old('period_number') }}" hidden></div>
    @endif
</x-layouts.app>
