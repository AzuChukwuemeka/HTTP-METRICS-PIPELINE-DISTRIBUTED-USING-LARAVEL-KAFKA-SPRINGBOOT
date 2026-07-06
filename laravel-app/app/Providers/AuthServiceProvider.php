<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {

        $this->registerPolicies();//
        Gate::define("deleteUsers", function (User $user){
            return $user->isanAdmin();
        });
        Gate::define("createAdminUsers", function (User $user){
            return $user->isanAdmin();
        });
        Gate::define("modifyApiKeys", function (User $user){
            return $user->isanAdmin();
        });
    }
}
