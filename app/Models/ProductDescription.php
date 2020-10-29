<?php

namespace App\Models;

class ProductDescription extends Model
{
    protected $fillable = [
        'description',
    ];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
