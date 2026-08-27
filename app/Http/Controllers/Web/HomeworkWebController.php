<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ReviewHomeworkSubmissionWebRequest;
use App\Http\Requests\Web\StoreHomeworkWebRequest;
use App\Http\Requests\Web\SubmitHomeworkWebRequest;
use App\Models\HomeworkAssignment;
use App\Models\HomeworkSubmission;
use App\Models\StudentEnrollment;
use App\Models\TeacherSubjectClassroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HomeworkWebController extends Controller
{
    public function teacherIndex(): View
    {
        $teacher = auth()->user();
        $assignments = TeacherSubjectClassroom::where('teacher_user_id', $teacher->id)
            ->with(['subject', 'classroom.grade', 'academicYear'])
            ->orderByDesc('academic_year_id')
            ->get();
        $homeworks = HomeworkAssignment::where('teacher_user_id', $teacher->id)
            ->with(['subject', 'classroom.grade', 'academicYear'])
            ->withCount('submissions')
            ->orderByDesc('due_date')
            ->paginate(15);

        return view('teacher.homework.index', compact('assignments', 'homeworks'));
    }

    public function teacherStore(StoreHomeworkWebRequest $request): RedirectResponse
    {
        $teacher = auth()->user();
        $data = $request->validated();
        $assignment = TeacherSubjectClassroom::whereKey($data['teacher_assignment_id'])
            ->where('teacher_user_id', $teacher->id)
            ->firstOrFail();
        $homeworkData = [
            'teacher_assignment_id' => $assignment->id,
            'teacher_user_id' => $assignment->teacher_user_id,
            'subject_id' => $assignment->subject_id,
            'classroom_id' => $assignment->classroom_id,
            'academic_year_id' => $assignment->academic_year_id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'],
            'max_score' => $data['max_score'],
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $homeworkData['attachment_path'] = $file->store('homework/assignments', 'local');
            $homeworkData['attachment_original_name'] = $file->getClientOriginalName();
        }

        HomeworkAssignment::create($homeworkData);

        return redirect()
            ->route('teacher.homework')
            ->with('success', __('Homework created successfully.'));
    }

    public function teacherSubmissions(HomeworkAssignment $homework): View
    {
        abort_unless($homework->teacher_user_id === auth()->id(), 404);
        $homework->load([
            'subject',
            'classroom.grade',
            'academicYear',
            'submissions.student',
        ]);

        return view('teacher.homework.submissions', compact('homework'));
    }

    public function reviewSubmission(
        ReviewHomeworkSubmissionWebRequest $request,
        HomeworkSubmission $submission,
    ): RedirectResponse {
        $submission->load('homework');
        abort_unless($submission->homework->teacher_user_id === auth()->id(), 404);

        $data = $request->validated();
        if ($data['grade'] !== null && (float) $data['grade'] > (float) $submission->homework->max_score) {
            throw ValidationException::withMessages([
                'grade' => __('The grade cannot exceed the homework maximum score.'),
            ]);
        }

        $submission->update([
            'status' => $data['status'],
            'grade' => $data['grade'] ?? null,
            'feedback' => $data['feedback'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by_user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('teacher.homework.submissions', $submission->homework)
            ->with('success', __('Submission reviewed successfully.'));
    }

    public function studentIndex(): View
    {
        $student = auth()->user();
        $enrollments = StudentEnrollment::active()
            ->where('student_user_id', $student->id)
            ->whereHas('academicYear', fn ($query) => $query->where('is_active', true))
            ->get(['classroom_id', 'academic_year_id']);
        $classroomIds = $enrollments->pluck('classroom_id');
        $academicYearIds = $enrollments->pluck('academic_year_id');
        $homeworks = HomeworkAssignment::whereIn('classroom_id', $classroomIds)
            ->whereIn('academic_year_id', $academicYearIds)
            ->with([
                'subject',
                'classroom.grade',
                'academicYear',
                'submissions' => fn ($query) => $query->where('student_user_id', $student->id),
            ])
            ->orderByDesc('due_date')
            ->paginate(15);

        return view('student.homework.index', compact('homeworks'));
    }

    public function studentSubmit(
        SubmitHomeworkWebRequest $request,
        HomeworkAssignment $homework,
    ): RedirectResponse {
        $student = auth()->user();
        $isEnrolled = StudentEnrollment::active()
            ->where('student_user_id', $student->id)
            ->where('classroom_id', $homework->classroom_id)
            ->where('academic_year_id', $homework->academic_year_id)
            ->exists();

        abort_unless($isEnrolled, 403);

        if (today()->gt($homework->due_date)) {
            throw ValidationException::withMessages([
                'submission' => __('The homework deadline has passed.'),
            ]);
        }

        $file = $request->file('submission');
        $path = $file->store('homework/submissions', 'local');
        $existing = HomeworkSubmission::where('homework_assignment_id', $homework->id)
            ->where('student_user_id', $student->id)
            ->first();

        DB::transaction(function () use ($existing, $homework, $student, $file, $path): void {
            if ($existing?->file_path) {
                Storage::disk('local')->delete($existing->file_path);
            }

            HomeworkSubmission::updateOrCreate(
                [
                    'homework_assignment_id' => $homework->id,
                    'student_user_id' => $student->id,
                ],
                [
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'submitted_at' => now(),
                    'status' => 'submitted',
                    'grade' => null,
                    'feedback' => null,
                    'reviewed_at' => null,
                    'reviewed_by_user_id' => null,
                ],
            );
        });

        return redirect()
            ->route('student.homework')
            ->with('success', __('Homework submitted successfully.'));
    }

    public function downloadAssignment(HomeworkAssignment $homework): BinaryFileResponse
    {
        abort_unless($homework->attachment_path, 404);
        $this->authorizeHomeworkAccess($homework);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($homework->attachment_path), 404);

        return response()->download(
            $disk->path($homework->attachment_path),
            $homework->attachment_original_name ?? basename($homework->attachment_path),
        );
    }

    public function downloadSubmission(HomeworkSubmission $submission): BinaryFileResponse
    {
        $submission->load('homework');
        $isTeacher = $submission->homework->teacher_user_id === auth()->id();
        $isStudent = $submission->student_user_id === auth()->id();
        abort_unless($isTeacher || $isStudent, 403);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($submission->file_path), 404);

        return response()->download($disk->path($submission->file_path), $submission->original_filename);
    }

    private function authorizeHomeworkAccess(HomeworkAssignment $homework): void
    {
        if ($homework->teacher_user_id === auth()->id()) {
            return;
        }

        $enrolled = StudentEnrollment::active()
            ->where('student_user_id', auth()->id())
            ->where('classroom_id', $homework->classroom_id)
            ->where('academic_year_id', $homework->academic_year_id)
            ->exists();

        abort_unless($enrolled, 403);
    }
}
