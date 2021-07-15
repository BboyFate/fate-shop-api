<?php

namespace App\Policies;

use App\Models\Users\User;
use App\Models\Users\UserAddress;

class UserAddressPolicy
{
    public function own(User $currentUser, UserAddress $userAddress)
    {
        return $currentUser->id == $userAddress->user_id;
    }
}
