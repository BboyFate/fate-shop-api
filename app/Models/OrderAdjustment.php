<?php

namespace App\Models;

class OrderAdjustment extends Model
{
    const UPDATED_AT = null;

    /**
     * 调整的类型
     * 记录影响商品实际支付金额的原因
     */
    const TYPE_SHIPPING  = 'shipping';
    const TYPE_PROMOTION = 'promotion';
    public static $typeMap = [
        self::TYPE_SHIPPING  => '运费',
        self::TYPE_PROMOTION => '促销',
    ];

    protected $fillable = [
        'type',
        'label',
        'origin_code',
        'is_included',
        'amount',
    ];

    protected $dates = [

    ];

    protected $casts = [
        'is_included' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
