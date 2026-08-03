<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    public function view(User $user, Attachment $attachment): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $user->isStaff() && ($user->id === $attachment->uploaded_by || $user->role->value === 'super_admin');
    }
}
