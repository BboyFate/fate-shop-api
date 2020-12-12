<?php

namespace App\Models;

class UserSocial extends Model
{
    const TYPE_WECHAT = 'wechat';
    const TYPE_WEAPP  = 'weapp';

    public static $typeMap = [
        self::TYPE_WECHAT => '微信公众号',
        self::TYPE_WEAPP  => '微信小程序',
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'social_type',
        'openid',
        'unionid',
        'extra',
    ];

    protected $casts = [
        'extra' => 'json'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
