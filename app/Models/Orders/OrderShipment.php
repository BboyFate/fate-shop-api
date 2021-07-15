<?php

namespace App\Models\Orders;

use App\Models\Model;

class OrderShipment extends Model
{
    const SHIPMENT_STATE_PENDING   = 'pending';
    const SHIPMENT_STATE_DELIVERED = 'delivered';
    const SHIPMENT_STATE_RECEIVED  = 'received';
    const SHIPMENT_STATE_REFUNDED  = 'refunded';
    public static $shipmentStateMap = [
        self::SHIPMENT_STATE_PENDING   => '未发货',
        self::SHIPMENT_STATE_DELIVERED => '已发货',
        self::SHIPMENT_STATE_RECEIVED  => '已收货',
        self::SHIPMENT_STATE_REFUNDED  => '退货',
    ];

    protected $fillable = [
        'shipment_state',
        'express_no',
        'extra',
        'delivered_at',
        'received_at',
        'refunded_at',
    ];

    protected $dates = [
        'delivered_at',
        'received_at',
        'refunded_at',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function expressCompany()
    {
        return $this->belongsTo(\App\Models\Expresses\ExpressCompany::class);
    }
}
