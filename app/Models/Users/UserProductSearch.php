<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class UserProductSearch extends Model
{
    use SoftDeletes;

    const UPDATED_AT = null;

    protected $fillable = [
        'content',
    ];
}
