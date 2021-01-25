<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    const UPDATED_AT = null;

    /**
     * 订单类型
     */
    const TYPE_NORMAL       = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    const TYPE_SECKILL      = 'seckill';
    public static $typeMap = [
        self::TYPE_NORMAL       => '普通订单',
        self::TYPE_CROWDFUNDING => '众筹订单',
        self::TYPE_SECKILL      => '秒杀订单',
    ];

    /**
     * 订单主要状态
     */
    const ORDER_STATE_PENDING   = 'pending';
    const ORDER_STATE_NEW       = 'new';
    const ORDER_STATE_CANCELLED = 'cancelled';
    const ORDER_STATE_COMPLETED = 'completed';
    public static $orderStateMap = [
        self::ORDER_STATE_PENDING   => '待支付',
        self::ORDER_STATE_NEW       => '新的订单',
        self::ORDER_STATE_CANCELLED => '订单取消',
        self::ORDER_STATE_COMPLETED => '订单完成',
    ];

    /**
     * 订单支付状态
     */
    const PAYMENT_STATE_PENDING            = 'pending';
    const PAYMENT_STATE_PAID               = 'paid';
    const PAYMENT_STATE_CANCELLED          = 'cancelled';
    const PAYMENT_STATE_FULL_REFUNDED      = 'full_refunded';
    const PAYMENT_STATE_PARTIALLY_REFUNDED = 'partially_refunded';
    public static $paymentStateMap = [
        self::PAYMENT_STATE_PENDING            => '待支付',
        self::PAYMENT_STATE_PAID               => '已支付',
        self::PAYMENT_STATE_CANCELLED          => '取消支付',
        self::PAYMENT_STATE_FULL_REFUNDED      => '全额退款',
        self::PAYMENT_STATE_PARTIALLY_REFUNDED => '部分退款',
    ];

    /**
     * 订单运输状态
     */
    const SHIPMENT_STATE_PENDING             = 'pending';
    const SHIPMENT_STATE_READY               = 'ready';
    const SHIPMENT_STATE_FULL_DELIVERED      = 'full_delivered';
    const SHIPMENT_STATE_PARTIALLY_DELIVERED = 'partially_delivered';
    const SHIPMENT_STATE_FUll_RECEIVED       = 'full_received';
    const SHIPMENT_STATE_PARTIALLY_RECEIVED  = 'partially_received';
    const SHIPMENT_STATE_FUll_REFUNDED       = 'full_refunded';
    const SHIPMENT_STATE_PARTIALLY_REFUNDED  = 'partially_refunded';
    public static $shipmentStateMap = [
        self::SHIPMENT_STATE_PENDING             => '待处理',
        self::SHIPMENT_STATE_READY               => '正在备货',
        self::SHIPMENT_STATE_FULL_DELIVERED      => '全部发货',
        self::SHIPMENT_STATE_PARTIALLY_DELIVERED => '部分发货',
        self::SHIPMENT_STATE_FUll_RECEIVED       => '已收货',
        self::SHIPMENT_STATE_PARTIALLY_RECEIVED  => '部分收货',
        self::SHIPMENT_STATE_FUll_REFUNDED       => '全部退货',
        self::SHIPMENT_STATE_PARTIALLY_REFUNDED  => '部分退货',
    ];

    protected $fillable = [
        'type',
        'no',
        'address',
        'adjustment_total',
        'item_adjustment_total',
        'original_total',
        'payment_total',
        'qty_item',
        'is_closed',
        'order_state',
        'payment_state',
        'shipment_state',
        'extra',
        'remark',
        'ip_address',
        'paid_at',
        'completed_at',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
        'address'   => 'array',
        'extra'     => 'array',
    ];

    protected $dates = [
        'paid_at',
        'completed_at',
    ];

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::creating(function ($model) {
            // 如果没有订单流水号，则生成一个
            if (! $model->no) {
                $model->no = static::getAvailableNo();
                // 如果生成失败，则终止创建订单
                if (! $model->no) {
                    return false;
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * 生成订单流水号
     *
     * @return bool|string
     * @throws \Exception
     */
    public static function getAvailableNo()
    {
        // 订单流水号前缀
        $prefix = date('YmdHis');
        for ($i = 0; $i < 10; $i++) {
            // 随机生成 6 位的数字
            $no = $prefix.str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // 判断是否已经存在
            if (! static::query()->where('no', $no)->exists()) {
                return $no;
            }
        }
        \Log::warning('find order no failed');

        return false;
    }
}
