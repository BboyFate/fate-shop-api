<?php

namespace App\Admin\Http\Controllers\V1\Users;

use Illuminate\Http\Request;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Users\User;
use App\Admin\Http\Resources\Users\UserResource;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $builder = User::query();
        $limit = $request->input('limit', 10);

        // 关键词搜索
        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where(function ($query) use ($like) {
                $query->where('nickname', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        if ($createdAt = $request->input('created_at')) {
            $builder->whereBetween('created_at', [ $createdAt[0], $createdAt[1] ]);
        }

        $list = $builder->paginate($limit);

        return $this->response->success(UserResource::collection($list));
    }

    public function show($userId)
    {
        $data = User::query()->findOrFail($userId);

        return $this->response->success(new UserResource($data));
    }
}
