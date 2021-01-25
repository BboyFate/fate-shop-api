<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class OrderRefundCause extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sorted',
        'is_showed',
    ];

    protected $casts = [
        'is_showed' => 'boolean'
    ];
}
