<?php

namespace App\Models\Systems;

use App\Models\Model;

class SysAreaDistrict extends Model
{
    protected $fillable = [
        'name',
        'city_code',
        'district_code',
    ];
}
