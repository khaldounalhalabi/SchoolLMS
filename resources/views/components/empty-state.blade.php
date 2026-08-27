@props([
    'title' => null,
    'description' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    @if($icon)
        <div class="empty-icon">{{ $icon }}</div>
    @endif

    @if($title)
        <div class="empty-title">{{ $title }}</div>
    @endif

    @if($description)
        <div class="empty-desc">{{ $description }}</div>
    @endif

    {{ $slot }}
</div>
