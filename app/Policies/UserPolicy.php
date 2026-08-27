<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;
use App\Services\Access\StudentRecordAccessService;

class UserPolicy
{
    public function __construct(private StudentRecordAccessService $access) {}

    /**
     * Whether $actor may read any student record (grades, attendance, behavioral notes,
     * knowledge map) belonging to $student.
     *
     * Teachers are restricted to their assigned classrooms; blanket teacher access is
     * intentionally blocked — use the admin role for cross-classroom workflows.
     */
    public function viewKnowledgeMap(User $actor, User $student, Subject $subject): bool
    {
        return $this->access->canView($actor, $student, $subject);
    }

    public function viewRecords(User $actor, User $student): bool
    {
        return $this->access->canView($actor, $student);
    }
}
