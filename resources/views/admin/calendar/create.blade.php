<x-layouts.app :pageTitle="__('Add Calendar Event')">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.calendar.index') }}" style="font-size: 13px; color: var(--text-secondary); text-decoration: none;">
            &larr; {{ __("Back to Calendar") }}
        </a>
    </div>

    <x-ui.form :action="route('admin.calendar.store')" max-width="560px">
        <x-ui.form.field name="date" type="date" :label="__('Date')" required />

        <x-ui.form.field name="type" :label="__('Type')" required>
            <x-ui.form.select name="type" :placeholder="__('Select type...')" required>
                <option value="holiday" {{ old('type') === 'holiday' ? 'selected' : '' }}>{{ __("Holiday") }}</option>
                <option value="event" {{ old('type') === 'event' ? 'selected' : '' }}>{{ __("Event") }}</option>
                <option value="exam" {{ old('type') === 'exam' ? 'selected' : '' }}>{{ __("Exam") }}</option>
            </x-ui.form.select>
        </x-ui.form.field>

        <x-ui.form.field
            name="description"
            type="textarea"
            :label="__('Description')"
            :placeholder="__('e.g. Spring Break, Midterm Exams')"
            required
        />

        <x-slot:actions>
            <x-ui.form.actions
                :submit="__('Add Event')"
                :cancel="route('admin.calendar.index')"
                icon="plus"
            />
        </x-slot:actions>
    </x-ui.form>
</x-layouts.app>
