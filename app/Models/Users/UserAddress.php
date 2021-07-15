<?php

namespace App\Models\Users;

use App\Models\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'province',
        'city',
        'district',
        'address',
        'zip',
        'contact_name',
        'contact_phone',
        'last_used_at',
        'is_default',
    ];

    protected $appends = ['full_address'];

    /**
     * 指定字段返回时间日期对象
     *
     * @var array
     */
    protected $dates = ['last_used_at'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 访问器
     * 获取完整的地址
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        return "{$this->province}{$this->city}{$this->district}{$this->address}";
    }

    /**
     * 作用域 默认地址
     * @param $query
     * @return mixed
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
