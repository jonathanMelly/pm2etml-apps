<?php

namespace Tests;

use App\Constants\RoleName;
use App\Models\AcademicPeriod;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupName;
use App\Models\JobDefinition;
use App\Models\User;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\PermissionV1Seeder;

trait TestHarness
{
    /**
     * @before
     * @return void
     */
    public function setupDbData()
    {
        $this->afterApplicationCreated(function(){
            $this->seed(PermissionV1Seeder::class);
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

    public function createClientWithContracts(int $contractsCount):array
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

        JobDefinition::factory()
            ->afterMaking(function (JobDefinition $job) {
                $job->image = 'dummy.png';
                $job->published_date = today()->subWeek();
            })
            ->afterCreating(fn ($job) => $job->providers()->attach($client->id))
            ->count(1)->create();

        $job = JobDefinition::firstOrFail();

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
