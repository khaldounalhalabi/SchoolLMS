<?php

namespace App\Services\Diagnostic;

use App\Domain\MasteryLevel;
use App\Models\KnowledgeMapResult;
use App\Models\LearningObjective;
use App\Models\Subject;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use App\Services\Access\StudentRecordAccessService;
use Illuminate\Support\Collection;

class DiagnosticKnowledgeMapService
{
    public function __construct(private StudentRecordAccessService $access) {}

    public function adminData(?int $subjectId, ?int $studentId): array
    {
        $subjects = Subject::orderBy('name')->get();
        $students = User::where('role', 'student')->orderBy('name')->get();
        $subject = $subjectId ? Subject::find($subjectId) : null;
        $student = $studentId ? User::find($studentId) : null;
        $tree = $subject && $student
            ? $this->treeFor($student->id, $subject->id)
            : [];

        return compact('subjects', 'students', 'subject', 'student', 'tree');
    }

    public function studentData(User $student, ?int $subjectId): array
    {
        $subjects = Subject::orderBy('name')->get();
        $subject = $subjectId ? Subject::find($subjectId) : null;
        $tree = $subject ? $this->treeFor($student->id, $subject->id) : [];

        return compact('subjects', 'subject', 'tree');
    }

    public function teacherData(User $teacher, ?int $subjectId, ?int $studentId): array
    {
        $assignments = TeacherSubjectClassroom::where('teacher_user_id', $teacher->id)
            ->with(['subject', 'classroom'])
            ->get();
        $subjectIds = $assignments->pluck('subject_id')->unique();
        $classroomIds = $assignments->pluck('classroom_id')->unique();
        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        $students = User::where('role', 'student')
            ->whereHas('studentProfile', fn ($query) => $query->whereIn('classroom_id', $classroomIds))
            ->orderBy('name')
            ->get();
        $subject = $subjectId && $subjectIds->contains($subjectId) ? Subject::find($subjectId) : null;
        $student = $studentId && $students->contains('id', $studentId) ? $students->find($studentId) : null;
        $tree = $subject && $student && $this->teacherCanView($teacher, $student->id, $subject->id)
            ? $this->treeFor($student->id, $subject->id)
            : [];

        return compact('subjects', 'students', 'subject', 'student', 'tree');
    }

    public function teacherCanView(User $teacher, int $studentId, int $subjectId): bool
    {
        return $this->access->canTeacherView($teacher, $studentId, $subjectId);
    }

    public function treeFor(int $studentId, int $subjectId): array
    {
        $masteryMap = KnowledgeMapResult::where('student_user_id', $studentId)
            ->pluck('mastery_percent', 'learning_objective_id');
        $rootObjectives = LearningObjective::with('children.children')
            ->where('subject_id', $subjectId)
            ->whereNull('parent_id')
            ->get();

        return $this->buildTree($rootObjectives, $masteryMap);
    }

    private function buildTree(Collection $objectives, Collection $masteryMap): array
    {
        return $objectives->map(function (LearningObjective $objective) use ($masteryMap): array {
            $mastery = $masteryMap->get($objective->id);

            return [
                'id' => $objective->id,
                'name' => $objective->name,
                'description' => $objective->description,
                'mastery_percent' => $mastery !== null ? (float) $mastery : null,
                'level' => MasteryLevel::fromPercent($mastery !== null ? (float) $mastery : null),
                'children' => $this->buildTree($objective->children, $masteryMap),
            ];
        })->values()->toArray();
    }

    public function toApiTree(array $tree): array
    {
        return array_map(function (array $node): array {
            $node['level'] = $node['level']->name;
            $node['children'] = $this->toApiTree($node['children']);

            return $node;
        }, $tree);
    }
}
