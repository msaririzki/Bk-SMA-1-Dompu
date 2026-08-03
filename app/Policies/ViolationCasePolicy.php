<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\ViolationCase;

class ViolationCasePolicy
{
    public function view(User $user, ViolationCase $case): bool
    {
        return $user->isStaff() || $user->student_id === $case->student_id;
    }

    public function update(User $user, ViolationCase $case): bool
    {
        if ($user->hasRole(UserRole::SuperAdmin, UserRole::Coordinator)) {
            return true;
        }
        if ($user->role !== UserRole::Counselor) {
            return false;
        }

        return $case->created_by === $user->id || $user->teacher?->assignedClasses()->whereKey($case->student->currentEnrollment?->class_id)->exists();
    }
}
