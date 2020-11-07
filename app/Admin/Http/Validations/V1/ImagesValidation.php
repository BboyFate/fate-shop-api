<?php

namespace App\Admin\Http\Validations\V1;

class ImagesValidation
{
    public function store()
    {
        $rules = [
            'type' => 'required|string|in:avatar,product',
        ];

        if (request()->input('type') == 'avatar') {
            $rules['image'] = 'required|mimes:jpeg,bmp,png,gif|dimensions:min_width=200,min_height=200';
        } else {
            $rules['image'] = 'required|mimes:jpeg,bmp,png,gif';
        }

        return [
            'rules' => $rules
        ];
    }
}
