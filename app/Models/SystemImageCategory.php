<?php

namespace App\Models;

class SystemImageCategory extends Model
{
    const TYPE_PRODUCT = 'product';
    public static $typeMap = [
        self::TYPE_PRODUCT => '商品图片',
    ];

    protected $fillable = [
        'name',
        'type',
        'mime',
        'path',
        'size',
    ];

    protected static function boot()
    {
        parent::boot();
        // 监听 Category 的创建事件，用于初始化 path 和 level 字段值
        static::creating(function (SystemImageCategory $category) {
            // 如果创建的是一个根类目
            if (is_null($category->parent_id)) {
                // 将层级设为 0
                $category->level = 0;
                // 将 path 设为 -
                $category->path  = '-';
            } else {
                // 将层级设为父类目的层级 + 1
                $category->level = $category->parent->level + 1;
                // 将 path 值设为父类目的 path 追加父类目 ID 以及最后跟上一个 - 分隔符
                $category->path  = $category->parent->path . $category->parent_id . '-';
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(SystemImageCategory::class);
    }

    public function children()
    {
        return $this->hasMany(SystemImageCategory::class, 'parent_id');
    }

    /**
     * 访问器
     * 获取所有祖先类目的 ID 值
     *
     * @return array
     */
    public function getPathIdsAttribute()
    {
        // trim($str, '-') 将字符串两端的 - 符号去除
        // explode() 将字符串以 - 为分隔切割为数组
        // 最后 array_filter 将数组中的空值移除
        return array_filter(explode('-', trim($this->path, '-')));
    }

    /**
     * 访问器
     * 获取所有祖先类目并按层级排序
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsAttribute()
    {
        return SystemImageCategory::query()
            ->whereIn('id', $this->path_ids)
            ->orderBy('level')
            ->get();
    }

    /**
     * 访问器
     * 获取以 - 为分隔的所有祖先类目名称以及当前类目的名称
     *
     * @return mixed
     */
    public function getFullNameAttribute()
    {
        return $this->ancestors  // 获取所有祖先类目
        ->pluck('name') // 取出所有祖先类目的 name 字段作为一个数组
        ->push($this->name) // 将当前类目的 name 字段值加到数组的末尾
        ->implode(' - '); // 用 - 符号将数组的值组装成一个字符串
    }
}
