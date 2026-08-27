<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\EnrollStudentsWebRequest;
use App\Http\Requests\Web\StoreClassroomSubjectWebRequest;
use App\Http\Requests\Web\StoreClassroomWebRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClassroomWebController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $requestedYearId = request()->integer('academic_year_id');
        $yearId = $requestedYearId
            ?: ($user->role->value === 'admin'
                ? AcademicYear::where('is_active', true)->value('id')
                : null);

        $query = Classroom::with([
            'grade',
            'academicYear',
            'studentEnrollments' => fn ($query) => $query->active()->with('student'),
            'teacherAssignments.subject',
        ])->withCount([
            'studentEnrollments as active_students_count' => fn ($query) => $query->active(),
        ]);

        if ($user->role->value === 'teacher') {
            $query->whereIn(
                'id',
                $user->teacherAssignments()
                    ->when($yearId, fn ($assignmentQuery) => $assignmentQuery->where('academic_year_id', $yearId))
                    ->pluck('classroom_id')
                    ->unique(),
            );
        }

        $classrooms = $query
            ->when($yearId, fn ($classroomQuery) => $classroomQuery->where('academic_year_id', $yearId))
            ->orderBy('grade_id')
            ->orderBy('name')
            ->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.classrooms.index', compact('classrooms', 'academicYears', 'yearId'));
    }

    public function create(): View
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $grades = Grade::orderBy('order_index')->orderBy('name')->get();
        $selectedYearId = request()->integer('academic_year_id')
            ?: AcademicYear::where('is_active', true)->value('id');

        return view('admin.classrooms.create', compact('academicYears', 'grades', 'selectedYearId'));
    }

    public function store(StoreClassroomWebRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $year = AcademicYear::findOrFail($data['academic_year_id']);
        $grade = Grade::findOrFail($data['grade_id']);

        if ($year->school_id !== $grade->school_id) {
            throw ValidationException::withMessages([
                'grade_id' => __('The selected grade does not belong to the selected academic year school.'),
            ]);
        }

        $exists = Classroom::where('academic_year_id', $year->id)
            ->where('grade_id', $grade->id)
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => __('This classroom already exists for the selected grade and academic year.'),
            ]);
        }

        Classroom::create($data);

        return redirect()
            ->route('admin.academic-years.show', $year)
            ->with('success', __('Classroom created successfully.'));
    }

    public function show(Classroom $classroom): View
    {
        $classroom->load([
            'grade',
            'academicYear',
            'studentEnrollments' => fn ($query) => $query->active()->with('student'),
            'teacherAssignments.subject',
            'teacherAssignments.teacher',
            'teacherAssignments.academicYear',
        ]);

        $availableStudents = collect();
        $availableSubjects = collect();
        $availableTeachers = collect();

        if (auth()->user()->role->value === 'admin' && $classroom->academic_year_id) {
            $availableStudents = User::where('role', 'student')
                ->whereDoesntHave('enrollments', function ($query) use ($classroom) {
                    $query->where('academic_year_id', $classroom->academic_year_id)
                        ->active();
                })
                ->orderBy('name')
                ->get();

            $availableSubjects = Subject::where('school_id', $classroom->grade->school_id)
                ->whereDoesntHave('teacherAssignments', function ($query) use ($classroom) {
                    $query->where('classroom_id', $classroom->id)
                        ->where('academic_year_id', $classroom->academic_year_id);
                })
                ->orderBy('name')
                ->get();

            $availableTeachers = User::where('role', 'teacher')
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return view('admin.classrooms.show', compact(
            'classroom',
            'availableStudents',
            'availableSubjects',
            'availableTeachers',
        ));
    }

    public function storeSubject(StoreClassroomSubjectWebRequest $request, Classroom $classroom): RedirectResponse
    {
        abort_unless($classroom->academic_year_id, 422, __('This classroom is not linked to an academic year.'));

        $data = $request->validated();
        $classroom->loadMissing('grade');
        $subject = Subject::findOrFail($data['subject_id']);

        if ($subject->school_id !== $classroom->grade->school_id) {
            throw ValidationException::withMessages([
                'subject_id' => __('The selected subject does not belong to this classroom school.'),
            ]);
        }

        $alreadyAssigned = TeacherSubjectClassroom::where('classroom_id', $classroom->id)
            ->where('academic_year_id', $classroom->academic_year_id)
            ->where('subject_id', $subject->id)
            ->exists();

        if ($alreadyAssigned) {
            throw ValidationException::withMessages([
                'subject_id' => __('This subject is already assigned to this classroom.'),
            ]);
        }

        $teacherHasDifferentSubject = TeacherSubjectClassroom::where('teacher_user_id', $data['teacher_user_id'])
            ->where('subject_id', '!=', $subject->id)
            ->exists();

        if ($teacherHasDifferentSubject) {
            throw ValidationException::withMessages([
                'teacher_user_id' => __('This teacher is already assigned to a different subject.'),
            ]);
        }

        TeacherSubjectClassroom::create([
            'teacher_user_id' => $data['teacher_user_id'],
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $classroom->academic_year_id,
        ]);

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', __('Subject assigned to classroom successfully.'));
    }

    public function enroll(EnrollStudentsWebRequest $request, Classroom $classroom): RedirectResponse
    {
        abort_unless($classroom->academic_year_id, 422, __('This classroom is not linked to an academic year.'));

        $studentIds = $request->validated('student_user_ids');
        $activeCount = $classroom->studentEnrollments()->active()->count();
        $availableCapacity = $classroom->capacity - $activeCount;

        if (count($studentIds) > $availableCapacity) {
            throw ValidationException::withMessages([
                'student_user_ids' => __('The selected students exceed the remaining classroom capacity.'),
            ]);
        }

        $students = User::where('role', 'student')->whereIn('id', $studentIds)->get();
        if ($students->count() !== count($studentIds)) {
            throw ValidationException::withMessages([
                'student_user_ids' => __('Only student accounts can be enrolled in a classroom.'),
            ]);
        }

        $alreadyEnrolled = StudentEnrollment::active()
            ->where('academic_year_id', $classroom->academic_year_id)
            ->whereIn('student_user_id', $studentIds)
            ->pluck('student_user_id');

        if ($alreadyEnrolled->isNotEmpty()) {
            throw ValidationException::withMessages([
                'student_user_ids' => __('One or more selected students are already enrolled in this academic year.'),
            ]);
        }

        DB::transaction(function () use ($students, $classroom): void {
            foreach ($students as $student) {
                StudentEnrollment::create([
                    'student_user_id' => $student->id,
                    'academic_year_id' => $classroom->academic_year_id,
                    'classroom_id' => $classroom->id,
                    'enrollment_date' => now()->toDateString(),
                    'status' => 'active',
                ]);

                if ($classroom->academicYear?->is_active) {
                    StudentProfile::updateOrCreate(
                        ['user_id' => $student->id],
                        [
                            'classroom_id' => $classroom->id,
                            'enrollment_date' => now()->toDateString(),
                        ],
                    );
                }
            }
        });

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', __('Students enrolled successfully.'));
    }
}
