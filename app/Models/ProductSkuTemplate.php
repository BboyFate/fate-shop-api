<?php

namespace App\Models;

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
