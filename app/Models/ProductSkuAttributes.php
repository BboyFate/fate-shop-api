<?php

namespace App\Models;

class ProductSkuAttributes extends Model
{
    protected $fillable = ['name'];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(ProductSku::class);
    }
}
