<?php

namespace App\Models;

class OrderItemShipment extends Model
{
    const SHIPMENT_STATE_PENDING   = 'pending';
    const SHIPMENT_STATE_READY     = 'ready';
    const SHIPMENT_STATE_DELIVERED = 'delivered';
    const SHIPMENT_STATE_RECEIVED  = 'received';
    const SHIPMENT_STATE_CANCELED  = 'canceled';
    public static $shipStateMap = [
        self::SHIPMENT_STATE_PENDING   => '未发货',
        self::SHIPMENT_STATE_READY     => '正在备货',
        self::SHIPMENT_STATE_DELIVERED => '已发货',
        self::SHIPMENT_STATE_RECEIVED  => '已收货',
        self::SHIPMENT_STATE_CANCELED  => '退货',
    ];

    protected $fillable = [
        'shipment_state',
        'express_no',
        'express_data',
        'readied_at',
        'delivered_at',
        'received_at',
        'canceled_at',
    ];

    protected $dates = [
        'readied_at',
        'delivered_at',
        'received_at',
        'canceled_at',
    ];

    protected $casts = [
        'express_data' => 'array',
    ];

    public $timestamps = false;

    public function order()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
