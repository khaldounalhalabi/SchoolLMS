<?php

namespace App\Services\Attendance;

use App\Data\BulkAttendanceData;
use App\Enums\AbsenceJustificationStatus;
use App\Enums\AttendanceStatus;
use App\Models\AbsenceJustification;
use App\Models\Attendance;
use App\Models\ScheduleSlot;
use App\Models\StudentProfile;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Assert that the given teacher is authorized to record attendance for a classroom.
     * Throws 403 if unauthorized.
     */
    public function teacherCanRecord(User $teacher, int $classroomId, ?int $scheduleSlotId): bool
    {
        if ($scheduleSlotId !== null) {
            $slot = ScheduleSlot::find($scheduleSlotId);

            return (bool) ($slot
                && $slot->teacher_user_id === $teacher->id
                && $slot->classroom_id === $classroomId);
        }

        return TeacherSubjectClassroom::where('teacher_user_id', $teacher->id)
            ->where('classroom_id', $classroomId)
            ->exists();
    }

    public function assertTeacherCanRecord(User $teacher, int $classroomId, ?int $scheduleSlotId): void
    {
        if (! $this->teacherCanRecord($teacher, $classroomId, $scheduleSlotId)) {
            throw new AuthorizationException(__('You are not assigned to this classroom.'));
        }
    }

    /**
     * Record bulk attendance. Uses updateOrCreate so resubmitting the same day
     * updates existing records instead of creating duplicates.
     *
     * @param  array  $entries  [['student_id' => int, 'status' => string], ...]
     */
    public function recordBulk(User $teacher, BulkAttendanceData $data): Collection
    {
        $this->assertTeacherCanRecord($teacher, $data->classroomId, $data->scheduleSlotId);

        $studentIds = collect($data->entries)->pluck('studentId')->unique();
        $enrolledStudentIds = StudentProfile::where('classroom_id', $data->classroomId)
            ->whereIn('user_id', $studentIds)
            ->pluck('user_id');

        if ($enrolledStudentIds->count() !== $studentIds->count()) {
            throw new AuthorizationException(__('One or more students are not enrolled in this classroom.'));
        }

        $records = collect();
        foreach ($data->entries as $entry) {
            $records->push(Attendance::updateOrCreate(
                [
                    'student_user_id' => $entry->studentId,
                    'classroom_id' => $data->classroomId,
                    'date' => $data->date,
                ],
                [
                    'status' => $entry->status,
                    'schedule_slot_id' => $data->scheduleSlotId,
                    'recorded_by' => $teacher->id,
                ]
            ));
        }

        $this->notifyParentsOfAbsences($data);

        return $records;
    }

    private function notifyParentsOfAbsences(BulkAttendanceData $data): void
    {
        $studentIds = collect($data->entries)
            ->filter(fn ($entry) => $entry->status === AttendanceStatus::ABSENT)
            ->pluck('studentId')
            ->unique();

        $students = User::with('parents')->whereIn('id', $studentIds)->get();

        foreach ($students as $student) {
            foreach ($student->parents as $parent) {
                $parent->notify(new SystemNotification(
                    'Student absent',
                    ':student was marked absent on :date.',
                    route('parent.attendance', ['child_id' => $student->id]),
                    'attendance',
                    [
                        'student' => $student->name,
                        'date' => $data->date,
                    ],
                ));
            }
        }
    }

    /**
     * Approve a justification and cascade attendance status to 'excused'.
     */
    public function approveJustification(AbsenceJustification $justification): void
    {
        DB::transaction(function () use ($justification): void {
            $justification->update(['status' => AbsenceJustificationStatus::APPROVED]);
            $justification->attendance->update(['status' => AttendanceStatus::EXCUSED]);
        });
    }

    /**
     * Reject a justification without changing attendance status.
     */
    public function rejectJustification(AbsenceJustification $justification): void
    {
        $justification->update(['status' => AbsenceJustificationStatus::REJECTED]);
    }

    /**
     * Get students enrolled in a classroom for the attendance form.
     */
    public function getClassroomStudents(int $classroomId): Collection
    {
        return StudentProfile::where('classroom_id', $classroomId)
            ->with('student')
            ->get();
    }
}
