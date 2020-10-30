<?php

namespace App\Admin\Validations\V1;

class SystemImagesValidation
{
    public function store()
    {
        $rules = [
            'type'        => 'string|in:product',
            'image'       => 'required|mimes:jpeg,bmp,png,gif',
            'category_id' => 'exists:system_image_categories,id',
        ];

        return [
            'rules' => $rules
        ];
    }

    public function imagesDestroy()
    {
        $rules = [
            'image_ids' => 'required|array',
        ];

        return [
            'rules' => $rules
        ];
    }
}
