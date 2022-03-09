<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

use App\Providers\O365EloquantMixTestUserProvider;
use App\Providers\O365EloquantMixUserProvider;

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
     * Manually caches custom eup
     */
    protected ?O365EloquantMixUserProvider $eup = null;

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
            if ($this->eup == null) {
                $eupClassName = app()->environment('production') ?
                    O365EloquantMixUserProvider::class :
                    O365EloquantMixTestUserProvider::class;
                $this->eup = new $eupClassName($config['model'], $config['endpoint']);
            }
            return $this->eup;
        });

    }
}
