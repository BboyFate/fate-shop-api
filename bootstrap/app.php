<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration driectory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

$app->configure('app');
$app->configure('auth');
$app->configure('broadcasting');
$app->configure('cache');
$app->configure('database');
$app->configure('filesystems');
$app->configure('logging');
$app->configure('queue');
$app->configure('services');
$app->configure('views');
$app->configure('easysms');
$app->configure('permission');

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//     App\Http\Middleware\RandomDropSeckillRequest::class
// ]);

$app->routeMiddleware([
    'auth'                    => App\Http\Middleware\Authenticate::class,
    'random_drop'             => App\Http\Middleware\RandomDropSeckillRequest::class,
    'accept_header'           => App\Http\Middleware\AcceptHeader::class,
    'admin.guard'             => App\Admin\Middleware\AdminGuardMiddleware::class,
    'admin.check_permissions' => App\Admin\Middleware\AdminCheckPermissionsMiddleware::class,
    'admin.refresh'           => App\Admin\Middleware\RefreshAdminTokenMiddleware::class,
    'permission'              => Spatie\Permission\Middlewares\PermissionMiddleware::class,
    'role'                    => Spatie\Permission\Middlewares\RoleMiddleware::class,
]);

$app->alias('cache', \Illuminate\Cache\CacheManager::class);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

/*
* Application Service Providers...
*/
$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);

/*
 * Package Service Providers...
 */
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);
$app->register(Overtrue\LaravelLang\TranslationServiceProvider::class);
$app->register(SocialiteProviders\Manager\ServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(Yansongda\LaravelPay\PayServiceProvider::class);
$app->register(Intervention\Image\ImageServiceProviderLumen::class);
$app->register(Spatie\Permission\PermissionServiceProvider::class);

/*
 * Custom Service Providers.
 */
$app->register(App\Providers\EasySmsServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/api/v1.php';
    // require __DIR__.'/../routes/web.php';
});

$app->router->group([
    'namespace' => 'App\Admin\Controllers',
], function ($router) {
    require __DIR__.'/../app/Admin/v1.php';
});

return $app;
