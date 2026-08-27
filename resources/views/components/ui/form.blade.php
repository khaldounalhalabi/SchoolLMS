{{--
    Shared form shell. Renders the <form>, the CSRF token, the optional
    validation summary and the card the fields sit in.

    All form styling lives in @layer components in resources/css/app.css,
    driven by the --field-* tokens in :root. Pages describe fields; they
    must not re-declare control styling in a page-local <style> block —
    unlayered page CSS beats @layer and would silently break consistency.

        <x-ui.form :action="route('admin.subjects.store')">
            <x-ui.form.field name="name" :label="__('Name')" />

            <x-slot:actions>
                <x-ui.form.actions :submit="__('Create')" :cancel="route('admin.subjects.index')" />
            </x-slot:actions>
        </x-ui.form>

    method  — the HTTP verb to use. PUT/PATCH/DELETE are spoofed via
              @method, so the <form> stays POST as Laravel expects.
    card    — set false when the form is already inside a card.
    summary — set false to suppress the error summary on forms that
              show messages only beside each field.
--}}
@props([
    'action',
    'method' => 'POST',
    'card' => true,
    'summary' => true,
    'maxWidth' => '600px',
])

@php
    $verb = strtoupper($method);
    $spoofed = in_array($verb, ['PUT', 'PATCH', 'DELETE'], true);

    // See the note in field.blade.php — $errors is request-scoped.
    $bag = $errors ?? new Illuminate\Support\ViewErrorBag;
@endphp

<form method="{{ $spoofed ? 'POST' : $verb }}" action="{{ $action }}" {{ $attributes }}>
    @csrf

    @if($spoofed)
        @method($verb)
    @endif

    @if($summary && $bag->any())
        <div class="form-error-summary" style="max-width: {{ $maxWidth }};" role="alert">
            <div class="form-error-summary-title">{{ __('Please fix the following errors:') }}</div>
            <ul>
                @foreach($bag->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($card)
        <div class="form-card" style="max-width: {{ $maxWidth }};">
            {{ $slot }}

            @isset($actions)
                {{ $actions }}
            @endisset
        </div>
    @else
        {{ $slot }}

        @isset($actions)
            {{ $actions }}
        @endisset
    @endif
</form>
