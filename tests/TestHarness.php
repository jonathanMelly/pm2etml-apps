<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\PermissionV1Seeder;

trait TestHarness
{
    /**
     * @before
     * @return void
     */
    public function SetupDbData()
    {
        $this->afterApplicationCreated(function(){
            $this->seed(PermissionV1Seeder::class);
        });
    }

    public function CreateUser(bool $be=true, string... $roles)
    {
        $eleve = User::factory()->create();
        $eleve->syncRoles($roles);

        if($be)
        {
            $this->be($eleve);
        }

        return $eleve;
    }
}
