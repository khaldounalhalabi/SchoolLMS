<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreBehavioralNoteWebRequest;
use App\Models\BehavioralNote;
use App\Models\StudentProfile;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherBehavioralNoteController extends Controller
{
    /**
     * GET /teacher/behavioral-notes
     * Shows create form (with student dropdown) + paginated list of previously written notes.
     */
    public function index(): View
    {
        $teacher      = Auth::user();
        $classroomIds = $teacher->teacherAssignments()->pluck('classroom_id');

        $students = StudentProfile::whereIn('classroom_id', $classroomIds)
            ->with(['student', 'classroom.grade'])
            ->get();

        $notes = BehavioralNote::where('teacher_user_id', $teacher->id)
            ->with(['student', 'student.studentProfile.classroom'])
            ->orderByDesc('date')
            ->paginate(20);

        return view('teacher.behavioral-notes', compact('students', 'notes'));
    }

    /**
     * POST /teacher/behavioral-notes
     */
    public function store(StoreBehavioralNoteWebRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $teacher = Auth::user();

        abort_unless(
            TeacherSubjectClassroom::where('teacher_user_id', $teacher->id)
                ->whereHas('classroom.studentProfiles', fn ($q) => $q->where('user_id', $validated['student_user_id']))
                ->exists(),
            403,
            'You are not a teacher of this student.'
        );

        BehavioralNote::create([
            ...$validated,
            'teacher_user_id' => $teacher->id,
        ]);

        $student = User::with('parents')->findOrFail($validated['student_user_id']);
        foreach ($student->parents as $parent) {
            $parent->notify(new SystemNotification(
                'New behavioral note',
                'A new behavioral note was added for :student.',
                route('parent.behavioral-notes', ['child_id' => $student->id]),
                'behavioral_note',
                ['student' => $student->name],
            ));
        }

        return redirect()->route('teacher.behavioral-notes')
            ->with('success', 'Behavioral note created successfully.');
    }
}
