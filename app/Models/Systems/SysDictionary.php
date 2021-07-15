<?php

namespace App\Models\Systems;

use App\Models\Model;

class SysDictionary extends Model
{
    protected $fillable = [
        'lavel',
        'value',
        'value_type',
        'is_disabled',
        'is_default',
        'sorted',
        'remark',
    ];

    protected $casts = [
        'is_disabled' => 'boolean',
        'is_default'  => 'boolean',
        'remark'      => 'json',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // 如果更新为默认，其他则更新为 false
            if ($model->is_default === true) {
                $model->type()->dictionaries()->update(['is_default' => false]);
            }
        });
    }

    /**
     * 作用域 只查询可用的字典数据
     *
     * @return mixed
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_disabled', false);
    }

    public function type()
    {
        return $this->belongsTo(SysDictionaryType::class, 'dictionary_type_id');
    }
}
