<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionV1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::transaction(function () {

            ##
            #create permissions
            #

            #JobDefinition related permissions
            Permission::create(['name' => 'jobs.create']);
            $permision_job_view = Permission::create(['name' => 'jobs.view']); //assumes view all
            Permission::create(['name' => 'jobs.edit']);
            Permission::create(['name' => 'jobs.trash']);
            Permission::create(['name' => 'jobs.restore']);
            $permission_job_apply = Permission::create(['name' => 'jobs-apply']);//custom for separating apply from students
            //Permission::create(['name' => 'jobs.admin']); //can do on all items (not only his) [not used, as wildcard jobs...]
            $permission_jobs = Permission::create(['name' => 'jobs']);
            $permission_jobs_for_teachers = Permission::create(['name' => 'jobs.create,view,edit,trash,restore']);

            #Contracts
            $permission_contract_create = Permission::create(['name' => 'contracts.create']);
            $permission_contracts_view = Permission::create(['name' => 'contracts.view']); //assumes view all
            Permission::create(['name' => 'contracts.edit']);
            Permission::create(['name' => 'contracts.trash']);
            Permission::create(['name' => 'contracts.restore']);
            //Permission::create(['name' => 'contracts.admin']); //can do on all items (not only his) [duplicate with jobs wildcard]
            $permission_contracts = Permission::create(['name' => 'contracts']);

            //tools list
            $permission_tools_for_teachers = Permission::create(['name' => 'tools.teacher']);

            ##
            #create roles and assign created permissions
            ##

            $eleve = Role::create(['name' => RoleName::STUDENT])
                ->givePermissionTo(
                    $permision_job_view,
                    $permission_job_apply
                );

            // this can be done as separate statements
            $prof = Role::create(['name' => RoleName::TEACHER])
                ->givePermissionTo(
                    $permission_jobs_for_teachers,
                    $permission_tools_for_teachers
                );

            //MP/Doyen, ...
            $mp = Role::create(['name' => RoleName::PRINCIPAL])
                ->givePermissionTo(
                    $permission_jobs,
                    $permission_contracts
                );

            $mp = Role::create(['name' => RoleName::DEAN]);
                //->givePermissionTo();

            #Super Admin
            Role::create(['name' => RoleName::ADMIN]);
        #should be handled by AuthServiceProvider
        #$role->givePermissionTo(Permission::all());
        });


    }
}
