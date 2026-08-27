<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SalaryTransfer;
use App\Models\User;

class SalaryTransferPolicy
{
    public function view(User $user, SalaryTransfer $salaryTransfer): bool
    {
        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        return $user->id === $salaryTransfer->teacher_user_id;
    }
}
