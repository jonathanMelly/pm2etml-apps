<?php

namespace Database\Seeders;

use App\Constants\RoleName;
use App\Models\JobDefinition;
use App\Models\User;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    protected int $counter=0;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $total = app()->environment('testing')?5:21;
        $faker = Container::getInstance()->make(Generator::class);

        JobDefinition::factory()->afterMaking(
            function (JobDefinition $job) use ($faker) {
                if(app()->environment('testing'))
                {
                    $imgName = $faker->imageUrl(350, 350);
                }
                else
                {
                    $img = $faker->image(null, 350, 350);
                    $imgName = basename($img);
                    rename($img,storage_path('dmz-assets/').$imgName);
                }

                $job->image=$imgName;
        })->afterCreating(
            function (JobDefinition $job) use($faker,$total) {

                $clientCounts = 10;

                $candidates = User::role(RoleName::TEACHER)->orderBy('id')->limit($clientCounts)->get();
                $client = $candidates[rand(1,$clientCounts/2-1)];

                //First teacher has at least 80% of the jobs
                if($this->counter++<(80/100*$total) || rand(0,10)<5)
                {
                    $job->providers()->attach($candidates[0]->id);//often put base teacher (for easier testing)
                }


                $job->providers()->attach($client->id);
                if(rand(0,1)==0)
                {
                    $client = $candidates[rand($clientCounts/2,$clientCounts-1)];
                    $job->providers()->attach($client->id);
                }

        })->count($total)->create();
    }
}
