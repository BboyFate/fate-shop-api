<?php

namespace App\Models;

class Banner extends Model
{
    const TYPE_WEAPP = 'weapp';
    public static $typeMap = [
        self::TYPE_WEAPP => '小程序',
    ];

    protected $fillable = [
        'name',
        'url',
        'type',
        'sorted',
        'is_showed',
    ];

    protected $casts = [
        'is_showed' => 'boolean',
    ];
}
