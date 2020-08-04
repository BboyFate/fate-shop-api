<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * 判断订单是否是用户自己的
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function own(User $user, Order $order)
    {
        return $user->id == $order->user_id;
    }
}
