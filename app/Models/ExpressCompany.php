<?php

namespace App\Models;

class ExpressCompany extends Model
{
    protected $fillable = [
        'name',
        'sorted',
        'is_default',
        'is_showed',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_showed'  => 'boolean',
    ];

    public function fees()
    {
        return $this->hasMany(ExpressFee::class);
    }

    /**
     * 作用域 默认物流公司
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
