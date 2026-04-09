<?php

namespace App\Services\Staff;

use App\Models\User;
use App\Models\StaffMember;

class StaffMemberService
{
    public function createProfile(array $data, User $user): StaffMember
    {
        return StaffMember::create(array_merge($data, ['user_id' => $user->id]));
    }

    public function updateProfile(StaffMember $staffMember, array $data): StaffMember
    {
        $staffMember->update($data);

        return $staffMember->fresh();
    }
}
