<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionV2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        \Illuminate\Support\Facades\DB::transaction(function () {

            $p = \Spatie\Permission\Models\Permission::findByName('contracts.edit');
            $r = \Spatie\Permission\Models\Role::findByName(\App\Constants\RoleName::STUDENT);
            $r->givePermissionTo($p);
        });

    }
}
