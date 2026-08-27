<x-layouts.app :pageTitle="__('Create Academic Year')">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.academic-years.index') }}" style="font-size: 13px; color: var(--text-secondary); text-decoration: none;">
            &larr; {{ __("Back to Academic Years") }}
        </a>
    </div>

    @if(! $hasSchool)
        <div class="alert-warning" style="max-width: 560px; margin-bottom: 18px;">
            {{ __('You need to create a school before adding an academic year.') }}
            <a href="{{ route('admin.schools.index') }}" style="color: inherit; font-weight: 700;">{{ __('Create School') }}</a>
        </div>
    @endif

    <x-ui.form :action="route('admin.academic-years.store')" max-width="560px">
        <x-ui.form.field
            name="name"
            :label="__('Academic Year Name')"
            :placeholder="__('e.g. 2025-2026')"
            required
        />

        <div class="form-row">
            <x-ui.form.field name="start_date" type="date" :label="__('Start Date')" required />
            <x-ui.form.field name="end_date" type="date" :label="__('End Date')" required />
        </div>

        <x-ui.form.field name="is_active">
            <div class="form-check">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                <label for="is_active">{{ __("Set as active academic year") }}</label>
            </div>
        </x-ui.form.field>

        <x-slot:actions>
            <x-ui.form.actions
                :submit="__('Create Academic Year')"
                :cancel="route('admin.academic-years.index')"
                icon="plus"
            />
        </x-slot:actions>
    </x-ui.form>
</x-layouts.app>
