<?php

namespace App\Models;

class ProductProperty extends Model
{
    protected $fillable = ['name', 'value'];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
