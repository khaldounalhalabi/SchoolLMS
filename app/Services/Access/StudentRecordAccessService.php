<?php

namespace App\Services\Access;

use App\Enums\UserRole;
use App\Models\Subject;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class StudentRecordAccessService
{
    public function canView(User $actor, User $student, ?Subject $subject = null): bool
    {
        if ($actor->role === UserRole::ADMIN || $actor->is($student)) {
            return true;
        }

        if ($actor->role === UserRole::PARENT) {
            return $this->isParentOf($actor, $student->getKey());
        }

        return $actor->role === UserRole::TEACHER
            && $this->canTeacherView($actor, $student->getKey(), $subject?->getKey());
    }

    public function assertCanView(User $actor, User $student, ?Subject $subject = null): void
    {
        if (! $this->canView($actor, $student, $subject)) {
            throw new AuthorizationException(__('You are not authorized to view this student\'s records.'));
        }
    }

    public function canTeacherView(User $teacher, int $studentId, ?int $subjectId = null): bool
    {
        if ($teacher->role === UserRole::ADMIN) {
            return true;
        }

        if ($teacher->role !== UserRole::TEACHER) {
            return false;
        }

        return TeacherSubjectClassroom::where('teacher_user_id', $teacher->id)
            ->when($subjectId !== null, fn ($query) => $query->where('subject_id', $subjectId))
            ->whereHas('classroom.studentProfiles', fn ($query) => $query->where('user_id', $studentId))
            ->exists();
    }

    public function assertTeacherCanView(
        User $teacher,
        int $studentId,
        ?int $subjectId = null,
        ?string $message = null,
    ): void {
        if (! $this->canTeacherView($teacher, $studentId, $subjectId)) {
            throw new AuthorizationException(
                $message ?? __('You are not assigned to this student and subject.')
            );
        }
    }

    public function isParentOf(User $parent, int $studentId): bool
    {
        return $parent->children()->whereKey($studentId)->exists();
    }
}
