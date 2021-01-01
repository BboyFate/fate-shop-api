<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    const SHIP_STATUS_PENDING   = 'pending';
    const SHIP_STATUS_DELIVERED = 'delivered';
    const SHIP_STATUS_RECEIVED  = 'received';
    public static $shipStatusMap = [
        self::SHIP_STATUS_PENDING   => '未发货',
        self::SHIP_STATUS_DELIVERED => '已发货',
        self::SHIP_STATUS_RECEIVED  => '已收货',
    ];

    const TYPE_NORMAL       = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    const TYPE_SECKILL      = 'seckill';
    public static $typeMap = [
        self::TYPE_NORMAL       => '普通商品订单',
        self::TYPE_CROWDFUNDING => '众筹商品订单',
        self::TYPE_SECKILL      => '秒杀商品订单',
    ];

    protected $fillable = [
        'type',
        'no',
        'address',
        'total_amount',
        'remark',
        'paid_at',
        'payment_method',
        'payment_no',
        'closed',
        'ship_status',
        'ship_data',
        'shipped_at',
        'extra',
        'delivered_at',
    ];

    protected $casts = [
        'closed'    => 'boolean',
        'address'   => 'array',
        'extra'     => 'array',
        'ship_data' => 'array',
    ];

    protected $dates = [
        'paid_at',
        'shipped_at',
        'delivered_at',
    ];

    const UPDATED_AT = null;

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
