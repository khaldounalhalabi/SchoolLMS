@props(['label', 'value', 'icon', 'color' => 'indigo', 'trend' => null])

@php
$colorMap = [
    'indigo' => ['bg' => 'var(--accent-tint)', 'icon' => 'var(--accent)', 'text' => 'var(--accent-dark)'],
    'blue'   => ['bg' => 'var(--info-tint)', 'icon' => 'var(--info)', 'text' => 'var(--info-dark)'],
    'green'  => ['bg' => 'var(--success-tint)', 'icon' => 'var(--success)', 'text' => 'var(--success-dark)'],
    'purple' => ['bg' => 'var(--violet-tint)', 'icon' => 'var(--violet)', 'text' => 'var(--violet-dark)'],
    'amber'  => ['bg' => 'var(--primary-tint)', 'icon' => 'var(--primary)', 'text' => 'var(--primary-dark)'],
    'rose'   => ['bg' => 'var(--danger-tint)', 'icon' => 'var(--rose)', 'text' => 'var(--rose-dark)'],
];
$c = $colorMap[$color] ?? $colorMap['indigo'];

$iconPaths = [
    'users'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
    'user-admin'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
    'user-teacher' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
    'user-student' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0112 21a12.083 12.083 0 01-6.16-10.422L12 14z"/>',
    'user-parent'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
    'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'trending-up'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>',
];
$path = $iconPaths[$icon] ?? $iconPaths['users'];
@endphp

<div style="
    background: var(--surface);
    border-radius: var(--radius-card);
    padding: 22px;
    border: 1px solid var(--border-soft);
    box-shadow: var(--shadow-card), 0 4px 16px rgba(0,0,0,0.03);
    display: flex;
    align-items: flex-start;
    gap: 16px;
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
    overflow: hidden;
">

    <div style="
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: {{ $c['bg'] }};
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    ">
        <svg width="22" height="22" fill="none" stroke="{{ $c['icon'] }}" viewBox="0 0 24 24">
            {!! $path !!}
        </svg>
    </div>

    <div style="flex: 1; min-width: 0;">
        <div style="
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.1;
            font-family: var(--font-body);
            letter-spacing: -0.5px;
        ">{{ number_format($value) }}</div>
        <div style="
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 3px;
            font-weight: 500;
        ">{{ $label }}</div>

        @if($trend !== null)
            <div style="
                display: inline-flex;
                align-items: center;
                gap: 3px;
                margin-top: 6px;
                font-size: 11.5px;
                font-weight: 600;
                color: {{ $trend >= 0 ? 'var(--success-dark)' : 'var(--danger-dark)' }};
                background: {{ $trend >= 0 ? 'var(--success-tint)' : 'var(--danger-tint)' }};
                padding: 2px 7px;
                border-radius: 20px;
            ">
                {{ $trend >= 0 ? '↑' : '↓' }} {{ abs($trend) }}%
            </div>
        @endif
    </div>
</div>
