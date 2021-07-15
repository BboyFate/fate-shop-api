<?php

namespace App\Services;

use App\Handlers\ImageUploadHandler;
use App\Models\Users\UserImage;
use App\Models\Users\User;

class UserImageService
{
    public function store(User $user, $image, $imageType, $folder = 'users')
    {
        $uploader = new ImageUploadHandler;

        $result = $uploader->save($image, $folder, $user->id);

        $image = new UserImage([
            'imageable_type' => $imageType,
            'name'           => $result['name'],
            'mime'           => $result['mime'],
            'path'           => $result['path'],
            'size'           => $result['size'],
        ]);
        $image->user()->associate($user);
        $image->save();

        return $image;
    }
}
