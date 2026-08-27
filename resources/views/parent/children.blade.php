<x-layouts.app :pageTitle="__('My Children')">
    <style>
        .page-header { margin-bottom: 24px; }
        .page-header h2 { font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .page-header p { font-size: 13px; color: var(--text-secondary); }
        .children-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(340px, 100%), 1fr)); gap: 16px; }
        .child-card {
            background: var(--surface); border-radius: 14px; padding: 24px;
            border: 1px solid var(--border-soft); box-shadow: var(--shadow-card);
            transition: all 0.2s;
        }
        .child-card:hover { border-color: var(--primary-light); box-shadow: 0 4px 14px color-mix(in srgb, var(--primary) 8%, transparent); }
        .child-header { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
        .child-avatar {
            width: 48px; height: 48px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 700; color: white; flex-shrink: 0;
        }
        .child-name { font-size: 16px; font-weight: 700; color: var(--text-primary); }
        .child-class { font-size: 13px; color: var(--text-secondary); margin-top: 2px; }
        .child-details { display: flex; flex-direction: column; gap: 8px; margin-bottom: 16px; }
        .detail-row {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: var(--text-secondary);
        }
        .detail-row svg { width: 16px; height: 16px; color: var(--text-muted); flex-shrink: 0; }
        .detail-label { color: var(--text-muted); min-width: 80px; }
        .detail-value { color: var(--text-strong); font-weight: 500; }
        .child-actions { display: flex; gap: 8px; padding-top: 16px; border-top: 1px solid var(--border-soft); }
        .btn-sm {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 8px; font-size: 12.5px;
            font-weight: 600; text-decoration: none; transition: all 0.2s;
            font-family: var(--font-body);
        }
        .btn-schedule {
            background: var(--primary-tint); color: var(--primary-dark); border: 1px solid var(--primary-light);
        }
        .btn-schedule:hover { background: var(--primary-dark); color: var(--on-primary); }
        .btn-schedule svg { width: 14px; height: 14px; }
        .empty-state {
            text-align: center; padding: 80px 20px; color: var(--text-faint);
            background: var(--surface); border-radius: 14px; border: 1px solid var(--border-soft);
        }
        .empty-state svg { margin-bottom: 16px; }
        .empty-state h3 { font-family: var(--font-display); font-size: 18px; color: var(--text-muted); margin-bottom: 8px; }
        .empty-state p { font-size: 13px; color: var(--text-faint); }
    </style>

    <div class="page-header">
        <h2>{{ __("My Children") }}</h2>
        <p>{{ __("View your children's information and academic details") }}</p>
    </div>

    @if($children->count() > 0)
        <div class="children-grid">
            @foreach($children as $i => $child)
                @php
                    $initials = collect(explode(' ', $child->name))->map(fn($w) => strtoupper($w[0]))->take(2)->join('');
                    $gradients = [
                        'linear-gradient(135deg, #c084fc, var(--violet-dark))',
                        'linear-gradient(135deg, #f9a8d4, #ec4899)',
                        'linear-gradient(135deg, var(--info-border), var(--info))',
                        'linear-gradient(135deg, var(--success-border), var(--success))',
                    ];
                    $profile = $child->studentProfile;
                @endphp
                <div class="child-card">
                    <div class="child-header">
                        <div class="child-avatar" style="background: {{ $gradients[$i % count($gradients)] }};">{{ $initials }}</div>
                        <div>
                            <div class="child-name">{{ $child->name }}</div>
                            <div class="child-class">
                                @if($profile && $profile->classroom)
                                    {{ $profile->classroom->grade->name }} &mdash; {{ $profile->classroom->name }}
                                @else
                                    {{ __("No classroom assigned") }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="child-details">
                        <div class="detail-row">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span class="detail-label">{{ __("Email:") }}</span>
                            <span class="detail-value">{{ $child->email }}</span>
                        </div>
                        @if($child->phone)
                        <div class="detail-row">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="detail-label">{{ __("Phone:") }}</span>
                            <span class="detail-value">{{ $child->phone }}</span>
                        </div>
                        @endif
                        @if($profile)
                        <div class="detail-row">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="detail-label">{{ __("Enrolled:") }}</span>
                            <span class="detail-value">{{ $profile->enrollment_date?->format('M d, Y') ?? __('N/A') }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="child-actions">
                        <a href="{{ route('parent.child-schedule', $child) }}" class="btn-sm btn-schedule">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ __("View Schedule") }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <svg width="56" height="56" fill="none" stroke="var(--border)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <h3>{{ __("No Children Linked") }}</h3>
            <p>{{ __("Your account doesn't have any children linked yet. Please contact the school administration.") }}</p>
        </div>
    @endif
</x-layouts.app>
