<?php

namespace Tests\Feature;

use App\Constants\RoleName;
use App\Enums\JobPriority;
use App\Models\AcademicPeriod;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\JobDefinition;
use App\Models\User;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\ContractSeeder;
use Database\Seeders\GroupSeeder;
use Database\Seeders\JobSeeder;
use Database\Seeders\PermissionV1Seeder;
use Database\Seeders\UserV1Seeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\BrowserKitTestCase;

class JobDefinitionCreateTest extends BrowserKitTestCase
{
    use WithFaker;

    /* @var $teacher User */
    protected User $teacher;

    /**
     * @before
     * @return void
     */
    public function setUpLocal()
    {
        $this->afterApplicationCreated(function () {

            $this->multiSeed(
                AcademicPeriodSeeder::class,
                UserV1Seeder::class,
            //JobSeeder::class,
            //ContractSeeder::class
            );


            $this->teacher = User::role(RoleName::TEACHER)->firstOrFail();
            $this->be($this->teacher);

        });
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_teacher_can_create_a_job()
    {

        $image = base_path('tests/data/job-1.png');
        $providers = User::role(RoleName::TEACHER)
            //->where('id','>',1)
            ->orderBy('id')
            ->take(3)
            ->get(['id'])->pluck('id')->toArray();

        $this->visit(route('jobDefinitions.create'))
            ->submitForm(trans('Publish job offer'),
                [
                    'name'=>'lol',
                    'description'=>'description',
                    'required_xp_years'=> 1,
                    'priority'=>0,
                    'image_data'=>$image,
                    'providers'=>$providers,
                    'allocated_time'=>25,
                    'one_shot'=>1
                ],
                [
                    'image_data'=>'job.png',
                    'image_data-file' =>
                        [
                        //'name' => 'job.png',
                        'tmp_name' => $image
                        ],
                ]
            )
            //->seePageIs('/marketplace')
            ->seeText('Emploi "lol" ajouté');

        //unlink($image);

        /* @var $createdJob \App\Models\JobDefinition */
        $createdJob = JobDefinition::orderByDesc('id')->first();
        $this->assertEquals('lol',$createdJob->name);
        $this->assertEquals('description',$createdJob->description);
        $this->assertEquals(1,$createdJob->required_xp_years);
        $this->assertEquals(JobPriority::MANDATORY,$createdJob->priority);
        $this->assertEquals(25,$createdJob->allocated_time);
        $this->assertEquals(true,$createdJob->one_shot);
        $this->assertEquals($providers,$createdJob->providers()->get()->pluck('id')->toArray());

    }

    public function test_teacher_cannot_create_an_invalid_job()
    {

        $this->visit(route('jobDefinitions.create'))
            ->submitForm(trans('Publish job offer'),
                [
                    'name'=>'lol',
                ]
            )
            ->seePageIs('/jobDefinitions/create')
            ->seeText('erreur');

    }

}
