<?php

namespace App\Models;

class ProductSkuAttribute extends Model
{
    protected $fillable = ['name'];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }
}
