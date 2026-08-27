<x-layouts.app :pageTitle="__('Create Semester')">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.schedule.index') }}" style="font-size: 13px; color: var(--text-secondary); text-decoration: none;">&larr; {{ __('Back to Schedule') }}</a>
    </div>

    <x-ui.form :action="route('admin.semesters.store')" max-width="560px">
        <x-ui.form.field name="academic_year_id" :label="__('Academic Year')" required>
            <select name="academic_year_id" class="form-control" required>
                <option value="">{{ __('Select academic year') }}</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $selectedYearId) === (string) $year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
        </x-ui.form.field>

        <x-ui.form.field name="name" :label="__('Semester Name')" :placeholder="__('e.g. First Semester')" required />

        <div class="form-row">
            <x-ui.form.field name="start_date" type="date" :label="__('Start Date')" required />
            <x-ui.form.field name="end_date" type="date" :label="__('End Date')" required />
        </div>

        <x-ui.form.field name="is_active">
            <div class="form-check">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active'))>
                <label for="is_active">{{ __('Set as active semester') }}</label>
            </div>
        </x-ui.form.field>

        <x-slot:actions>
            <x-ui.form.actions
                :submit="__('Create Semester')"
                :cancel="route('admin.schedule.index')"
                icon="plus"
            />
        </x-slot:actions>
    </x-ui.form>
</x-layouts.app>
