{{--
    Select. Same .form-control geometry as the inputs, with the native
    arrow replaced by the shared chevron (see select.form-control in
    app.css, which also flips the arrow under [dir="rtl"]).

    Options come either from a slot, when they need per-option data
    attributes:

        <x-ui.form.select name="teacher_user_id" :placeholder="__('-- Select Teacher --')">
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" data-x="…" @selected(old('teacher_user_id') == $teacher->id)>…</option>
            @endforeach
        </x-ui.form.select>

    or from `options`, which accepts a value => label array or a
    collection of models addressed via `optionValue` / `optionLabel`.
--}}
@props([
    'name' => null,
    'id' => null,
    'options' => null,
    'selected' => null,
    'placeholder' => null,
    'optionValue' => 'id',
    'optionLabel' => 'name',
    'invalid' => false,
])

@php
    $current = $selected ?? old($name);
@endphp

<select
    @if($name) name="{{ $name }}" @endif
    id="{{ $id ?? $name }}"
    @if($invalid) aria-invalid="true" @endif
    {{ $attributes->merge(['class' => 'form-control' . ($invalid ? ' is-error' : '')]) }}
>
    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    @if($options)
        @foreach($options as $key => $option)
            @php
                $optValue = is_array($option) || is_object($option)
                    ? data_get($option, $optionValue)
                    : $key;
                $optLabel = is_array($option) || is_object($option)
                    ? data_get($option, $optionLabel)
                    : $option;
            @endphp
            <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
    @endif

    {{ $slot }}
</select>
