<?php

namespace App\Models\Products;

use App\Models\Model;

class ProductSkuTemplate extends Model
{
    protected $fillable = [
        'name',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public $timestamps = false;
}
