<?php

namespace Tests\Feature;

use App\Constants\RemediationStatus;
use App\Constants\RoleName;
use App\Models\AcademicPeriod;
use App\Models\User;
use App\Models\WorkerContract;
use App\Models\WorkerContractEvaluationLog;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\ContractSeeder;
use Database\Seeders\JobSeeder;
use Database\Seeders\UserV1Seeder;
use Tests\BrowserKitTestCase;

class ClientContractsEvaluateFormTest extends BrowserKitTestCase
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

    public function test_teacher_can_evaluate_2_contracts_1okAnd1Ko(): void
    {
        $contractsCount = 2;

        $clientAndJob = $this->createClientAndJob($contractsCount);

        $this->teacher = $clientAndJob['client'];

        $contractIds = $this->teacher->contractsAsAClientForJob($clientAndJob['job'], AcademicPeriod::current())
            //->whereNull('success_date')
            ->take($contractsCount)
            ->get('id')->pluck('id')->toArray();

        $wkIds = WorkerContract::query()->whereIn('contract_id', $contractIds)->pluck('id')->toArray();

        $comment = 'doit chercher par lui-meme 15 minutes avant de demander de l’aide';

        $logCount = WorkerContractEvaluationLog::query()->count();

        $this->visit('/contracts/evaluate/'.(implode(',', $wkIds)))
            //->submitForm(__('Confirm'),['password'=>config('auth.fake_password')])
            ->submitForm(trans('Save evaluation results'), [
                'workersContracts' => $wkIds,
                'success-'.$wkIds[0] => 'true',
                'success-'.$wkIds[1] => 'false',
                'comment-'.$wkIds[1] => $comment,

            ])
            ->seeText($contractsCount.' contrats mis à jour')
            ->see($comment)
            ->seePageIs('/dashboard');

        //Check trigger
        $this->assertEquals($logCount + count($contractIds), WorkerContractEvaluationLog::query()->count());

        //check data
        $this->assertEquals(WorkerContract::whereId($wkIds[0])->firstOrFail()->success, true);
        $this->assertEquals(WorkerContract::whereId($wkIds[1])->firstOrFail()->success, false);
        $this->assertEquals(WorkerContract::whereId($wkIds[1])->firstOrFail()->success_comment, $comment);
    }

    public function test_teacher_validates_remediation_request(): void
    {
        $contractsCount = 1;

        $clientAndJob = $this->createClientAndJob($contractsCount);

        $this->teacher = $clientAndJob['client'];

        $contractIds = $this->teacher->contractsAsAClientForJob($clientAndJob['job'], AcademicPeriod::current())
            //->whereNull('success_date')
            ->take($contractsCount)
            ->get('id')->pluck('id')->toArray();

        $wks = WorkerContract::query()->whereIn('contract_id', $contractIds)->get();

        //Make contracts fail and asked for remediation
        $wks->each(function (WorkerContract $wc) {
            $wc->success=0;
            $wc->success_date = now();
            $wc->remediation_status=RemediationStatus::ASKED_BY_WORKER;
            $wc->save();
        });

        //check that it can be remediable
        $this->visit('/dashboard')
            ->see("Demande de remédiation")
            ->submitForm(trans('Yes'), [
                'remediation-accept' => 1,
            ])
            ->seePageIs('/dashboard')
            ->seeText($contractsCount.' contrat mis à jour')
            ->see("Remédiation");

    }

    public function testDummy(): void
    {
        $this->createClientAndJob(1);
        $logCount = WorkerContractEvaluationLog::query()->count();
        $c = WorkerContract::query()->firstOrFail();
        $c->evaluate(true);
        $this->assertEquals(WorkerContract::query()->firstOrFail()->fresh()->success, true);
        //sleep(3);
        $this->assertEquals($logCount + 1, WorkerContractEvaluationLog::query()->count());

    }
}
