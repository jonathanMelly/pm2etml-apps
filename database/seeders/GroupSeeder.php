<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Group;
use App\Models\GroupName;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Periods
        $currentYear = now()->year;
        for ($i=-5;$i<1;$i++)
        {
            $start = CarbonImmutable::create($currentYear+$i,8,1);
            $end = $start->addYear()->subDay();
            AcademicPeriod::create([
                'start' => $start,
                'end' => $end
            ]);
        }

        //Group names
        foreach (['cin1a','cin2a','cin1b','cin2b','cid3a','cin4b','fin1','fin2','msig'] as $group)
        GroupName::create([
            'name'=> $group
        ]);

        //Groups
        AcademicPeriod::all()->each(function ($academicPeriod)
        {
            GroupName::all()->each(function($groupName) use($academicPeriod)
            {
               Group::create([
                   'academic_period_id' => $academicPeriod->id,
                   'group_name_id' => $groupName->id
               ]) ;
            });
        });

    }
}
