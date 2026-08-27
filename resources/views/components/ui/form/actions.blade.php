{{--
    Submit / cancel row, separated from the fields by a rule.

        <x-ui.form.actions :submit="__('Create Subject')" :cancel="route('admin.subjects.index')" />

    Pass extra buttons through the default slot; they sit after the
    submit and cancel buttons in the same flex row.
--}}
@props([
    'submit' => null,
    'cancel' => null,
    'cancelLabel' => null,
    'icon' => 'check',
])

<div {{ $attributes->merge(['class' => 'form-actions']) }}>
    @if($submit)
        <button type="submit" class="btn-submit">
            @if($icon === 'check')
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            @elseif($icon === 'plus')
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            @endif
            {{ $submit }}
        </button>
    @endif

    @if($cancel)
        <a href="{{ $cancel }}" class="btn-cancel">{{ $cancelLabel ?? __('Cancel') }}</a>
    @endif

    {{ $slot }}
</div>
