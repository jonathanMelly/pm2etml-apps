<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\JobDefinition;
use App\Models\User;
use Tests\BrowserKitTestCase;

class ClientContractsDeleteFormTest extends BrowserKitTestCase
{
    /* @var $teacher User */
    protected User $teacher;

    protected JobDefinition $job;

    protected int $contractsCount = 2;

    private string $formPage;

    /**
     * @before
     *
     * @return void
     */
    public function setUpLocal()
    {
        $this->afterApplicationCreated(function () {
            $clientAndJob = $this->createClientAndJob($this->contractsCount);

            $this->teacher = $clientAndJob['client'];
            $this->job = $clientAndJob['job'];

            $this->formPage = '/dashboard';
        });
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_teacher_can_delete_two_contracts()
    {

        $jobId = $this->job->id;
        $contractIds = $this->teacher->contractsAsAClientForJob($this->job, AcademicPeriod::current())->take($this->contractsCount)
            ->get('id')->pluck('id')->toArray();

        $this->assertEquals($this->contractsCount, count($contractIds));

        $contractFields = 'job-'.$jobId.'-contracts';

        $this->visit($this->formPage)
            ->submitForm("job-{$jobId}-form-input-for-test", [
                $contractFields => $contractIds,
                'job_id' => $jobId,
            ])
            ->seePageIs('/dashboard')
            ->seeText($this->contractsCount.' contrats supprimés');

    }
}
