{{--
    Text-family input: text, number, email, password, date, search, tel …
    All appearance comes from .form-control; every other attribute
    (placeholder, min, max, step, autocomplete, required, disabled)
    passes straight through untouched.
--}}
@props([
    'name' => null,
    'type' => 'text',
    'value' => null,
    'id' => null,
    'invalid' => false,
])

<input
    type="{{ $type }}"
    @if($name) name="{{ $name }}" @endif
    id="{{ $id ?? $name }}"
    value="{{ $value ?? old($name) }}"
    @if($invalid) aria-invalid="true" @endif
    {{ $attributes->merge(['class' => 'form-control' . ($invalid ? ' is-error' : '')]) }}
>
