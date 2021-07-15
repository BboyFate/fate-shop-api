<?php

namespace App\Models\Systems;

use Spatie\Permission\Models\Permission;

class SysPermission extends Permission
{
    /**
     * 菜单类型
     */
    const TYPE_MENU      = 'menu';
    const TYPE_BTN       = 'btn';
    const TYPE_DIRECTORY = 'directory';
    public static $typeMap = [
        self::TYPE_MENU      => '菜单',
        self::TYPE_BTN       => '按钮',
        self::TYPE_DIRECTORY => '目录',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'name',
        'path',
        'sorted',
        'meta',
        'ids',
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

        // 监听创建事件
        static::creating(function (Permission $permission) {
            // 如果创建的是一个根类目
            if (! $permission->parent_id) {
                $permission->ids  = '-';
            } else {
                // 将 path 值设为父类目的 path 追加父类目 ID 以及最后跟上一个 - 分隔符
                $permission->ids  = $permission->parent->ids . $permission->parent_id . '-';
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
        return array_filter(explode('-', trim($this->ids, '-')));
    }

    /**
     * 访问器
     * 获取所有祖先菜单并按层级排序
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAncestorsAttribute()
    {
        return Permission::query()
            ->whereIn('id', $this->ids)
            ->orderBy('sorted')
            ->get();
    }

    public function parent()
    {
        return $this->belongsTo(Permission::class);
    }

    public function children()
    {
        return $this->hasMany(Permission::class, 'parent_id');
    }

    public function scopeShowed($query)
    {
        return $query->where('is_showed', true);
    }
}
