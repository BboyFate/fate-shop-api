<?php

namespace App\Admin\Controllers\V1;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Admin\Models\AdminImage;
use App\Admin\Resources\ImageResource;
use App\Handlers\ImageUploadHandler;

class ImagesController extends Controller
{
    public function store(Request $request, ImageUploadHandler $uploader, AdminImage $image)
    {
        $this->validateRequest($request);

        $user = $request->user();
        $size = $request->type == 'avatar' ? 416 : 1024;
        $result = $uploader->save($request->image, 'admin/' . Str::plural($request->type), $user->id, $size);

        $image->path          = $result['path'];
        $image->type          = $request->type;
        $image->admin_user_id = $user->id;
        $image->save();

        return new ImageResource($image);
    }
}
