<?php

namespace App\Models\Products;

use App\Models\Model;

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
