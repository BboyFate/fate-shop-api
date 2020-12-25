<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'amount',
        'price',
        'rating',
        'review',
        'images',
        'is_verified',
        'reviewed_at'
    ];

    protected $dates = ['reviewed_at'];

    protected $casts = [
        'images'      => 'array',
        'is_verified' => 'boolean',
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
