<?php

namespace App\Admin\Http\Controllers\V1\Products;

use Illuminate\Http\Request;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Products\ProductAttributeTemplate;
use App\Admin\Http\Resources\Products\ProductAttributeTemplateResource;

class AttributeTemplatesController extends Controller
{
    public function index(Request $request)
    {
        $builder = ProductAttributeTemplate::query();

        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where(function ($query) use ($like) {
                $query->where('name', 'like', $like);
            });
        }

        $limit = $request->input('limit', 10);

        $templates = $builder->orderBy('id', 'desc')->paginate($limit);

        return $this->response->success(ProductAttributeTemplateResource::collection($templates));
    }

    public function show($templateId)
    {
        $template = ProductAttributeTemplate::query()->findOrFail($templateId);

        return $this->response->success(new ProductAttributeTemplateResource($template));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $template = ProductAttributeTemplate::query()->create($request->only(['name', 'attributes']));

        return $this->response->created(new ProductAttributeTemplateResource($template));
    }

    public function update($templateId, Request $request)
    {
        $template = ProductAttributeTemplate::query()->findOrFail($templateId);
        $this->validateRequest($request);

        $template->update($request->only(['name', 'attributes']));

        return $this->response->success(new ProductAttributeTemplateResource($template));
    }

    public function destroy($templateId)
    {
        $template = ProductAttributeTemplate::query()->findOrFail($templateId);
        $template->delete();

        return $this->response->noContent();
    }
}
