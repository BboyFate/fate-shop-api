<?php

namespace App\Models;

class OrderPayment extends Model
{
    /**
     * 付款状态
     */
    const PAYMENT_STATE_PENDING    = 'pending';
    const PAYMENT_STATE_CANCELLED  = 'cancelled';
    const PAYMENT_STATE_PROCESSING = 'processing';
    const PAYMENT_STATE_PAID       = 'paid';
    public static $paymentStateMap = [
        self::PAYMENT_STATE_PENDING    => '待支付',
        self::PAYMENT_STATE_CANCELLED  => '取消支付',
        self::PAYMENT_STATE_PROCESSING => '支付中',
        self::PAYMENT_STATE_PAID       => '已支付',
    ];

    const PAYMENT_TYPE_WECHAT = 'wechat';
    const PAYMENT_TYPE_ALIPAY = 'alipay';
    public static $paymentTypeMap = [
        self::PAYMENT_TYPE_WECHAT => '微信',
        self::PAYMENT_TYPE_ALIPAY => '支付宝',
    ];

    protected $fillable = [
        'payment_total',
        'payment_method',
        'payment_no',
        'payment_state',
        'paid_at',
    ];

    protected $dates = [
        'paid_at',
    ];

    protected $casts = [
    ];

    public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
