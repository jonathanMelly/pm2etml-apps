<?php

namespace Database\Seeders;

use App\Constants\MorphTargets;
use App\Constants\RoleName;
use App\Models\Attachment;
use App\Models\JobDefinition;
use App\Models\JobDefinitionMainImageAttachment;
use App\Models\User;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
                //
        })->afterCreating(
            function (JobDefinition $job) use($faker,$total) {


                if(app()->environment('testing'))
                {
                    $imgName=$img= 'empty-test';
                    $size=strlen($img);
                }
                else
                {
                    $img = $faker->image(uploadDisk()->path(attachmentPathInUploadDisk(temporary: true)), 350, 350);
                    //bug with curl an via.placeholder...
                    if(!$img)
                    {
                        $imgName='job-'.$faker->numberBetween(1,2).'.png';
                        $size=strlen($imgName);
                    }
                    else
                    {
                        $imgName = basename($img);
                        $size = filesize($img);
                    }

                }
                //Do it manually to avoid filesystem pressure...
                JobDefinitionMainImageAttachment::create([
                    'name' => 'ori-'.$imgName,
                    'storage_path' => attachmentPathInUploadDisk($imgName,),
                    'attachable_id' => $job->id,
                    'attachable_type' => MorphTargets::MORPH2_JOB_DEFINITION,
                    'size' => $size
                ]);

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
