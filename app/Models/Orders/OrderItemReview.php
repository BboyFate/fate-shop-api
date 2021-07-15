<?php

namespace App\Models\Orders;

use App\Models\Model;

class OrderItemReview extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'rating',
        'review',
        'is_verified',
        'verified_at',
    ];

    protected $dates = [
        'verified_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Products\Product::class);
    }

    public function productSku()
    {
        return $this->belongsTo(\App\Models\Products\ProductSku::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\Users\User::class);
    }

    public function images()
    {
        return $this->morphMany(\App\Models\Users\UserImage::class, 'imageable');
    }

    /**
     * 作用域 查询最新评论
     *
     * @param $query
     * @param int $limit 查询条数
     *
     * @return mixed
     */
    public function scopeRecentReviews($query, $limit = 3)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
