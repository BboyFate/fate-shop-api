<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;

class OrderItemRefund extends Model
{
    /**
     * 退款状态
     */
    const REFUND_STATE_PENDING    = 'pending';
    const REFUND_STATE_PROCESSING = 'processing';
    const REFUND_STATE_SUCCEED    = 'succeed';
    const REFUND_STATE_FAILED     = 'failed';
    const REFUND_STATE_CANCELLED  = 'cancelled';
    public static $refundStateMap = [
        self::REFUND_STATE_PENDING    => '待处理',
        self::REFUND_STATE_PROCESSING => '退款中',
        self::REFUND_STATE_SUCCEED    => '退款成功',
        self::REFUND_STATE_FAILED     => '退款失败',
        self::REFUND_STATE_CANCELLED  => '取消申请',
    ];

    /**
     * 退款类型
     */
    const TYPE_ONLY_REFUND = 'only_refund';
    const TYPE_ALL_REFUND  = 'all_refund';
    public static $typeMap = [
        self::TYPE_ONLY_REFUND => '仅退款',
        self::TYPE_ALL_REFUND  => '退货退款',
    ];

    protected $fillable = [
        'refund_no',
        'refund_state',
        'refunded_qty',
        'refunded_total',
        'refunded_at',
        'refund_verified_at',
        'images',
        'extra',
        'type',
        'is_verified',
    ];

    protected $dates = [
        'refunded_at',
        'refund_verified_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'images'      => 'array',
        'extra'       => 'array'
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
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->morphMany(UserImage::class, 'imageable');
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
