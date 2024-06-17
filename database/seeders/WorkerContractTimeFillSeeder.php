<?php

namespace Database\Seeders;

use App\Models\WorkerContract;
use Illuminate\Database\Seeder;

//Used to fill empty data during migration phase...(mainly to ease tests...)
class WorkerContractTimeFillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Fill new fields of existing contracts with project values
        WorkerContract::with('contract.jobDefinition')->each(function ($wc) {
            /* @var $wc WorkerContract */
            if ($wc->allocated_time === null && $wc->contract !== null) {
                $job = $wc->contract->jobDefinition;
                $wc->allocated_time = $job->allocated_time;
                $wc->allocated_time_unit = $job->allocated_time_unit;
                $wc->name = '';

                $wc->save();
            }
        });
    }
}
