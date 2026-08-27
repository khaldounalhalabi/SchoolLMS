<?php

namespace App\Services\Attendance;

use App\Enums\AbsenceJustificationStatus;
use App\Enums\UserRole;
use App\Models\AbsenceJustification;
use App\Models\Attendance;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\Parent\ParentAccessService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AbsenceJustificationService
{
    public function __construct(
        private ParentAccessService $access,
        private AttendanceService $attendance,
    ) {}

    public function submit(
        User $parent,
        Attendance $attendance,
        string $reason,
        ?UploadedFile $document,
    ): AbsenceJustification {
        $this->access->assertChild(
            $parent,
            $attendance->student_user_id,
            __('You are not a parent of this student.'),
        );

        if ($attendance->justification()->exists()) {
            throw new HttpException(422, __('A justification already exists for this absence.'));
        }

        $documentPath = $document?->store('justifications', 'local');

        $justification = AbsenceJustification::create([
            'attendance_id' => $attendance->id,
            'reason' => $reason,
            'submitted_by' => $parent->id,
            'document_path' => $documentPath,
            'status' => AbsenceJustificationStatus::PENDING,
        ]);

        $attendance->loadMissing('student', 'classroom');
        $teachers = TeacherSubjectClassroom::with('teacher')
            ->where('classroom_id', $attendance->classroom_id)
            ->get()
            ->pluck('teacher')
            ->filter()
            ->unique('id');

        foreach ($teachers as $teacher) {
            $teacher->notify(new SystemNotification(
                'Justification submitted',
                'A new absence justification was submitted for :student.',
                route('teacher.justifications'),
                'justification',
                ['student' => $attendance->student?->name ?? 'a student'],
            ));
        }

        return $justification;
    }

    public function download(User $user, AbsenceJustification $justification): StreamedResponse
    {
        $justification->loadMissing('attendance');
        $attendance = $justification->attendance;

        $authorized = false;
        if ($user->role === UserRole::PARENT) {
            $this->access->assertChild($user, $attendance->student_user_id);
            $authorized = true;
        } elseif ($user->role === UserRole::TEACHER) {
            $authorized = $this->attendance->teacherCanRecord($user, $attendance->classroom_id, null);
        }

        if (! $authorized) {
            throw new AuthorizationException(__('You are not authorized to access this document.'));
        }

        $path = $justification->document_path;
        $disk = Storage::disk('local');

        if (! $path || ! $disk->exists($path)) {
            throw new HttpException(404, __('Document not found.'));
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'bin';

        return $disk->download($path, "justification-{$justification->id}.{$extension}");
    }

    public function approve(User $teacher, AbsenceJustification $justification): void
    {
        $justification->loadMissing('attendance');
        $this->attendance->assertTeacherCanRecord(
            $teacher,
            $justification->attendance->classroom_id,
            null,
        );
        $this->attendance->approveJustification($justification);
    }

    public function reject(User $teacher, AbsenceJustification $justification): void
    {
        $justification->loadMissing('attendance');
        $this->attendance->assertTeacherCanRecord(
            $teacher,
            $justification->attendance->classroom_id,
            null,
        );
        $this->attendance->rejectJustification($justification);
    }
}
