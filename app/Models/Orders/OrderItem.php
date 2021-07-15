<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class OrderItem extends Model
{
    use SoftDeletes;

    const UPDATED_AT = null;

    protected $fillable = [
        'qty',
        'delivered_qty',
        'received_qty',
        'shipment_refunded_qty',
        'refunded_qty',
        'price',
        'price_total',
        'adjustment_total',
        'payment_total',
        'refunded_total',
        'has_reviewed',
        'has_applied_refund',
        'extra',
        'refund_state',
        'shipment_state',
        'receiving_state',
        'shipment_refund_state',
    ];

    protected $dates = [
    ];

    protected $casts = [
        'has_applied_refund' => 'boolean',
        'has_reviewed'       => 'boolean',
        'extra'              => 'array',
    ];

    /**
     * 子订单退款状态
     */
    const REFUND_STATE_PENDING   = 'pending';
    const REFUND_STATE_PARTIALLY = 'partially';
    const REFUND_STATE_ALL       = 'all';
    public static $refundStateMap = [
        self::REFUND_STATE_PENDING   => '无',
        self::REFUND_STATE_PARTIALLY => '部分退款',
        self::REFUND_STATE_ALL       => '全部退款',
    ];
    /**
     * 子订单发货状态
     */
    const SHIPMENT_STATE_PENDING             = 'pending';
    const SHIPMENT_STATE_DELIVERED           = 'delivered';
    const SHIPMENT_STATE_PARTIALLY_DELIVERED = 'partially_delivered';
    public static $shipmentStateMap = [
        self::SHIPMENT_STATE_PENDING             => '待发货',
        self::SHIPMENT_STATE_DELIVERED           => '全部发货',
        self::SHIPMENT_STATE_PARTIALLY_DELIVERED => '部分发货',
    ];
    /**
     * 子订单收货状态
     */
    const RECEIVING_STATE_PENDING            = 'pending';
    const RECEIVING_STATE_RECEIVED           = 'received';
    const RECEIVING_STATE_PARTIALLY_RECEIVED = 'partially_received';
    public static $receivingStateMap = [
        self::RECEIVING_STATE_PENDING            => '无',
        self::RECEIVING_STATE_RECEIVED           => '全部收货',
        self::RECEIVING_STATE_PARTIALLY_RECEIVED => '部分收货',
    ];
    /**
     * 子订单退货状态
     */
    const SHIPMENT_REFUND_STATE_PENDING            = 'pending';
    const SHIPMENT_REFUND_STATE_REFUNDED           = 'refunded';
    const SHIPMENT_REFUND_STATE_PARTIALLY_REFUNDED = 'partially_refunded';
    public static $shipmentRefundStateMap = [
        self::SHIPMENT_REFUND_STATE_PENDING            => '无',
        self::SHIPMENT_REFUND_STATE_REFUNDED           => '全部退货',
        self::SHIPMENT_REFUND_STATE_PARTIALLY_REFUNDED => '部分退货',
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Products\Product::class);
    }

    public function productSku()
    {
        return $this->belongsTo(\App\Models\Products\ProductSku::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function units()
    {
        return $this->hasMany(OrderItemUnit::class);
    }

    public function review()
    {
        return $this->hasOne(OrderItemReview::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\Users\User::class);
    }

    public function refunds()
    {
        return $this->hasMany(OrderItemRefund::class);
    }

    public function orderShipments()
    {
        return $this->belongsTo(OrderShipment::class);
    }

    public function orderAdjustments()
    {
        return $this->hasMany(OrderAdjustment::class);
    }
}
