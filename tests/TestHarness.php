<?php

namespace Tests;

use App\Constants\RoleName;
use App\Models\AcademicPeriod;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
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

    public function CreateUser(bool $be=true, string... $roles)
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
}
