<x-layouts.app :pageTitle="__('Create Classroom')">
    <div style="margin-bottom: 20px;">
        <a href="{{ request('academic_year_id') ? route('admin.academic-years.show', request('academic_year_id')) : route('classrooms.index') }}" style="font-size: 13px; color: var(--text-secondary); text-decoration: none;">
            &larr; {{ __('Back') }}
        </a>
    </div>

    <x-ui.form :action="route('admin.classrooms.store')" max-width="560px">
        <x-ui.form.field name="academic_year_id" :label="__('Academic Year')" required>
            <select name="academic_year_id" class="form-control" required>
                <option value="">{{ __('Select academic year') }}</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ (string) old('academic_year_id', $selectedYearId) === (string) $year->id ? 'selected' : '' }}>
                        {{ $year->name }}{{ $year->is_active ? ' — ' . __('Active') : '' }}
                    </option>
                @endforeach
            </select>
        </x-ui.form.field>

        <div class="form-field">
            <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin-bottom: var(--field-label-gap);">
                <x-ui.form.label for="grade_id" :required="true" style="margin-bottom: 0;">{{ __('Grade') }}</x-ui.form.label>
                <a href="{{ route('admin.grades.index') }}" style="font-size: 12px; color: var(--primary); text-decoration: none;">{{ __('Manage Grades') }}</a>
            </div>
            <select id="grade_id" name="grade_id" class="form-control" required>
                <option value="">{{ __('Select grade') }}</option>
                @foreach($grades as $grade)
                    <option value="{{ $grade->id }}" {{ (string) old('grade_id') === (string) $grade->id ? 'selected' : '' }}>
                        {{ $grade->name }}
                    </option>
                @endforeach
            </select>
            @error('grade_id')
                <x-ui.form.error>{{ $message }}</x-ui.form.error>
            @enderror
        </div>

        <div class="form-row">
            <x-ui.form.field name="name" :label="__('Section Name')" :placeholder="__('e.g. A or 7-A')" required />
            <x-ui.form.field name="capacity" type="number" :label="__('Capacity')" :value="old('capacity', 30)" min="1" max="500" required />
        </div>

        <x-slot:actions>
            <x-ui.form.actions
                :submit="__('Create Classroom')"
                :cancel="route('classrooms.index')"
                icon="plus"
            />
        </x-slot:actions>
    </x-ui.form>
</x-layouts.app>
