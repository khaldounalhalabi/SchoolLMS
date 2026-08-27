<?php

namespace App\Services\Parent;

use App\Models\BehavioralNote;
use App\Models\User;

class ParentBehavioralNoteService
{
    public function __construct(private ParentChildContextService $context) {}

    public function forParent(User $parent, mixed $childId): array
    {
        ['children' => $children, 'selectedChild' => $selectedChild] = $this->context->forParent($parent, $childId);
        $notes = collect();

        if ($selectedChild) {
            $notes = BehavioralNote::where('student_user_id', $selectedChild->id)
                ->with('teacher')
                ->orderByDesc('date')
                ->paginate(20);
        }

        return compact('parent', 'children', 'selectedChild', 'notes');
    }
}
