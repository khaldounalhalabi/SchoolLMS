@props(['user'])

@php
$roleBadge = [
    'admin'   => ['label' => __('Admin'),   'bg' => 'var(--primary-tint)', 'color' => 'var(--primary-dark)', 'dot' => 'var(--primary)'],
    'teacher' => ['label' => __('Teacher'), 'bg' => 'var(--info-tint)', 'color' => 'var(--info-strong)', 'dot' => 'var(--info)'],
    'student' => ['label' => __('Student'), 'bg' => 'var(--success-tint)', 'color' => 'var(--success-text)', 'dot' => 'var(--success)'],
    'parent'  => ['label' => __('Parent'),  'bg' => 'var(--violet-tint)', 'color' => 'var(--violet-strong)', 'dot' => 'var(--violet)'],
];
$rb = $roleBadge[$user->role->value] ?? $roleBadge['student'];
$initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0]))->take(2)->join('');

$avatarColors = [
    'admin'   => ['from' => 'var(--primary-light)', 'to' => 'var(--primary)'],
    'teacher' => ['from' => '#60a5fa', 'to' => 'var(--info-dark)'],
    'student' => ['from' => 'var(--success)', 'to' => 'var(--success-dark)'],
    'parent'  => ['from' => '#c084fc', 'to' => 'var(--violet-dark)'],
];
$ac = $avatarColors[$user->role->value] ?? $avatarColors['student'];
@endphp

<tr style="border-bottom: 1px solid var(--border-soft); transition: background 0.15s; cursor: pointer;"
    data-row-href="{{ route('admin.users.show', $user) }}">

    {{-- User Info --}}
    <td style="padding: 14px 20px;">
        <div style="display: flex; align-items: center; gap: 11px;">
            <div style="
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: linear-gradient(135deg, {{ $ac['from'] }}, {{ $ac['to'] }});
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                font-weight: 700;
                color: white;
                flex-shrink: 0;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            ">{{ $initials }}</div>
            <div>
                <div style="font-size: 14px; font-weight: 600; color: var(--text-primary);">{{ $user->name }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 1px;">{{ $user->email }}</div>
            </div>
        </div>
    </td>

    {{-- Role --}}
    <td style="padding: 14px 16px;">
        <span style="
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            background: {{ $rb['bg'] }};
            color: {{ $rb['color'] }};
        ">
            <span style="width: 5px; height: 5px; border-radius: 50%; background: {{ $rb['dot'] }}; display: inline-block;"></span>
            {{ $rb['label'] }}
        </span>
    </td>

    {{-- Phone --}}
    <td style="padding: 14px 16px; font-size: 13px; color: var(--text-secondary);">
        {{ $user->phone ?? '—' }}
    </td>

    {{-- Status --}}
    <td style="padding: 14px 16px;">
        @if($user->is_active)
            <span style="
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 11.5px;
                font-weight: 600;
                background: var(--success-tint);
                color: var(--success-text);
            ">
                <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--success); display: inline-block;"></span>
                {{ __('Active') }}
            </span>
        @else
            <span style="
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 11.5px;
                font-weight: 600;
                background: var(--surface-2);
                color: var(--text-muted);
            ">
                <span style="width: 5px; height: 5px; border-radius: 50%; background: var(--text-faint); display: inline-block;"></span>
                {{ __('Inactive') }}
            </span>
        @endif
    </td>

    {{-- Joined --}}
    <td style="padding: 14px 16px; font-size: 12px; color: var(--text-muted);">
        {{ $user->created_at->format('M d, Y') }}
    </td>

    {{-- Actions --}}
    <td style="padding: 14px 20px; text-align: end;">
        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" style="display: inline;">
            @csrf
            @method('PATCH')
            <button type="submit" style="
                padding: 6px 14px;
                border-radius: 8px;
                font-size: 12px;
                font-weight: 600;
                font-family: var(--font-body);
                cursor: pointer;
                transition: all 0.2s;
                border: 1px solid;
                {{ $user->is_active
                    ? 'background: var(--danger-tint); border-color: var(--danger-border); color: var(--danger-dark);'
                    : 'background: var(--success-tint); border-color: var(--success-border); color: var(--success-dark);' }}
            ">
                {{ $user->is_active ? __('Deactivate') : __('Activate') }}
            </button>
        </form>
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline; margin-inline-start: 6px;" data-confirm="{{ __('Delete :name? This action cannot be undone.', ['name' => $user->name]) }}">
            @csrf
            @method('DELETE')
            <button type="submit" style="
                padding: 6px 14px;
                border-radius: 8px;
                font-size: 12px;
                font-weight: 600;
                font-family: var(--font-body);
                cursor: pointer;
                transition: all 0.2s;
                border: 1px solid var(--danger-border);
                background: var(--danger-tint);
                color: var(--danger-dark);
            ">
                {{ __('Delete') }}
            </button>
        </form>
    </td>
</tr>
