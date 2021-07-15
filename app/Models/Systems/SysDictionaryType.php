<?php

namespace App\Models\Systems;

use App\Models\Model;

class SysDictionaryType extends Model
{
    protected $fillable = [
        'name',
        'type',
        'is_disabled',
        'remark',
    ];

    protected $casts = [
        'is_disabled' => 'boolean',
    ];

    /**
     * 作用域 只查询可用的字典类型
     *
     * @return mixed
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_disabled', false);
    }

    public function dictionaries()
    {
        return $this->hasMany(SysDictionary::class, 'dictionary_type_id');
    }
}
