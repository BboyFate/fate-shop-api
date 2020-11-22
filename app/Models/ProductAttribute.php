<?php

namespace App\Models;

class ProductAttribute extends Model
{
    protected $fillable = ['name', 'values'];

    public $timestamps = false;

    protected $casts = [
        'values' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
