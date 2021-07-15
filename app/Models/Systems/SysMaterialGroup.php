<?php

namespace App\Models\Systems;

use App\Models\Model;

class SysMaterialGroup extends Model
{
    protected $fillable = [
        'name',
        'level',
        'level_path',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (SysMaterialGroup $group) {
            // 如果创建的是一个根分组
            if (is_null($group->parent_id)) {
                // 将层级设为 0
                $group->level = 0;
                // 将 path 设为 -
                $group->level_path  = '-';
            } else {
                // 将层级设为父类目的层级 + 1
                $group->level = $group->parent->level + 1;
                // 将 level_path 值设为：父分组的 level_path 追加父类目 ID 、最后加一个 - 分隔符
                $group->level_path  = $group->parent->path . $group->parent_id . '-';
            }
        });
    }

    public function materials()
    {
        return $this->hasMany(SysMaterial::class);
    }

    public function parent()
    {
        return $this->belongsTo(SysMaterialGroup::class);
    }

    public function children()
    {
        return $this->hasMany(SysMaterialGroup::class, 'parent_id');
    }
}
