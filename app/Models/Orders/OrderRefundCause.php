<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

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
