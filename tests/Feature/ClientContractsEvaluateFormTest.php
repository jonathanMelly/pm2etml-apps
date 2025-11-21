<?php

namespace Tests\Feature;

use App\Constants\RemediationStatus;
use App\Constants\RoleName;
use App\Exports\EvaluationResult;
use App\Models\AcademicPeriod;
use App\Models\ContractEvaluationAttachment;
use App\Models\User;
use App\Models\WorkerContract;
use App\Models\WorkerContractEvaluationLog;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\ContractSeeder;
use Database\Seeders\JobSeeder;
use Database\Seeders\UserV1Seeder;
use Illuminate\Http\UploadedFile;
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
        $this->assertEquals(WorkerContract::whereId($wkIds[0])->firstOrFail()->isSuccess(), true);
        $this->assertEquals(WorkerContract::whereId($wkIds[1])->firstOrFail()->isSuccess(), false);
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
            $wc->evaluation_result='na';
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

    public function test_teacher_can_upload_pdf_attachment_for_contract_evaluation_and_both_teacher_and_student_see_in_dashboard(): void
    {
        $contractsCount = 1;
        $clientAndJob = $this->createClientAndJob($contractsCount);
        $this->teacher = $clientAndJob['client'];
        $student = $clientAndJob['workerContracts'][0]->groupMember->user;

        $contract = $this->teacher->contractsAsAClientForJob($clientAndJob['job'], AcademicPeriod::current())
            ->take($contractsCount)
            ->first();

        $wkIds = WorkerContract::query()->where('contract_id', $contract->id)->pluck('id')->toArray();
        $workerContract = WorkerContract::query()->where('contract_id', $contract->id)->first();

        // Upload PDF attachment before evaluation
        $filename = 'evaluation-support.pdf';
        $file = UploadedFile::fake()->create($filename, 100, 'application/pdf');

        $response = $this->call('POST', 'contract-evaluation-attachment', [
            'worker_contract_id' => $workerContract->id,
            '_token' => csrf_token(),
        ], [], [
            'file' => $file
        ]);

        // Verify upload response
        $response->assertStatus(200);
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($filename, $responseData['name']);

        // Now evaluate the contract as teacher (this will finalize attachments)
        $this->visit('/contracts/evaluate/'.(implode(',', $wkIds)))
            ->submitForm(trans('Save evaluation results'), [
                'workersContracts' => $wkIds,
                'success-'.$wkIds[0] => 'true',
            ])
            ->seePageIs('/dashboard');

        // Verify attachment was created and linked to worker contract
        $attachment = ContractEvaluationAttachment::find($responseData['id']);
        $this->assertNotNull($attachment);
        $this->assertEquals($workerContract->id, $attachment->attachable_id);
        $this->assertEquals(\App\Constants\MorphTargets::MORPH2_WORKER_CONTRACT, $attachment->attachable_type);

        // Refresh the attachment to get updated storage path after evaluation finalization
        $attachment->refresh();
        $this->assertFileExists(uploadDisk()->path($attachment->storage_path));
        $this->assertStringNotContainsString('pending', $attachment->storage_path);

        // Test that attachment filename appears on teacher dashboard
        $this->visit('/dashboard')
            ->see($filename); // Should show the attachment filename

        // Test that attachment filename also appears on student dashboard
        $this->be($student);
        $this->visit('/dashboard')
            ->see($filename); // Student should also see the attachment filename
    }

    public function testDummy(): void
    {
        $this->createClientAndJob(1);
        $logCount = WorkerContractEvaluationLog::query()->count();
        $c = WorkerContract::query()->firstOrFail();
        $c->evaluate(true);
        $this->assertEquals(WorkerContract::query()->firstOrFail()->fresh()->isSuccess(), true);
        //sleep(3);
        $this->assertEquals($logCount + 1, WorkerContractEvaluationLog::query()->count());

    }
}
