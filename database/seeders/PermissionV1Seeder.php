<?php

namespace Database\Seeders;

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

            // create permissions
            #Job related permissions
            Permission::create(['name' => 'jobs.create']);
            $pjview = Permission::create(['name' => 'jobs.view']); //assumes view all
            Permission::create(['name' => 'jobs.edit']);
            Permission::create(['name' => 'jobs.trash']);
            Permission::create(['name' => 'jobs.restore']);
            Permission::create(['name' => 'jobs.admin']); //can do on all items (not only his)
            $pjobs = Permission::create(['name' => 'jobs']);

            //tools list
            $ptoolsteacher = Permission::create(['name' => 'tools.teacher']);

            //jobs for teachers
            $pprof = Permission::create(['name' => 'jobs.create,view,edit,trash,restore']);

            //Apply for a job
            $papply = Permission::create(['name' => 'jobs-apply']);

            // create roles and assign created permissions

            // this can be done as separate statements
            $prof = Role::create(['name' => 'prof'])
                ->givePermissionTo([$pprof,$ptoolsteacher]);

            //MP/Doyen, ...
            $mp = Role::create(['name' => 'mp'])
                ->givePermissionTo($pjobs);

            $mp = Role::create(['name' => 'doyen']);
                //->givePermissionTo();

            // or may be done by chaining
            $eleve = Role::create(['name' => 'eleve'])
                ->givePermissionTo([$pjview, $papply]);

            #Super Admin
            Role::create(['name' => 'root']);
        #should be handled by AuthServiceProvider
        #$role->givePermissionTo(Permission::all());
        });


    }
}
