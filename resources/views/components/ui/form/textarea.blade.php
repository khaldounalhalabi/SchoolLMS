{{--
    Textarea. Shares .form-control with the inputs, adding a min-height
    (--field-min-height) and vertical-only resize.
--}}
@props([
    'name' => null,
    'value' => null,
    'id' => null,
    'rows' => null,
    'invalid' => false,
])

<textarea
    @if($name) name="{{ $name }}" @endif
    id="{{ $id ?? $name }}"
    @if($rows) rows="{{ $rows }}" @endif
    @if($invalid) aria-invalid="true" @endif
    {{ $attributes->merge(['class' => 'form-control' . ($invalid ? ' is-error' : '')]) }}
>{{ $value ?? old($name) ?? trim($slot) }}</textarea>
