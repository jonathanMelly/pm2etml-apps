<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Group;
use App\Models\GroupName;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //Group names
        foreach ([
            'cin1a' => 1, 'cin1b' => 1, 'cin1c' => 1, 'min1' => 1, 'min1a' => 1, 'min1b' => 1, 'fin1' => 1, 'msig' => 1,
            'cin2a' => 2, 'cin2b' => 2, 'min2' => 2, 'cid2a' => 2, 'cid2b' => 2, 'mid2' => 2,
            'cin3a' => 3, 'cin3b' => 3, 'cin3b1' => 3, 'cin3b2' => 3, 'min3' => 3, 'cid3a' => 3, 'mid3' => 3, 'fin2' => 3,
            'cin4a' => 4, 'cin4b' => 4, 'cid4a' => 4, 'cid4b' => 4, 'min4' => 4, 'mid4' => 4] as $group => $year) {
            GroupName::firstOrCreate([
                'name' => $group,
                'year' => $year,
            ]);
        }

        //Groups
        AcademicPeriod::all()->each(function ($academicPeriod) {
            GroupName::all()->each(function ($groupName) use ($academicPeriod) {
                Group::firstOrCreate([
                    'academic_period_id' => $academicPeriod->id,
                    'group_name_id' => $groupName->id,
                ]);
            });
        });

    }
}
