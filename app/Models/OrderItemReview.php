<?php

namespace App\Models;

class OrderItemReview extends Model
{
    protected $fillable = [
        'rating',
        'review',
        'images',
        'is_verified',
        'reviewed_at',
        'verified_at',
    ];

    protected $dates = [
        'reviewed_at',
        'verified_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'images'      => 'array',
    ];

    public $timestamps = false;

    public function productSku()
    {
        return $this->belongsTo(ProductSku::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 作用域 查询最新评论
     * @param $query
     * @param int $limit 查询条数
     * @return mixed
     */
    public function scopeRecentReviews($query, $limit = 3)
    {
        return $query->orderBy('reviewed_at', 'desc')->limit($limit);
    }
}
