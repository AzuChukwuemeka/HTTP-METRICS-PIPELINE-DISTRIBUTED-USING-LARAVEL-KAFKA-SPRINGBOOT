<?php

namespace App\Providers;

use App\Http\Repositories\ApiKeyInfoRepositoryI;
use App\Http\Repositories\ApiKeyInfoRepositoryImpl;
use App\Http\Repositories\ApiKeyRepositoryI;
use App\Http\Repositories\ApiKeyRepositoryImpl;
use App\Http\Repositories\UserRepositoryI;
use App\Http\Repositories\UserRepositoryImpl;
use App\Http\Services\ApiKeyService;
use App\Http\Services\EventService;
use App\Http\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(UserService::class, UserService::class);
        $this->app->bind(UserRepositoryI::class, UserRepositoryImpl::class);
        $this->app->bind(ApiKeyService::class, ApiKeyService::class);
        $this->app->bind(ApiKeyRepositoryI::class, ApiKeyRepositoryImpl::class);
        $this->app->bind(ApiKeyInfoRepositoryI::class, ApiKeyInfoRepositoryImpl::class);
        $this->app->bind(EventService::class, EventService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
