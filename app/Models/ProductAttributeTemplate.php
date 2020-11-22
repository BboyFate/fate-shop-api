<?php

namespace App\Models;

class ProductAttributeTemplate extends Model
{
    protected $fillable = [
        'name',
        'attributes',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    public $timestamps = false;
}
