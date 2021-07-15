<?php

namespace App\Models\Expresses;

use App\Models\Model;

class ExpressFeeItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'provinces',
        'fees',
    ];

    protected $casts = [
        'provinces' => 'array',
        'fees'      => 'array',
    ];

    public function expressFee()
    {
        return $this->belongsTo(ExpressFee::class);
    }
}
