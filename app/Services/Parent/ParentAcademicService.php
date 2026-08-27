<?php

namespace App\Services\Parent;

use App\Domain\ReportCardData;
use App\Models\User;
use App\Services\Grade\ReportCardService;
use Illuminate\Http\Response;

class ParentAcademicService
{
    public function __construct(
        private ParentAccessService $access,
        private ParentChildContextService $context,
        private ReportCardService $reports,
    ) {}

    public function results(User $parent, mixed $childId, ?int $semesterId): array
    {
        ['children' => $children, 'selectedChild' => $selectedChild] = $this->context->forParent($parent, $childId);
        $semesters = $this->reports->semesters();
        $selectedSemesterId = $semesterId ?: $semesters->first()?->id;
        $academic = $selectedChild
            ? $this->reports->studentResults($selectedChild, $selectedSemesterId)
            : [
                'summaries' => collect(),
                'grades' => collect(),
                'examTypes' => collect(),
            ];
        $summaries = $academic['summaries'];
        $grades = $academic['grades'];
        $examTypes = $academic['examTypes'];

        return compact(
            'parent',
            'children',
            'selectedChild',
            'semesters',
            'selectedSemesterId',
            'summaries',
            'grades',
            'examTypes',
        );
    }

    public function reportCard(User $parent, User $child, ?int $semesterId): ReportCardData
    {
        $child = $this->access->assertChild($parent, $child);

        return $this->reports->assemble($child, $semesterId ?: null);
    }

    public function download(User $parent, User $child, ?int $semesterId): Response
    {
        $child = $this->access->assertChild($parent, $child);

        return $this->reports->download($child, $semesterId);
    }
}
