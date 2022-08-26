<?php

namespace Tests;

use App\Constants\DiskNames;
use App\Constants\RoleName;
use App\Models\AcademicPeriod;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupName;
use App\Models\JobDefinition;
use App\Models\JobDefinitionDocAttachment;
use App\Models\JobDefinitionMainImageAttachment;
use App\Models\Skill;
use App\Models\User;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\PermissionV1Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait TestHarness
{
    /**
     * @before
     * @return void
     */
    public function setupDbDataAndStorage()
    {
        $this->afterApplicationCreated(function(){
            $this->seed(PermissionV1Seeder::class);
            Storage::fake('local');
            Storage::fake('upload');
        });
    }

    public function multiSeed(...$classes)
    {
        foreach ($classes as $class)
        {
            $this->seed($class);
        }
    }

    public function createUser(bool $be=true, string... $roles)
    {
        $user = User::factory()->create();
        $user->syncRoles($roles);

        //attach user to a random group
        if(collect($roles)->contains(RoleName::STUDENT))
        {
            $gm=GroupMember::make();
            $gm->user_id = $user->id;
            $gm->group_id = Group::where('academic_period_id','=',AcademicPeriod::current())
                ->firstOrFail()->id;
            $gm->save();
        }

        if($be)
        {
            $this->be($user);
        }

        return $user;
    }

    public function createAttachment(string $name='storage.pdf',bool $image=false,bool $save=true)
    {
        $path = attachmentPathInUploadDisk($name,true);
        $randomSize = rand(1,1024*1024*5);
        if($image)
        {
            $file = UploadedFile::fake()->image('temp',50,49);
            $file->store($path,DiskNames::UPLOAD);

            $result =JobDefinitionMainImageAttachment::make(['name' => 'ori.png',
                'storage_path' => $path,'size'=>$file->getSize()]);
        }
        else
        {
            $file = UploadedFile::fake()->createWithContent($name.'-tmp','test content');
            $file->store($path,DiskNames::UPLOAD);

            $result = JobDefinitionDocAttachment::make(['name' => $name,
                'storage_path' => $path,'size'=>$file->getSize()]);
        }

        if($save)
        {
            $result->save();
        }

        return $result;
    }

    public function createClientAndJob(int $contractsCount=0):array
    {
        AcademicPeriod::create([
            'start' => today()->subWeek(),
            'end' => today()->addWeek()
        ]);

        Group::create([
            'academic_period_id' => AcademicPeriod::current(),
            'group_name_id' => GroupName::create([
                'name'=> 'test',
                'year' =>today()->year
            ])->id
        ]) ;

        $client = $this->createUser(roles:RoleName::TEACHER);

        $job=JobDefinition::factory()
            ->afterMaking(function (JobDefinition $job) {
                $job->published_date = today()->subWeek();
            })
            ->afterCreating(function (JobDefinition $job) use ($client) {
                $job->providers()->attach($client->id);

                $job->image()
                    ->create($this
                        ->createAttachment(name:'image.png',image:true,save:false)->attributesToArray()
                    );

                $job->skills()->attach(Skill::firstOrCreateFromString('tgroup:tskill'));
            })
            ->count(1)->create()->firstOrFail()->/*without it, default values set in DB are not loaded...*/fresh();

        $employees=[];
        for ($i=0;$i<$contractsCount;$i++)
        {
            $employees[]=$this->createUser(false,RoleName::STUDENT);
        }

        //Be sure to have 2 contracts for the first job
        foreach ($employees as $employee) {

            $contract = \App\Models\Contract::make([
                'start' => today()->subDay(),
                'end' => today()->addDay()]);
            $contract->job_definition_id = $job->id;
            $contract->save();

            $contract->clients()->attach($client->id);
            $contract->workers()->attach($employee->groupMember()->id);

        }

        return ['client'=>$client,'job'=>$job];
    }
}
