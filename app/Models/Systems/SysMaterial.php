<?php

namespace App\Models\Systems;

use App\Models\Model;

class SysMaterial extends Model
{
    /**
     * 素材类型
     */
    const TYPE_IMAGE   = 'image';
    const TYPE_VIDEO = 'video';
    public static $typeMap = [
        self::TYPE_IMAGE   => '图片',
        self::TYPE_VIDEO => '视频',
    ];

    protected $fillable = [
        'name',
        'size',
        'type',
        'mime',
        'path',
    ];

    public function group()
    {
        return $this->belongsTo(SysMaterialGroup::class);
    }

    /**
     * 随机查询一条数据
     * @param $query
     * @param $type
     * @return mixed
     */
    public function scopeRandomByType($query, $type)
    {
        return $query->where('type', $type)->inRandomOrder();
    }
}
