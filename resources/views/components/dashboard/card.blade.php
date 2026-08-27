@props(['title', 'subtitle' => null, 'iconBg' => 'var(--border-soft)'])

<div class="dash-card">
    <div class="dash-card-hdr">
        <div class="dash-card-ico" style="background: {{ $iconBg }};">
            {{ $icon }}
        </div>
        <div>
            <div class="dash-card-ttl">{{ $title }}</div>
            @if($subtitle)
                <div class="dash-card-sub">{{ $subtitle }}</div>
            @endif
        </div>
    </div>
    {{ $slot }}
</div>
