@props(['type' => 'success'])

<div {{ $attributes->merge(['class' => "flash-{$type}"]) }} role="alert">
    {{ $slot }}
</div>
