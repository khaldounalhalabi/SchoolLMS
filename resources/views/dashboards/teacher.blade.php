<x-layouts.app :pageTitle="__('Teacher Dashboard')">
    <style>
        /* Fixed-dark hero: white text on a deep gradient in BOTH themes.
           Deliberately literal hex — tokens that lighten in dark mode would
           put light text on a light fill here. */
        .welcome-banner {
            background: linear-gradient(135deg, #1C2321 0%, #1E3722 50%, #1E3722 100%);
            border-radius: 16px;
            padding: 28px 32px;
            color: white;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }
        .placeholder-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        @media (max-width: 640px) { .placeholder-grid { grid-template-columns: 1fr; } }
        .data-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--surface-2);
            font-size: 13.5px;
            color: var(--text-strong);
        }
        .data-item:last-child { border-bottom: none; }
        .schedule-card-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            background: linear-gradient(135deg, var(--primary-dark), #6d28d9);
            border-radius: 12px;
            color: var(--on-primary);
            text-decoration: none;
            margin-bottom: 16px;
            transition: all 0.2s;
        }
        .schedule-card-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(67,56,202,0.4);
        }
        .slot-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px; height: 32px;
            border-radius: 8px;
            background: var(--primary-tint);
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .assignment-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--surface-2);
        }
        .assignment-row:last-child { border-bottom: none; }
        .assignment-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            margin-top: 6px;
            flex-shrink: 0;
        }
    </style>

    <div class="welcome-banner">
        <div style="position: relative; z-index: 1;">
            <div style="font-size: 13px; color: #a5b4fc; font-weight: 500; margin-bottom: 4px;">
                {{ now()->format('l, F j, Y') }}
            </div>
            <h2 class="rtl-display" style="font-family: var(--font-display); font-size: 24px; font-weight: 700; margin-bottom: 6px;">
                {{ __("Good :time, :name!", ['time' => __(now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening')), 'name' => explode(' ', $user->name)[0]]) }}
            </h2>
            <p style="font-size: 14px; color: var(--primary-light);">
                {{ __("Ready for today's classes? Here's your teaching overview.") }}
            </p>
        </div>
    </div>

    {{-- My Schedule quick link --}}
    @if($todaySlots->count() > 0)
    <a href="{{ route('teacher.schedule') }}" class="schedule-card-link">
        <div>
            <div style="font-size: 13px; opacity: 0.8; margin-bottom: 4px;">{{ __("Today's Schedule") }}</div>
            <div style="font-size: 18px; font-weight: 700;">{{ __(':count classes today', ['count' => $todaySlots->count()]) }}</div>
        </div>
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
    @endif

    <div class="placeholder-grid">
        {{-- My Classes --}}
        <x-dashboard.card :title="__('My Classes')" :subtitle="__(':count assigned', ['count' => $classrooms->count()])" iconBg="var(--info-tint)">
            <x-slot:icon>
                <svg width="20" height="20" fill="none" stroke="var(--info)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </x-slot:icon>
            @forelse($classrooms as $classroom)
                <div class="data-item">
                    <div style="flex: 1;">
                        <div style="font-weight: 600;">{{ $classroom->name }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $classroom->grade->name }} &middot; {{ $classroom->student_profiles_count }} students</div>
                    </div>
                </div>
            @empty
                <div class="dash-card-empty">
                    <svg width="32" height="32" fill="none" stroke="var(--border)" viewBox="0 0 24 24" style="margin: 0 auto 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    {{ __("No classrooms assigned yet") }}
                </div>
            @endforelse
        </x-dashboard.card>

        {{-- My Schedule --}}
        <x-dashboard.card :title="__('Today\'s Schedule')" iconBg="var(--success-tint)">
            <x-slot:icon>
                <svg width="20" height="20" fill="none" stroke="var(--success)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </x-slot:icon>
            <x-slot:subtitle>{{ now()->format('l') }} &middot; <a href="{{ route('teacher.schedule') }}" style="color: var(--primary); text-decoration: none;">{{ __("View full week") }}</a></x-slot:subtitle>
            @forelse($todaySlots as $slot)
                <div class="data-item">
                    <div class="slot-badge">P{{ $slot->period_number }}</div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600;">{{ $slot->subject->name }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $slot->classroom->name }} &middot; {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</div>
                    </div>
                </div>
            @empty
                <div class="dash-card-empty">
                    <svg width="32" height="32" fill="none" stroke="var(--border)" viewBox="0 0 24 24" style="margin: 0 auto 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ __("No classes scheduled for today") }}
                </div>
            @endforelse
        </x-dashboard.card>

        {{-- My Subject Assignments --}}
        <x-dashboard.card :title="__('Subject Assignments')" :subtitle="__(':count assigned', ['count' => $assignments->count()])" iconBg="var(--warning-tint)">
            <x-slot:icon>
                <svg width="20" height="20" fill="none" stroke="var(--warning)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </x-slot:icon>
            @forelse($assignments as $i => $assignment)
                @php $colors = ['var(--primary)','var(--success)','var(--info)','var(--warning)','var(--violet)','#ec4899']; @endphp
                <div class="assignment-row">
                    <div class="assignment-dot" style="background: {{ $colors[$i % count($colors)] }};"></div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600;">{{ $assignment->subject->name }} <span style="font-size: 11px; color: var(--text-muted);">({{ $assignment->subject->code }})</span></div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $assignment->classroom->name }} &middot; {{ $assignment->classroom->grade->name }}</div>
                    </div>
                </div>
            @empty
                <div class="dash-card-empty">
                    <svg width="32" height="32" fill="none" stroke="var(--border)" viewBox="0 0 24 24" style="margin: 0 auto 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    {{ __("No subject assignments yet") }}
                </div>
            @endforelse
        </x-dashboard.card>

        {{-- Attendance Entry --}}
        <x-dashboard.card :title="__('Attendance Entry')" :subtitle="__('Mark today\'s attendance')" iconBg="var(--primary-tint)">
            <x-slot:icon>
                <svg width="20" height="20" fill="none" stroke="var(--primary)" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot:icon>
            <div class="dash-card-empty">
                <svg width="32" height="32" fill="none" stroke="var(--border)" viewBox="0 0 24 24" style="margin: 0 auto 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ __("Attendance module coming soon") }}
            </div>
        </x-dashboard.card>
    </div>
</x-layouts.app>
