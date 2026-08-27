{{--
    One form field: label, control, hint and error message, with the
    spacing between them owned by the --field-* tokens.

    Two ways to use it. Pass a `type` and it renders the control for you:

        <x-ui.form.field name="name" :label="__('Name')" required />
        <x-ui.form.field name="bio" type="textarea" :label="__('Bio')" />

    Or supply the control yourself for selects and anything custom — the
    field still owns the label, hint and error:

        <x-ui.form.field name="school_id" :label="__('School')">
            <x-ui.form.select name="school_id" :options="$schools" />
        </x-ui.form.field>

    The id defaults to the field name so <label for> always resolves, and
    aria-describedby / aria-invalid are wired from the hint and the error
    bag without the page having to think about it.
--}}
@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'id' => null,
    'required' => false,
    'error' => null,
    // Defaults to the field id; pass "none" when the slot has no single
    // labellable control (radio group, read-only value).
    'labelFor' => null,
])

@php
    $fieldId = $id ?? $name;

    // Laravel puts errors for name="marks[3]" under the dotted key marks.3
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
    // $errors is shared by ShareErrorsFromSession on every web request; the
    // fallback keeps the component usable outside one (mail, PDF, tests).
    $bag = $errors ?? new Illuminate\Support\ViewErrorBag;
    $message = $error ?? $bag->first($errorKey);

    $hintId = $hint ? $fieldId . '-hint' : null;
    $errorId = $message ? $fieldId . '-error' : null;
    $describedBy = implode(' ', array_filter([$hintId, $errorId])) ?: null;

    // Attributes aimed at the control (placeholder, autocomplete, min, step,
    // disabled …) are forwarded to it rather than landing on the wrapper.
    // Everything else — class, style, data-* — stays on the wrapper div.
    $controlKeys = ['placeholder', 'autocomplete', 'autofocus', 'disabled', 'readonly',
                    'min', 'max', 'step', 'minlength', 'maxlength', 'pattern', 'rows', 'inputmode', 'accept'];

    $controlAttributes = $attributes->only($controlKeys);
    $wrapperAttributes = $attributes->except($controlKeys);

    $hasSlot = isset($slot) && trim($slot) !== '';

    // <label for> points at the field id, which the slot control also
    // defaults to (x-ui.form.select/input fall back to id ?? name). Fields
    // whose slot has no single labellable control — a radio group, a
    // read-only value — pass :labelFor="null" so no dangling `for` is
    // emitted; those label their own inputs individually.
    $labelFor = $labelFor === 'none' ? null : ($labelFor ?? $fieldId);
@endphp

<div {{ $wrapperAttributes->merge(['class' => 'form-field']) }}>
    @if($label)
        <x-ui.form.label :for="$labelFor" :required="$required">{{ $label }}</x-ui.form.label>
    @endif

    @if($hasSlot)
        {{ $slot }}
    @elseif($type === 'textarea')
        <x-ui.form.textarea
            :name="$name"
            :id="$fieldId"
            :value="$value"
            :required="$required"
            :invalid="(bool) $message"
            :aria-describedby="$describedBy"
            :attributes="$controlAttributes"
        />
    @else
        <x-ui.form.input
            :name="$name"
            :id="$fieldId"
            :type="$type"
            :value="$value"
            :required="$required"
            :invalid="(bool) $message"
            :aria-describedby="$describedBy"
            :attributes="$controlAttributes"
        />
    @endif

    @if($hint)
        <div class="form-hint" id="{{ $hintId }}">{{ $hint }}</div>
    @endif

    @if($message)
        <x-ui.form.error :id="$errorId">{{ $message }}</x-ui.form.error>
    @endif
</div>
