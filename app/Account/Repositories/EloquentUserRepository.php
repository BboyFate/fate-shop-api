<?php

namespace App\Account\Repositories;

use App\Account\Models\User;
use App\Account\Repositories\Contracts\UserRepository;

class EloquentUserRepository extends AbstractEloquentRepository implements UserRepository
{
    protected $modelName = User::class;
}
