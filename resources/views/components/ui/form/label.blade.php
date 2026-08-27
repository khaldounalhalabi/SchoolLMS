{{-- Field label. Typography and the gap below it come from --field-label-*. --}}
@props([
    'for' => null,
    'required' => false,
])

<label {{ $attributes->merge(['class' => 'form-label']) }} @if($for) for="{{ $for }}" @endif>
    {{ $slot }}@if($required)<span class="req" aria-hidden="true">*</span>@endif
</label>
