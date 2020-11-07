<?php

namespace App\Admin\Http\Controllers\V1\Auth;

use Illuminate\Http\Request;
use App\Models\AdminVueMenu;
use App\Admin\Http\Controllers\V1\Controller;
use App\Admin\Http\Resources\AdminVueMenuCollection;
use App\Admin\Http\Resources\AdminVueMenuResource;

class VueMenusController extends Controller
{
    public function index()
    {
        $menus = AdminVueMenu::query()->get();

        return $this->response->success(new AdminVueMenuCollection($menus));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request, 'requestValidation');

        $menu = new AdminVueMenu([
            'name'      => $request->input('name'),
            'path'      => $request->input('path'),
            'meta'      => $request->input('meta'),
            'redirect'  => $request->input('redirect', ''),
            'is_showed' => $request->input('is_showed'),
            'component' => $request->input('component'),
        ]);

        if ($request->input('parent_id')) {
            $menu->parent()->associate($request->input('parent_id'));
        }

        $menu->save();

        return $this->response->created(new AdminVueMenuResource($menu));
    }

    public function show($id)
    {
        $menu = AdminVueMenu::query()->findOrFail($id);

        return $this->response->success(new AdminVueMenuResource($menu));
    }

    public function update(Request $request, $id)
    {
        $menu = AdminVueMenu::query()->findOrFail($id);
        $this->validateRequest($request, 'requestValidation');

        $menu->update([
            'name'      => $request->input('name'),
            'path'      => $request->input('path'),
            'meta'      => $request->input('meta'),
            'redirect'  => $request->input('redirect', ''),
            'is_showed' => $request->input('is_showed'),
            'component' => $request->input('component'),
        ]);

        return $this->response->success(new AdminVueMenuResource($menu));
    }

    public function destroy($id)
    {
        $menu = AdminVueMenu::query()->findOrFail($id);
        $menu->delete();

        return $this->response->noContent();
    }

    public function roleMenus(Request $request)
    {
        if ($request->user()->isAdmin()) {
            $menus = AdminVueMenu::query()->get();
        } else {
            $menus = $request->user()->roles()->first()->vueMenus()->get();
        }

        return $this->response->success(new AdminVueMenuCollection($menus));
    }
}
