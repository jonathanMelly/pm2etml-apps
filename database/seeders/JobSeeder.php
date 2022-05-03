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
            function (JobDefinition $job) use($faker) {

                $count = 10;

                $candidates = User::role(RoleName::TEACHER)->orderBy('id')->limit($count)->get();
                $client = $candidates[rand(1,$count/2-1)];

                //First teacher has at least 3 jobs
                if($this->counter++<3 || rand(0,10)<5)
                {
                    $job->providers()->attach($candidates[0]->id);//often put base teacher (for easier testing)
                }


                $job->providers()->attach($client->id);
                if(rand(0,1)==0)
                {
                    $client = $candidates[rand($count/2,$count-1)];
                    $job->providers()->attach($client->id);
                }

        })->count(app()->environment('testing')?5:21)->create();
    }
}
