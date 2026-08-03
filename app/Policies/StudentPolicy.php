<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function view(User $user, Student $student): bool
    {
        return $user->isStaff() || $user->student_id === $student->id;
    }

    public function update(User $user, Student $student): bool
    {
        if ($user->hasRole(UserRole::SuperAdmin, UserRole::Coordinator)) {
            return true;
        }
        if ($user->role !== UserRole::Counselor || ! $user->teacher) {
            return false;
        }
        $classId = $student->currentEnrollment?->class_id;

        return $classId && $user->teacher->assignedClasses()->whereKey($classId)->exists();
    }
}
