<?php

namespace App\Admin\Controllers\V1\Auth;

use App\Admin\Services\VueMenuService;
use Illuminate\Http\Request;
use App\Admin\Models\AdminVueMenu;
use App\Admin\Controllers\V1\Controller;
use App\Admin\Resources\AdminVueMenuResource;

class VueMenusController extends Controller
{
    public function index(Request $request, VueMenuService $service)
    {
        $menus = $service->getMenuTree(null, AdminVueMenu::query()->get());
       // return new AdminVueMenuResource($menus);
        return AdminVueMenuResource::collection($menus);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request, 'requestValidation');

        $menu = new AdminVueMenu([
            'name'     => $request->input('name'),
            'path'     => $request->input('path'),
            'meta'     => $request->input('meta'),
            'redirect' => $request->input('redirect', ''),
        ]);

        if ($request->input('parent_id')) {
            $menu->parent()->associate($request->input('parent_id'));
        }

        $menu->save();

        return new AdminVueMenuResource($menu);
    }

    public function show($id)
    {
        $menu = AdminVueMenu::query()->findOrFail($id);

        return new AdminVueMenuResource($menu);
    }

    public function update(Request $request, $id)
    {
        $menu = AdminVueMenu::query()->findOrFail($id);
        $this->validateRequest($request, 'requestValidation');

        $menu->update([
            'name'     => $request->input('name'),
            'path'     => $request->input('path'),
            'meta'     => $request->input('meta'),
            'redirect' => $request->input('redirect', ''),
        ]);

        return new AdminVueMenuResource($menu);
    }

    public function destroy($id)
    {
        $menu = AdminVueMenu::query()->findOrFail($id);
        $menu->delete();

        return $this->response->noContent();
    }

    public function roleMenus(Request $request, VueMenuService $service)
    {
        $menus = $request->user()->roles()->first()->vueMenus()->get();

        $menus = $service->getMenuTree(null, $menus);

        return AdminVueMenuResource::collection($menus);
    }
}
