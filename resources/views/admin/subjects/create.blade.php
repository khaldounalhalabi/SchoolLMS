<x-layouts.app :pageTitle="__('Create Subject')">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.subjects.index') }}" style="font-size: 13px; color: var(--text-secondary); text-decoration: none;">
            &larr; {{ __("Back to Subjects") }}
        </a>
    </div>

    <x-ui.form :action="route('admin.subjects.store')" max-width="560px">
        <x-ui.form.field name="school_id" :label="__('School')" required>
            <x-ui.form.select
                name="school_id"
                :options="$schools"
                :placeholder="__('Select school…')"
                required
            />
        </x-ui.form.field>

        <x-ui.form.field
            name="name"
            :label="__('Subject Name')"
            :placeholder="__('e.g. Mathematics')"
            required
        />

        <x-ui.form.field
            name="code"
            :label="__('Subject Code')"
            :placeholder="__('e.g. MATH101')"
            :hint="__('Must be unique across all subjects.')"
            required
        />

        <x-slot:actions>
            <x-ui.form.actions
                :submit="__('Create Subject')"
                :cancel="route('admin.subjects.index')"
                icon="plus"
            />
        </x-slot:actions>
    </x-ui.form>
</x-layouts.app>
