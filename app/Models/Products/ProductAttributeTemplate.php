<?php

namespace App\Models\Products;

use App\Models\Model;

class ProductAttributeTemplate extends Model
{
    protected $fillable = [
        'name',
        'attributes',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];
}
