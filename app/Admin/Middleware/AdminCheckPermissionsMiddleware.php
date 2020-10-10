<?php

namespace App\Admin\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use App\Support\Response;

class AdminCheckPermissionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user->hasRole(config('app.super_admin_role_name'))) {
            return $next($request);
        }

        // 获取当前路由名称
        $routeName = $request->route()[1]['as'];

        $permission = Permission::query()->where('name', $routeName)->first();
        if (! $permission || ! $user->hasPermissionTo($permission)) {
            (new Response())->errorForbidden();
        }

        return $next($request);
    }
}
