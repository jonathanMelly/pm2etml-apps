<?php

namespace Database\Seeders;

use App\Models\JobDefinition;
use App\Models\User;
use Database\Factories\JobDefinitionFactory;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Container::getInstance()->make(Generator::class);

        JobDefinition::factory()->afterMaking(
            function (JobDefinition $job) use ($faker) {
                $img = $faker->image(null, 350, 350);
                $imgName = basename($img);
                rename($img,storage_path('dmz-assets/').$imgName);
                $job->image=$imgName;
        })->afterCreating(
            function (JobDefinition $job) use($faker) {
                $candidates = User::role('prof')->get();
                $client = $candidates[($faker->numberBetween(0,$candidates->count()/2-1))];
                $job->providers()->attach($client->id);
                if(rand(0,1)==0)
                {
                    $client = $candidates[($faker->numberBetween($candidates->count()/2,$candidates->count()-1))];;
                    $job->providers()->attach($client->id);
                }

        })->count(10)->create();
    }
}
