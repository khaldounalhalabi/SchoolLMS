<x-layouts.app :pageTitle="__('Create Assignment')">
    {{-- The reference implementation for the shared form system. All field,
         label, error and action styling comes from <x-ui.form.*> and the
         --field-* tokens in app.css — this page owns no control styling. --}}
    <style>
        .page-header { margin-bottom: 28px; }
        .page-header h2 {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        .page-header p { font-size: 13px; color: var(--text-secondary); }
    </style>

    <div class="page-header">
        <h2>{{ __("Create Teacher Assignment") }}</h2>
        <p>{{ __("Assign a teacher to a subject in a specific classroom and academic year") }}</p>
    </div>

    <x-ui.form :action="route('admin.assignments.store')">
        <x-ui.form.field name="teacher_user_id" :label="__('Teacher')">
            <x-ui.form.select name="teacher_user_id" :placeholder="__('-- Select Teacher --')" required>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}"
                            data-subject-ids="{{ $teacher->teacherAssignments->pluck('subject_id')->implode(',') }}"
                            @selected(old('teacher_user_id') == $teacher->id)>{{ $teacher->name }}</option>
                @endforeach
            </x-ui.form.select>
        </x-ui.form.field>

        <x-ui.form.field name="subject_id" :label="__('Subject')">
            <x-ui.form.select name="subject_id" :placeholder="__('-- Select Subject --')" required>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }} ({{ $subject->code }})</option>
                @endforeach
            </x-ui.form.select>
        </x-ui.form.field>

        <x-ui.form.field name="classroom_id" :label="__('Classroom')">
            <x-ui.form.select name="classroom_id" :placeholder="__('-- Select Classroom --')" required>
                @foreach($classrooms as $classroom)
                    <option value="{{ $classroom->id }}" data-academic-year-id="{{ $classroom->academic_year_id }}" @selected(old('classroom_id') == $classroom->id)>{{ $classroom->grade->name }} — {{ $classroom->name }} ({{ $classroom->academicYear?->name ?? __('No academic year') }})</option>
                @endforeach
            </x-ui.form.select>
        </x-ui.form.field>

        <x-ui.form.field
            name="academic_year_id"
            :label="__('Academic Year')"
            :hint="__('Only one assignment per teacher-subject-classroom-year combination is allowed.')"
        >
            <x-ui.form.select name="academic_year_id" :placeholder="__('-- Select Academic Year --')" required>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" @selected(old('academic_year_id') == $year->id)>{{ $year->name }}</option>
                @endforeach
            </x-ui.form.select>
        </x-ui.form.field>

        <x-slot:actions>
            <x-ui.form.actions
                :submit="__('Create Assignment')"
                :cancel="route('admin.assignments.index')"
            />
        </x-slot:actions>
    </x-ui.form>

</x-layouts.app>
