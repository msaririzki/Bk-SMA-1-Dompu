<?php

namespace App\Policies;

use App\Models\SchoolDocument;
use App\Models\User;

class SchoolDocumentPolicy
{
    public function view(User $user, SchoolDocument $document): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, SchoolDocument $document): bool
    {
        return $user->isStaff() && ($user->id === $document->created_by || in_array($user->role->value, ['super_admin', 'coordinator_bk'], true));
    }
}
