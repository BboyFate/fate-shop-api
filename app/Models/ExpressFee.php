<?php

namespace App\Models;

class ExpressFee extends Model
{
    /**
     * 运费计算类型
     */
    const FEE_TYPE_WEIGHT = 'weight';
    const FEE_TYPE_VOLUME = 'volume';
    public static $feeTypeMap = [
        self::FEE_TYPE_WEIGHT => '重量',
        self::FEE_TYPE_VOLUME => '体积',
    ];

    protected $fillable = [
        'name',
        'fee_type',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function expressCompany()
    {
        return $this->belongsToMany(ExpressCompany::class);
    }

    public function items()
    {
        return $this->hasMany(ExpressFeeItem::class);
    }

    /**
     * 作用域 搜索默认运费模板
     *
     * @param $query
     * @return mixed
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
