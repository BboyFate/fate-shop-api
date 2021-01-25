<?php

namespace App\Models;

use Intervention\Image\Facades\Image as FacadeImage;

class UserImage extends Model
{
    const UPDATED_AT = null;

    /**
     * 图片关联的模型，这里转换成 字符串 而不是 Model 类名
     */
    const MORPH_ORDER_REFUND   = 'order_refund';
    const MORPH_ORDER_REVIEW   = 'order_review';
    public static $morphMap = [
        self::MORPH_ORDER_REFUND   => '订单退款',
        self::MORPH_ORDER_REVIEW   => '订单评价',
    ];

    protected $fillable = [
        'imageable_type',
        'name',
        'mime',
        'path',
        'size',
    ];

    public function imageable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDataUrlAttribute()
    {
        return (string) FacadeImage::make($this->path)->encode('data-url');
    }

    /**
     * 获取所有未关联的图片
     *
     * @param $query
     * @param $ids
     * @return mixed
     */
    public function scopeUnusedImages($query, $ids)
    {
        return $query->whereIn('id', $ids)
                ->where('imageable_id', 0)
                ->get();
    }

}
