<?php


namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Account\Models\User;
use App\Account\Repositories\Contracts\UserRepository;
use App\Account\Repositories\EloquentUserRepository;

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
