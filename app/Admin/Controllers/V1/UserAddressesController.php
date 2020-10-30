<?php

namespace App\Admin\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\UserAddress;
use App\Admin\Resources\UserAddressResource;

class UserAddressesController extends Controller
{
    public function index(Request $request)
    {
        $builder = UserAddress::query();

        // 关键词搜索
        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where(function ($query) use ($like) {
                $query->where('province', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('district', 'like', $like)
                    ->orWhere('address', 'like', $like)
                    ->orWhere('contact_name', 'like', $like)
                    ->orWhere('contact_phone', 'like', $like);
            });
        }

        $users = $builder->paginate();

        return $this->response->success(UserAddressResource::collection($users));
    }

    public function show($id)
    {
        $user = UserAddress::query()->findOrFail($id);

        return $this->response->success(new UserAddressResource($user));
    }
}
