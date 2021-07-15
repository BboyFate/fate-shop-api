<?php

namespace App\Models\Orders;

use App\Models\Model;

class OrderItemUnit extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'payment_price',
        'adjustment_total',
    ];

    protected $dates = [
    ];

    protected $casts = [
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function orderShipment()
    {
        return $this->belongsTo(OrderShipment::class);
    }

    public function orderItemRefund()
    {
        return $this->belongsTo(OrderItemRefund::class);
    }

    public function orderAdjustments()
    {
        return $this->hasMany(OrderAdjustment::class);
    }
}
