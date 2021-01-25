<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;

    const UPDATED_AT = null;

    protected $fillable = [
        'qty',
        'price',
        'price_total',
        'adjustment_total',
        'is_reviewed',
        'is_applied_refund',
        'extra',
    ];

    protected $dates = [
    ];

    protected $casts = [
        'is_applied_refund' => 'boolean',
        'is_reviewed'       => 'boolean',
        'extra'             => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productSku()
    {
        return $this->belongsTo(ProductSku::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function review()
    {
        return $this->hasOne(OrderItemReview::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function refund()
    {
        return $this->hasOne(OrderItemRefund::class);
    }

    public function shipment()
    {
        return $this->belongsTo(OrderItemShipment::class);
    }
}
