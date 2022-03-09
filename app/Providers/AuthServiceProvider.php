<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
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
    public function boot()
    {
        $this->registerPolicies();

        //https://spatie.be/docs/laravel-permission/v5/basic-usage/super-admin
        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('root') ? true : null;
        });


        //Sets dummy password check any other place than prod
        Auth::provider('o365-eloquent-mix', function ($app, array $config) {
            if (app()->environment('production')) {
                return new O365EloquantMixUserProvider($config['model'],$config['endpoint']);
            }
            else{
                return new O365EloquantMixTestUserProvider($config['model'],$config['endpoint']);
            }
        });


    }
}
