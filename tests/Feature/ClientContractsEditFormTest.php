<?php

namespace Tests\Feature;

use App\Constants\RoleName;
use App\Models\User;
use App\Models\WorkerContract;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\ContractSeeder;
use Database\Seeders\JobSeeder;
use Database\Seeders\UserV1Seeder;
use Tests\BrowserKitTestCase;

class ClientContractsEditFormTest extends BrowserKitTestCase
{
    /* @var $teacher User */
    protected User $teacher;

    /**
     * @before
     *
     * @return void
     */
    public function setUpLocal()
    {
        $this->afterApplicationCreated(function () {

            $this->multiSeed(
                AcademicPeriodSeeder::class,
                UserV1Seeder::class,
                JobSeeder::class,
                ContractSeeder::class);

            $this->teacher = User::role(RoleName::TEACHER)->firstOrFail();
            $this->be($this->teacher);

        });
    }

    public function test_teacher_can_edit_dates_and_periods(): void
    {
        $contractsCount = 2;

        $clientAndJob = $this->createClientAndJob($contractsCount);

        $this->teacher = $clientAndJob['client'];
        $period = \App\Models\AcademicPeriod::current(idOnly: false);

        $contractIds = $this->teacher->contractsAsAClientForJob($clientAndJob['job'], $period->id)
            //->whereNull('success_date')
            ->take($contractsCount)
            ->get('id')->pluck('id')->toArray();

        $wkIds = WorkerContract::query()->whereIn('contract_id', $contractIds)->pluck('id')->toArray();

        $year = $period->start->year;
        $starts = ["$year-10-01", "$year-12-01"];
        $ends = ["$year-11-15", "$year-12-25"];
        $times = ['7', '8'];
        $this->visit('/contracts/bulkEdit/'.(implode(',', $wkIds)))
           // ->submitForm(__('Confirm'),['password'=>config('auth.fake_password')])
            ->submitForm(trans('Save modifications'), [
                'workersContracts' => $wkIds,
                'starts' => $starts,
                'ends' => $ends,
                'allocated_times' => $times,

            ])
            ->seeText($contractsCount.' contrats mis à jour')
            ->seePageIs('/dashboard');

        //Check trigger

        //check data
        foreach ($wkIds as $i => $wkId) {
            $wk = WorkerContract::whereId($wkId)->firstOrFail();
            $this->assertEquals($starts[$i], $wk->contract->start->format(\App\DateFormat::HTML_FORMAT));
            $this->assertEquals($ends[$i], $wk->contract->end->format(\App\DateFormat::HTML_FORMAT));
            $this->assertEquals($times[$i], $wk->getAllocatedTime(\App\Enums\RequiredTimeUnit::PERIOD));
        }

    }

    public function test_teacher_cannot_set_date_out_of_period(): void
    {
        $contractsCount = 1;

        $clientAndJob = $this->createClientAndJob($contractsCount);

        $this->teacher = $clientAndJob['client'];
        $period = \App\Models\AcademicPeriod::current(idOnly: false);

        $contractIds = $this->teacher->contractsAsAClientForJob($clientAndJob['job'], $period->id)
            //->whereNull('success_date')
            ->take($contractsCount)
            ->get('id')->pluck('id')->toArray();

        $wkIds = WorkerContract::query()->whereIn('contract_id', $contractIds)->pluck('id')->toArray();

        $starts = ['1950-10-01'];
        $ends = ['1950-11-15'];
        $url = '/contracts/bulkEdit/'.(implode(',', $wkIds));
        $this->visit($url)
            //->submitForm(__('Confirm'),['password'=>config('auth.fake_password')])
            ->submitForm(trans('Save modifications'), [
                'workersContracts' => $wkIds,
                'starts' => $starts,
                'ends' => $ends,

            ])
            ->seeStatusCode(200)
            ->seePageIs($url)
            ->seeText(__('Dates must be included within current academic period'));

    }
}
