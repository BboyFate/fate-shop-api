<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Services\UserImageService;
use App\Models\Users\UserImage;
use App\Http\Resources\UserImageResource;

class UserImagesController extends Controller
{
    public function store(Request $request, UserImageService $service)
    {
        $this->validateRequest($request);

        $image = $service->store($request->user(), $request->image, $request->type);

        return $this->response->success(new UserImageResource($image));
    }

    public function destroy($id)
    {
        $image = UserImage::query()->findOrFail($id);
        $image->delete();

        return $this->response->noContent();
    }
}
