<?php

namespace Database\Seeders;

use App\Constants\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionV3Seeder extends Seeder
{
   public function run()
   {
      // Création ou récupération de la permission
      $permission = Permission::firstOrCreate(['name' => 'evaluation.storeEvaluation']);

      // Attribution de la permission aux rôles définis
      $teacherRole = Role::findByName(RoleName::TEACHER); // 'prof'
      $studentRole = Role::findByName(RoleName::STUDENT); // 'eleve'

      $teacherRole->givePermissionTo($permission);
      $studentRole->givePermissionTo($permission);
   }
}
