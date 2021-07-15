<?php

namespace App\Models\Users;

use App\Models\Model;

class UserCartItem extends Model
{
    protected $fillable = ['qty'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productSku()
    {
        return $this->belongsTo(\App\Models\Products\ProductSku::class);
    }
}
