<?php

namespace App\Models\Expresses;

use App\Models\Model;

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

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // 如果更新为默认，其他则更新为 false
            if ($model->is_default === true) {
                ExpressCompany::query()->update(['is_default' => false]);
            }
        });
    }

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
