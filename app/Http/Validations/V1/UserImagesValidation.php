<?php

namespace App\Http\Validations\V1;

use App\Models\UserImage;

class UserImagesValidation
{
    public function store()
    {
        $rules = [
            'image' => 'required|mimes:jpeg,bmp,png,gif',
            'type'  => 'required|in:' . implode(array_keys(UserImage::$morphMap), ',')
        ];

        return [
            'rules' => $rules
        ];
    }
}
