<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Models\User;
use App\Repositories\Contracts\UserRepository;

class EloquentUserRepository extends AbstractEloquentRepository implements UserRepository
{
    protected $modelName = User::class;
}
