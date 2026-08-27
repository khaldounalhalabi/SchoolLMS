<x-layouts.app :pageTitle="__('Student Dashboard')">
    <style>
        /* Fixed-dark hero: white text on a deep gradient in BOTH themes.
           Deliberately literal hex — tokens that lighten in dark mode would
           put light text on a light fill here. */
        .welcome-banner {
            background: linear-gradient(135deg, #064e3b 0%, #14532d 50%, #166534 100%);
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
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        @media (max-width: 640px) { .cards-grid { grid-template-columns: 1fr; } }
        .schedule-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--surface-2);
            font-size: 13.5px;
            color: var(--text-strong);
        }
        .schedule-item:last-child { border-bottom: none; }
        .period-badge {
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
        .classroom-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            background: var(--success-tint);
            color: var(--success-text);
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
        }
    </style>

    <div class="welcome-banner">
        <div style="position: relative; z-index: 1;">
            <div style="font-size: 13px; color: var(--success-border); font-weight: 500; margin-bottom: 4px;">
                {{ now()->format('l, F j, Y') }}
            </div>
            <h2 class="rtl-display" style="font-family: var(--font-display); font-size: 24px; font-weight: 700; margin-bottom: 6px;">
                {{ __("Hello, :name!", ['name' => explode(' ', $user->name)[0]]) }}
            </h2>
            <p style="font-size: 14px; color: var(--success-border);">{{ __("Keep up the great work. Your learning journey continues today.") }}</p>
            @if($classroom)
                <div style="margin-top: 10px;">
                    <span class="classroom-badge">{{ $classroom->name }} &middot; {{ $classroom->grade->name }}</span>
                </div>
            @endif
        </div>
    </div>

    <div class="cards-grid">
        {{-- My Grades --}}
        <x-dashboard.card :title="__('My Grades')" :subtitle="__('Latest academic results')" iconBg="var(--warning-tint)">
            <x-slot:icon>
                <svg width="20" height="20" fill="none" stroke="var(--warning)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </x-slot:icon>
            <div class="dash-card-empty">
                <svg width="32" height="32" fill="none" stroke="var(--border)" viewBox="0 0 24 24" style="margin: 0 auto 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                {{ __("Grades module coming soon") }}
            </div>
        </x-dashboard.card>

        {{-- Schedule --}}
        <x-dashboard.card :title="__('Today\'s Schedule')" :subtitle="now()->format('l')" iconBg="var(--primary-tint)">
            <x-slot:icon>
                <svg width="20" height="20" fill="none" stroke="var(--primary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </x-slot:icon>
            @forelse($todaySlots as $slot)
                <div class="schedule-item">
                    <div class="period-badge">P{{ $slot->period_number }}</div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600;">{{ $slot->subject->name }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $slot->teacher->name }} &middot; {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</div>
                    </div>
                </div>
            @empty
                <div class="dash-card-empty">
                    <svg width="32" height="32" fill="none" stroke="var(--border)" viewBox="0 0 24 24" style="margin: 0 auto 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ __("No classes scheduled for today") }}
                </div>
            @endforelse
        </x-dashboard.card>

        {{-- Announcements --}}
        <x-dashboard.card :title="__('Announcements')" :subtitle="__('School notices')" iconBg="var(--violet-tint)">
            <x-slot:icon>
                <svg width="20" height="20" fill="none" stroke="var(--violet)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            </x-slot:icon>
            <div class="dash-card-empty">
                <svg width="32" height="32" fill="none" stroke="var(--border)" viewBox="0 0 24 24" style="margin: 0 auto 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                {{ __("No announcements yet") }}
            </div>
        </x-dashboard.card>

        {{-- Attendance --}}
        <x-dashboard.card :title="__('Attendance')" :subtitle="__('This month\'s record')" iconBg="var(--success-tint)">
            <x-slot:icon>
                <svg width="20" height="20" fill="none" stroke="var(--success)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
            <div class="dash-card-empty">
                <svg width="32" height="32" fill="none" stroke="var(--border)" viewBox="0 0 24 24" style="margin: 0 auto 8px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ __("Attendance module coming soon") }}
            </div>
        </x-dashboard.card>
    </div>
</x-layouts.app>
