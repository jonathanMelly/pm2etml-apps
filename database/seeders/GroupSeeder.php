<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Group;
use App\Models\GroupName;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        //Group names
        foreach (['cin1a'=>1,'cin2a'=>2,'cin1b'=>1,'cin2b'=>2,'cid3a'=>3,'cin4b'=>4,'fin1'=>1,'fin2'=>3,'msig'=>1,'min1'=>1,'min2'=>2,'min3'=>3,'min4'=>4] as $group=>$year)
        {
            GroupName::create([
                'name'=> $group,
                'year' =>$year
            ]);
        }

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
