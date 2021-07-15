<?php

namespace App\Models\Products;

use App\Models\Model;
use Carbon\Carbon;

class SeckillProduct extends Model
{
    protected $fillable = ['start_at', 'end_at'];

    protected $dates = ['start_at', 'end_at'];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 访问器
     * 当前时间 早于 秒杀开始时间 时返回 true
     *
     * @return mixed
     */
    public function getIsBeforeStartAttribute()
    {
        return Carbon::now()->lt($this->start_at);
    }

    /**
     * 访问器
     * 当前时间 晚于 秒杀结束时间 时返回 true
     *
     * @return bool
     */
    public function getIsAfterEndAttribute()
    {
        return Carbon::now()->gt($this->end_at);
    }
}
