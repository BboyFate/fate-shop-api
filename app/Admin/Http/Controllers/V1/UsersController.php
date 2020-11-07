<?php

namespace App\Admin\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\User;
use App\Admin\Http\Resources\UserResource;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $builder = User::query();

        // 关键词搜索
        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where(function ($query) use ($like) {
                $query->where('nickname', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        $users = $builder->paginate();

        return $this->response->success(UserResource::collection($users));
    }

    public function show($id)
    {
        $user = User::query()->findOrFail($id);

        return $this->response->success(new UserResource($user));
    }
}
