<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class UserProductSearch extends Model
{
    use SoftDeletes;

    const UPDATED_AT = null;

    protected $fillable = [
        'content',
    ];
}
