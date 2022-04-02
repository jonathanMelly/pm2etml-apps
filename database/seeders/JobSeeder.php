<?php

namespace Database\Seeders;

use App\Models\JobDefinition;
use App\Models\User;
use Database\Factories\JobDefinitionFactory;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
                $client = User::findOrFail($faker->numberBetween(1,User::count()/2-1));
                $job->providers()->attach($client->id);
                if(rand(0,1)==0)
                {
                    $client = User::find($faker->numberBetween(User::count()/2,User::count()));
                    $job->providers()->attach($client->id);
                }

        })->count(10)->create();
    }
}
