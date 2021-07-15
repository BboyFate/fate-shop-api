<?php

namespace App\Models\Systems;

use App\Models\Model;

class SysLog extends Model
{
    const UPDATED_AT = null;

    /**
     * 订单类型
     */
    const SOURCE_TYPE_ORDER_REFUND = 'order_refund';
    public static $typeMap = [
        self::SOURCE_TYPE_ORDER_REFUND => '订单退款',
    ];

    protected $fillable = [
        'source_type',
        'ip_address',
        'extra',
    ];

    protected $casts = [
        'extra' => 'json',
    ];
}
