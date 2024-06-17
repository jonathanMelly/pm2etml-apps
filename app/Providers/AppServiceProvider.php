<?php

namespace App\Providers;

use App\Constants\AttachmentTypes;
use App\Constants\MorphTargets;
use App\Models\JobDefinition;
use App\Models\JobDefinitionMainImageAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //https://spatie.be/docs/laravel-permission/v5/prerequisites
        Schema::defaultStringLength(191);

        Relation::enforceMorphMap([
            AttachmentTypes::STI_JOB_DEFINITION_MAIN_IMAGE_ATTACHMENT => JobDefinitionMainImageAttachment::class,
            //AttachmentTypes::JOB_DEFINITION_ATTACHMENT => JobDefinitionAttachment::class,
            MorphTargets::MORPH2_JOB_DEFINITION => JobDefinition::class,
            MorphTargets::MORPH2_USER => User::class, //Used for spatie permissions
        ]);
    }
}
