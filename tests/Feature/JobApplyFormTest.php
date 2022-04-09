<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\JobDefinition;
use Database\Seeders\PermissionV1Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\BrowserKitTestCase;
use Tests\TestCase;
use function PHPUnit\Framework\assertNotEmpty;

class JobApplyFormTest extends BrowserKitTestCase
{
    protected JobDefinition $job;
    protected $formPage;
    protected $teacher;

    /**
     * @before
     * @return void
     */
    public function setUpLocal()
    {
        $this->afterApplicationCreated(function() {
            $this->CreateUser(roles: RoleName::STUDENT);
            $this->teacher = $this->CreateUser(false,'prof');

            $this->job = JobDefinition::factory()
                ->afterCreating(function(JobDefinition $jobD)
                {
                    $jobD->providers()->attach($this->teacher->id);
                })
                ->create();

            $this->formPage="/jobs-apply/".$this->job->id;
        });
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_can_apply_for_a_job_and_only_once()
    {

        //ok
        $this->visit($this->formPage)
            ->type(now(), 'start_date')
            ->type(now(), 'end_date')
            ->type($this->job->id, 'job_definition_id')
            ->select($this->teacher->id, 'client')
            ->press(__('Apply'))
            ->seePageIs('/dashboard')
            ->seeText(__('Congrats, you have been hired for the job'))
        ;

        //ko (already registered)
        $this->visit($this->formPage)
            ->type(now(), 'start_date')
            ->type(now(), 'end_date')
            ->type($this->job->id, 'job_definition_id')
            ->select($this->teacher->id, 'client')
            ->press(__('Apply'))
            ->seePageIs($this->formPage)
            ->seeText(__('You already have/had a contract for this job'))
        ;
    }

    public function test_user_cannot_apply_for_a_job_with_unregistered_providers()
    {
        $otherProvider = $this->CreateUser(false,'prof');


        //ko (already registered)
        $temp = $this->visit($this->formPage);

        //As the crawler checks for valid options, the easiest way to test this scenario
        //is to change the provider meanwhile the form is sent...
        $this->job->providers()->sync($otherProvider->id);

        $temp
            ->type(now(), 'start_date')
            ->type(now(), 'end_date')
            ->type($this->job->id, 'job_definition_id')
            ->select($this->teacher->id, 'client')
            ->press(__('Apply'))
            ->seePageIs($this->formPage)
            ->seeText(__('Invalid client (only valid providers are allowed)'))
        ;
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_cannot_apply_with_end_date_in_the_past()
    {
        $startDate = now();
        $endDate = now()->subDay();
        self::assertNotEquals($startDate,$endDate);

        //ok
        $this->visit($this->formPage)
            ->type($startDate, 'start_date')
            ->type($endDate, 'end_date')
            ->type($this->job->id, 'job_definition_id')
            ->select($this->teacher->id, 'client')
            ->press(__('Apply'))
            ->seePageIs($this->formPage)
            ->seeText(__('validation.after_or_equal', ['attribute' => 'end date', 'date' => 'start date']))
        ;
    }
}
