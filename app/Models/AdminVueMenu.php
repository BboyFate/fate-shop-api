<?php

namespace App\Models;

class AdminVueMenu extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'path',
        'redirect',
        'meta',
        'str_ids',
        'level',
        'is_showed',
        'component',
    ];

    protected $casts = [
        'meta'      => 'json',
        'is_showed' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        // 监听 Category 的创建事件，用于初始化 path 和 level 字段值
        static::creating(function (AdminVueMenu $menu) {
            // 如果创建的是一个根类目
            if (is_null($menu->parent_id)) {
                // 将层级设为 0
                $menu->level = 0;
                // 将 path 设为 -
                $menu->str_ids  = '-';
            } else {
                // 将层级设为父类目的层级 + 1
                $menu->level = $menu->parent->level + 1;
                // 将 path 值设为父类目的 path 追加父类目 ID 以及最后跟上一个 - 分隔符
                $menu->str_ids  = $menu->parent->str_ids . $menu->parent_id . '-';
            }
        });
    }

    /**
     * 访问器
     * 获取所有祖先菜单的 ID 值
     *
     * @return array
     */
    public function getPathIdsAttribute()
    {
        // trim($str, '-') 将字符串两端的 - 符号去除
        // explode() 将字符串以 - 为分隔切割为数组
        // 最后 array_filter 将数组中的空值移除
        return array_filter(explode('-', trim($this->str_ids, '-')));
    }

    /**
     * 访问器
     * 获取所有祖先菜单并按层级排序
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsAttribute()
    {
        return AdminVueMenu::query()
            ->whereIn('id', $this->path_ids)
            ->orderBy('level')
            ->orderBy('sorted')
            ->get();
    }

    public function parent()
    {
        return $this->belongsTo(AdminVueMenu::class);
    }

    public function children()
    {
        return $this->hasMany(AdminVueMenu::class, 'parent_id');
    }

    public function scopeIsShowed($query)
    {
        return $query->where('is_showed', true);
    }
}
