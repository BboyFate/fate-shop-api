<?php

namespace App\Models;

class ProductSkuAttribute extends Model
{
    protected $fillable = ['name', 'value'];

    public $timestamps = false;

    protected $casts = [
        'value' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
