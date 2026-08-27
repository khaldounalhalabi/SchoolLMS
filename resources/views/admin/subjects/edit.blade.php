<x-layouts.app :pageTitle="__('Edit Subject')">
    <style>
        /* Read-only display of an immutable value. Deliberately not a
           .form-control: it is not an input, so it must not look focusable. */
        .school-label {
            display: inline-block;
            padding: 8px var(--field-pad-x);
            background: var(--surface-2);
            border: var(--field-border-width) solid var(--border);
            border-radius: var(--field-radius);
            font-size: var(--field-font-size);
            color: var(--text-secondary);
        }
    </style>

    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.subjects.index') }}" style="font-size: 13px; color: var(--text-secondary); text-decoration: none;">
            &larr; {{ __("Back to Subjects") }}
        </a>
    </div>

    <x-ui.form :action="route('admin.subjects.update', $subject)" method="PUT" max-width="560px">
        <x-ui.form.field name="school" :label="__('School')" label-for="none" :hint="__('School cannot be changed after creation.')">
            <div class="school-label">{{ $subject->school?->name ?? '—' }}</div>
        </x-ui.form.field>

        <x-ui.form.field
            name="name"
            :label="__('Subject Name')"
            :value="old('name', $subject->name)"
            required
        />

        <x-ui.form.field
            name="code"
            :label="__('Subject Code')"
            :value="old('code', $subject->code)"
            :hint="__('Must be unique across all subjects.')"
            required
        />

        <x-slot:actions>
            <x-ui.form.actions
                :submit="__('Save Changes')"
                :cancel="route('admin.subjects.index')"
            />
        </x-slot:actions>
    </x-ui.form>
</x-layouts.app>
