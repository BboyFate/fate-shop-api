<?php

namespace App\Admin\Models;

class AdminImage extends Model
{
    const TYPE_PRODUCT = 'product';
    const TYPE_AVATAR  = 'avatar';
    public static $typeMap = [
        self::TYPE_PRODUCT => '商品图片',
        self::TYPE_AVATAR  => '管理员头像',
    ];

    protected $fillable = ['type', 'path'];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }
}
