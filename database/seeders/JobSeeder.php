<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\User;
use Database\Factories\JobFactory;
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

        Job::factory()->afterMaking(
            function (Job $job) use ($faker) {
                $img = $faker->image(null, 350, 350);
                $imgName = basename($img);
                rename($img,storage_path('dmz-assets/').$imgName);
                $job->image=$imgName;
        })->afterCreating(
            function (Job $job) use($faker) {
                $client = User::find($faker->numberBetween(1,User::count()/2-1));
                $job->clients()->attach($client->id);
                if(rand(0,1)==0)
                {
                    $client = User::find($faker->numberBetween(User::count()/2,User::count()));
                    $job->clients()->attach($client->id);
                }

        })->count(10)->create();
    }
}
