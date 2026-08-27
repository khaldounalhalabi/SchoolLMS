@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div>
        <div class="page-title">{{ $title }}</div>
        @if($description)
            <div class="page-desc">{{ $description }}</div>
        @endif
    </div>

    @isset($actions)
        <div class="page-actions">
            {{ $actions }}
        </div>
    @endisset
</div>
