<?php

namespace App\Models;

use Intervention\Image\Facades\Image;

class SystemImage extends Model
{
    protected $fillable = [
        'name',
        'mime',
        'path',
        'size',
    ];

    public function category()
    {
        return $this->belongsTo(SystemImageCategory::class);
    }

    public function getDataUrlAttribute()
    {
        return (string) Image::make($this->path)->encode('data-url');
    }
}
