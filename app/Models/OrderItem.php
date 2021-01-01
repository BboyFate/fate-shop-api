<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class OrderItem extends Model
{
    use SoftDeletes;

    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_APPLIED = 'applied';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';
    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING    => '未退款',
        self::REFUND_STATUS_APPLIED    => '已申请退款',
        self::REFUND_STATUS_PROCESSING => '退款中',
        self::REFUND_STATUS_SUCCESS    => '退款成功',
        self::REFUND_STATUS_FAILED     => '退款失败',
    ];

    protected $fillable = [
        'amount',
        'price',
        'refund_status',
        'refund_no',
        'refunded_money',
        'refunded_at',
        'refund_verified_at',
        'is_applied_refund',
        'extra',
        'reviewed',
    ];

    protected $dates = [
        'refunded_at',
        'refund_verified_at',
    ];

    protected $casts = [
        'is_applied_refund' => 'boolean',
        'reviewed'          => 'boolean',
        'extra'             => 'array',
    ];

    public $timestamps = false;

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
        return $this->belongsTo(OrderItemReview::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
