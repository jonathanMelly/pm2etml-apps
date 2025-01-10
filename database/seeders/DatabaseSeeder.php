<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "Starting seeding\n";

        $seeds = [
            PermissionResetSeeder::class,
            PermissionV1Seeder::class,
            PermissionV2Seeder::class,
            AcademicPeriodSeeder::class,
            //GroupSeeder::class, => started by UserV1Seeder as it depends on it
            UserV1Seeder::class,
            SkillSeeder::class,
            JobSeeder::class,
            ContractSeeder::class];

        collect($seeds)->each(function ($seeder) {

            echo '-->Seeding '.basename($seeder);

            $exitCode = Artisan::call('db:seed', [
                '--class' => $seeder,
                '--force' => true,
                //'-vvv' does not bring more output
            ]);
            if ($exitCode < 0) {
                echo " ==> \e[0;31mKO\e[0m\n";
                Log::error("$seeder KO");
            } else {
                echo " ==> \e[0;32mOK\e[0m\n";
                Log::info("$seeder OK");
            }
        });

        // \App\Models\User::factory(10)->create();

    }
}
