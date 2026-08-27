<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreGradeWebRequest;
use App\Models\ExamType;
use App\Models\Semester;
use App\Models\StudentGrade;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use App\Services\Grade\GradeEntryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherGradeController extends Controller
{
    public function __construct(private GradeEntryService $entries) {}

    public function entry(Request $request): View
    {
        $teacher = Auth::user();

        $assignments = TeacherSubjectClassroom::where('teacher_user_id', $teacher->id)
            ->with(['subject', 'classroom'])
            ->get();

        $semesters = Semester::with('academicYear')->orderByDesc('id')->get();
        $selectedSemesterId = $request->integer('semester_id') ?: $semesters->first()?->id;
        $selectedSubjectId = $request->integer('subject_id');
        $selectedClassroomId = $request->integer('classroom_id');
        $selectedExamTypeId = $request->integer('exam_type_id');

        $examTypes = $selectedSemesterId
            ? ExamType::where('semester_id', $selectedSemesterId)->get()
            : collect();

        $students = collect();
        $existingGrades = collect();

        if ($selectedSubjectId && $selectedClassroomId) {
            $students = User::whereHas('studentProfile', fn ($q) => $q->where('classroom_id', $selectedClassroomId))
                ->with('studentProfile')
                ->orderBy('name')
                ->get();

            if ($selectedExamTypeId) {
                $existingGrades = StudentGrade::where('subject_id', $selectedSubjectId)
                    ->where('exam_type_id', $selectedExamTypeId)
                    ->whereIn('student_user_id', $students->pluck('id'))
                    ->get()
                    ->keyBy('student_user_id');
            }
        }

        return view('teacher.grades.entry', compact(
            'assignments', 'semesters', 'examTypes', 'students', 'existingGrades',
            'selectedSemesterId', 'selectedSubjectId', 'selectedClassroomId', 'selectedExamTypeId'
        ));
    }

    public function store(StoreGradeWebRequest $request): RedirectResponse
    {
        $this->entries->storeWeb(
            Auth::user(),
            $request->integer('subject_id'),
            $request->integer('exam_type_id'),
            $request->float('max_score'),
            $request->input('scores'),
        );

        return back()->with('success', __('Grades saved successfully.'));
    }
}
