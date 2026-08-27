<x-layouts.app :pageTitle="__('My Schedule')">
    <style>
        .page-header { margin-bottom: 24px; }
        .page-header h2 { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .page-header p { font-size: 13px; color: var(--text-secondary); }
        .classroom-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            background: var(--success-tint);
            color: var(--success-text);
            font-size: 12px;
            font-weight: 600;
            margin-top: 6px;
        }
        .day-tabs { display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 4px; }
        .day-tab {
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            padding: 12px 20px; border-radius: 12px; background: var(--surface);
            border: 1.5px solid var(--border); cursor: pointer; text-decoration: none;
            transition: all 0.2s; min-width: 80px; flex-shrink: 0;
        }
        .day-tab:hover { border-color: var(--primary-light); background: var(--violet-wash); }
        .day-tab.active { background: var(--primary); border-color: var(--primary); color: var(--on-primary); box-shadow: 0 4px 12px color-mix(in srgb, var(--primary) 30%, transparent); }
        .day-tab .day-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
        .day-tab.active .day-name { color: var(--on-primary); }
        .day-tab .day-count {
            font-size: 11px; padding: 2px 8px; border-radius: 20px;
            background: var(--border-soft); color: var(--text-muted); font-weight: 600;
        }
        .day-tab.active .day-count { background: color-mix(in srgb, var(--on-primary) 25%, transparent); color: var(--on-primary); }
        .slots-list { display: flex; flex-direction: column; gap: 12px; }
        .slot-card {
            display: flex; align-items: center; gap: 16px;
            background: var(--surface); border-radius: 14px; padding: 20px;
            border: 1px solid var(--border-soft); box-shadow: var(--shadow-card);
            transition: all 0.2s;
        }
        .slot-card:hover { border-color: var(--primary-light); box-shadow: 0 4px 14px color-mix(in srgb, var(--primary) 8%, transparent); }
        .period-badge {
            width: 48px; height: 48px; border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            display: flex; align-items: center; justify-content: center;
            color: var(--on-primary); font-size: 14px; font-weight: 700; flex-shrink: 0;
            box-shadow: 0 4px 12px color-mix(in srgb, var(--primary) 30%, transparent);
        }
        .slot-info { flex: 1; min-width: 0; }
        .slot-subject { font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .slot-meta { font-size: 13px; color: var(--text-secondary); display: flex; align-items: center; gap: 8px; }
        .slot-meta svg { width: 14px; height: 14px; flex-shrink: 0; }
        .slot-time { font-size: 13px; font-weight: 600; color: var(--primary); white-space: nowrap; }
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-faint); }
        .empty-state p { font-size: 14px; }
    </style>

    <div class="page-header">
        <h2>{{ __("My Schedule") }}</h2>
        <p>{{ __("Your weekly class timetable") }}</p>
        @if($classroom)
            <span class="classroom-badge">{{ $classroom->grade->name }} &mdash; {{ $classroom->name }}</span>
        @endif
    </div>

    @if(!$classroom)
        <div class="empty-state">
            <svg width="48" height="48" fill="none" stroke="var(--border)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <p>{{ __("You are not assigned to a classroom yet.") }}</p>
        </div>
    @else
        <div class="day-tabs">
            @foreach($days as $day)
                @php
                    $count = $allSlots->get($day, collect())->count();
                    $isSelected = $day === $selectedDay;
                @endphp
                <a href="{{ route('student.schedule', ['day' => $day]) }}" class="day-tab {{ $isSelected ? 'active' : '' }}">
                    <span class="day-name">{{ __(ucfirst($day)) }}</span>
                    <span class="day-count">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        @if($slots->count() > 0)
            <div class="slots-list">
                @foreach($slots as $slot)
                    <div class="slot-card">
                        <div class="period-badge">P{{ $slot->period_number }}</div>
                        <div class="slot-info">
                            <div class="slot-subject">{{ $slot->subject->name }}</div>
                            <div class="slot-meta">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $slot->teacher->name }}
                            </div>
                        </div>
                        <div class="slot-time">
                            {{ substr($slot->start_time, 0, 5) }} &ndash; {{ substr($slot->end_time, 0, 5) }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <svg width="48" height="48" fill="none" stroke="var(--border)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p>{{ __('No classes on :day', ['day' => __(ucfirst($selectedDay))]) }}</p>
            </div>
        @endif
    @endif
</x-layouts.app>
