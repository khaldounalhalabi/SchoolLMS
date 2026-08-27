<?php

namespace App\Services\Parent;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class ParentAccessService
{
    public function children(User $parent): Collection
    {
        return $parent->children()
            ->with('studentProfile.classroom.grade')
            ->get();
    }

    public function findChild(User $parent, mixed $childId): ?User
    {
        $query = $parent->children()->with('studentProfile.classroom.grade');

        if ($childId !== null) {
            $query->whereKey($childId);
        }

        return $query->first();
    }

    public function assertChild(
        User $parent,
        int|User $child,
        ?string $message = null,
    ): User {
        $authorizedChild = $this->findChild(
            $parent,
            $child instanceof User ? $child->getKey() : $child,
        );

        if (! $authorizedChild) {
            throw new AuthorizationException(
                $message ?? __('This student is not linked to your account.')
            );
        }

        return $authorizedChild;
    }
}
