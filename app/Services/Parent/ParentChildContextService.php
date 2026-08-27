<?php

namespace App\Services\Parent;

use App\Models\User;
use Illuminate\Support\Collection;

class ParentChildContextService
{
    public function __construct(private ParentAccessService $access) {}

    /**
     * @return array{children: Collection, selectedChild: ?User}
     */
    public function forParent(User $parent, mixed $childId): array
    {
        $children = $this->access->children($parent);
        $selectedChild = $children->first(
            fn (User $child): bool => $childId === null || (string) $child->getKey() === (string) $childId
        );

        return compact('children', 'selectedChild');
    }
}
