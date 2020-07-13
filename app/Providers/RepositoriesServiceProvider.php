<?php


namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Models\User;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\Eloquent\EloquentUserRepository;

class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserRepository::class, function () {
            return new EloquentUserRepository(new User());
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            UserRepository::class
        ];
    }
}
