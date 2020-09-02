<?php

namespace App\Admin\Models;

class AdminImage extends Model
{
    protected $fillable = ['type', 'path'];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }
}
