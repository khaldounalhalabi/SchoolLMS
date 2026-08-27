<?php

namespace App\Services\Attendance;

use App\Enums\AbsenceJustificationStatus;
use App\Models\AbsenceJustification;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TeacherAttendanceQueryService
{
    public function __construct(private AttendanceService $attendance) {}

    public function formData(User $teacher, mixed $classroomId, string $date): array
    {
        $classrooms = $this->classrooms($teacher);
        $selectedClassroomId = $classroomId !== null && $classroomId !== ''
            ? (int) $classroomId
            : null;
        $selectedDate = $date;
        $students = collect();
        $existingAttendance = collect();

        if ($selectedClassroomId) {
            if (! $this->attendance->teacherCanRecord($teacher, $selectedClassroomId, null)) {
                throw new AuthorizationException(__('You are not assigned to this classroom.'));
            }

            $students = $this->attendance->getClassroomStudents($selectedClassroomId);
            $existingAttendance = Attendance::where('classroom_id', $selectedClassroomId)
                ->whereDate('date', $date)
                ->get()
                ->keyBy('student_user_id');
        }

        return compact(
            'classrooms',
            'selectedDate',
            'selectedClassroomId',
            'students',
            'existingAttendance',
        );
    }

    public function classrooms(User $teacher): Collection
    {
        return Classroom::whereHas(
            'teacherAssignments',
            fn ($query) => $query->where('teacher_user_id', $teacher->id),
        )
            ->with('grade')
            ->get();
    }

    public function pendingJustifications(User $teacher): LengthAwarePaginator
    {
        $classroomIds = $teacher->teacherAssignments()->pluck('classroom_id');

        return AbsenceJustification::with([
            'attendance.student',
            'attendance.classroom',
            'submittedBy',
        ])
            ->whereHas('attendance', fn ($query) => $query->whereIn('classroom_id', $classroomIds))
            ->where('status', AbsenceJustificationStatus::PENDING->value)
            ->orderByDesc('created_at')
            ->paginate(15);
    }
}
