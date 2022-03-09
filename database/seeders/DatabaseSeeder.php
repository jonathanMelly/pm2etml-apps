<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $seeds = [PermissionResetSeeder::class,PermissionV1Seeder::class,UserV1Seeder::class];

        collect($seeds)->each(function($seeder){
            $exitCode = Artisan::call('db:seed', [
                '--class' => $seeder,
                '--force' => true
            ]);
            if($exitCode<0)
            {
                Log::error("Cannot seed $seeder");
            }
        });

        // \App\Models\User::factory(10)->create();

    }
}
