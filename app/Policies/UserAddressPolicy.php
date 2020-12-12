<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserAddress;

class UserAddressPolicy
{
    public function own(User $currentUser, UserAddress $userAddress)
    {
        return $currentUser->id == $userAddress->user_id;
    }
}
