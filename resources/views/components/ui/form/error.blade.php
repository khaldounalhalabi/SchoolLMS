{{-- Validation message for a single field. --}}
@props(['id' => null])

<div {{ $attributes->merge(['class' => 'form-error']) }} @if($id) id="{{ $id }}" @endif>
    {{ $slot }}
</div>
