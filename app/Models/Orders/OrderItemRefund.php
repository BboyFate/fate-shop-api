<?php

namespace App\Models\Orders;

use Ramsey\Uuid\Uuid;
use App\Models\Model;

class OrderItemRefund extends Model
{
    /**
     * 退款状态
     */
    const REFUND_STATE_PENDING    = 'pending';
    const REFUND_STATE_PROCESSING = 'processing';
    const REFUND_STATE_DISAGREED  = 'disagreed';
    const REFUND_STATE_SUCCEED    = 'succeed';
    const REFUND_STATE_FAILED     = 'failed';
    const REFUND_STATE_CANCELLED  = 'cancelled';
    public static $refundStateMap = [
        self::REFUND_STATE_PENDING    => '待处理',
        self::REFUND_STATE_PROCESSING => '退款中',
        self::REFUND_STATE_DISAGREED  => '拒绝退款',
        self::REFUND_STATE_SUCCEED    => '退款成功',
        self::REFUND_STATE_FAILED     => '退款失败',
        self::REFUND_STATE_CANCELLED  => '取消申请',
    ];
    /**
     * 退款方式
     */
    const REFUND_METHOD_ONLY_REFUND = 'only_refund';
    const REFUND_METHOD_ALL         = 'all';
    public static $refundMethodMap = [
        self::REFUND_METHOD_ONLY_REFUND => '仅退款',
        self::REFUND_METHOD_ALL         => '退货退款',
    ];
    /**
     * 买家退款运输状态
     */
    const SHIPMENT_STATE_PENDING   = 'pending';
    const SHIPMENT_STATE_DELIVERED = 'delivered';
    const SHIPMENT_STATE_RECEIVED  = 'received';
    public static $shipmentStateMap = [
        self::SHIPMENT_STATE_PENDING   => '待买家发货',
        self::SHIPMENT_STATE_DELIVERED => '买家已发货',
        self::SHIPMENT_STATE_RECEIVED  => '仓库已收货',
    ];

    protected $fillable = [
        'refund_method',
        'refund_no',
        'thirdparty_no',
        'refund_method',
        'refund_state',
        'apply_qty',
        'apply_total',
        'shipment_state',
        'express_no',
        'extra',
        'applied_at',
        'agreed_at',
        'disagreed_at',
        'delivered_at',
        'received_at',
        'cancelled_at',
        'refunded_at',
    ];

    protected $dates = [
        'applied_at',
        'agreed_at',
        'disagreed_at',
        'delivered_at',
        'received_at',
        'cancelled_at',
        'refunded_at',
    ];

    protected $casts = [
        'extra' => 'array'
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

    public function user()
    {
        return $this->belongsTo(\App\Models\Users\User::class);
    }

    public function expressCompany()
    {
        return $this->belongsTo(\App\Models\Expresses\ExpressCompany::class);
    }

    /**
     * 生成唯一的退款订单号
     *
     * @return string
     * @throws \Exception
     */
    public static function getAvailableRefundNo()
    {
        do {
            // Uuid类可以用来生成大概率不重复的字符串
            $no = Uuid::uuid4()->getHex();
            // 为了避免重复我们在生成之后在数据库中查询看看是否已经存在相同的退款订单号
        } while (self::query()->where('refund_no', $no)->exists());

        return $no;
    }
}
