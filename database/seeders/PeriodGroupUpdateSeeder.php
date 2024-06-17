<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Database\QueryException;
use Illuminate\Database\Seeder;

class PeriodGroupUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periodId = AcademicPeriod::current();
        GroupMember::query()
            ->with('group.academicPeriod')
            ->with('group.groupName')
            //->whereDoesntHave('group.academicPeriod',fn($q)=>$q->whereId($periodId))
            ->each(function (GroupMember $gm) use ($periodId) {
                try {
                    $sameGroupForCurrenrtPeriod = Group::query()
                        ->where('group_name_id', '=', $gm->group->groupName->id)
                        ->where('academic_period_id', '=', $periodId)
                        ->firstOrCreate();

                    GroupMember::firstOrCreate(['group_id' => $sameGroupForCurrenrtPeriod->id, 'user_id' => $gm->user_id]);
                } catch (QueryException $e) {
                    //
                }

            });
    }
}
