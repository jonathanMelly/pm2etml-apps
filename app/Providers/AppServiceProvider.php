<?php

namespace App\Providers;

use App\Constants\AttachmentTypes;
use App\Constants\MorphTargets;
use App\Models\JobDefinition;
use App\Models\JobDefinitionMainImageAttachment;
use App\Models\User;
use App\Models\WorkerContract;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //https://spatie.be/docs/laravel-permission/v5/prerequisites
        Schema::defaultStringLength(191);

        Relation::enforceMorphMap([
            AttachmentTypes::STI_JOB_DEFINITION_MAIN_IMAGE_ATTACHMENT => JobDefinitionMainImageAttachment::class,
            //AttachmentTypes::JOB_DEFINITION_ATTACHMENT => JobDefinitionAttachment::class,
            MorphTargets::MORPH2_JOB_DEFINITION => JobDefinition::class,
            MorphTargets::MORPH2_USER => User::class, //Used for spatie permissions
            MorphTargets::MORPH2_WORKER_CONTRACT => WorkerContract::class,
        ]);

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('azure', \SocialiteProviders\Azure\Provider::class);
        });

        $this->bootRoute();
    }

    public function bootRoute()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        //Based on 500 users with 10 apps and each 5 log tentatives within an hour (at max)
        //Big number but still could reduce an attack...
        //To do better, sso should ask for a SECRET...
        RateLimiter::for('sso', function (Request $request) {
            return Limit::perHour(25000)->by($request->user()?->id ?: $request->ip());
        });

    }
}
