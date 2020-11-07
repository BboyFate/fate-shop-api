<?php

namespace App\Admin\Http\Controllers\V1;

use App\Models\SystemImageCategory;
use Illuminate\Http\Request;
use App\Models\SystemImage;
use App\Admin\Http\Resources\SystemImageResource;
use App\Handlers\ImageUploadHandler;

class SystemImagesController extends Controller
{
    public function index(Request $request)
    {
        $builder = SystemImage::query();

        if ($request->input('category_id') && $category = SystemImageCategory::query()->find($request->input('category_id'))) {
            $builder->whereHas('category', function ($query) use ($category) {
                // 这里的逻辑参考本章第一节
                $query->where('path', 'like', $category->path.$category->id.'-%');
            });
        }

        $limit = $request->input('limit', 10);

        $images = $builder->orderBy('created_at', 'desc')->paginate($limit);

        return $this->response->success(SystemImageResource::collection($images));
    }

    public function store(Request $request, ImageUploadHandler $uploader)
    {
        $this->validateRequest($request);

        $user = $request->user();
        $result = $uploader->save($request->image, 'systems/images/', $user->id);

        $image = new SystemImage([
            'name' => $result['name'],
            'mime' => $result['mime'],
            'path' => $result['path'],
            'size' => $result['size'],
        ]);

        if ($request->category_id) {
            $image->category()->associate($request->category_id);
        }

        $image->save();

        return $this->response->success(new SystemImageResource($image));
    }

    public function destroy($id)
    {
        $image = SystemImage::query()->findOrFail($id);
        $image->delete();

        return $this->response->noContent();
    }

    public function imagesDestroy(Request $request)
    {
        $this->validateRequest($request);

        SystemImage::destroy($request->input('image_ids'));

        return $this->response->noContent();
    }

}
