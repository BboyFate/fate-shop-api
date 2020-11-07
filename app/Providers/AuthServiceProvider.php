<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

//        $this->app['auth']->viaRequest('api', function ($request) {
//            if ($request->input('api_token')) {
//                return User::where('api_token', $request->input('api_token'))->first();
//            }
//        });

        // 自定义策略文件的寻找逻辑
        Gate::guessPolicyNamesUsing(function($class) {
            // class_basename 是 Laravel 提供的一个辅助函数，可以获取类的简短名称
            // 例如传入 \App\Models\User 会返回 User
            return '\\App\\Policies\\' . class_basename($class) . 'Policy';
        });
    }
}
