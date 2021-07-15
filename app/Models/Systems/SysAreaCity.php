<?php

namespace App\Models\Systems;

use App\Models\Model;

class SysAreaCity extends Model
{
    protected $fillable = [
        'name',
        'province_code',
        'city_code',
    ];
}
