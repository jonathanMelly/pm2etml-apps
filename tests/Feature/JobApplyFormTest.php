<?php

namespace Tests\Feature;

use App\Constants\RoleName;
use App\DateFormat;
use App\Models\JobDefinition;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\GroupSeeder;
use Tests\BrowserKitTestCase;

class JobApplyFormTest extends BrowserKitTestCase
{
    protected JobDefinition $job;

    protected $formPage;

    protected $teacher;

    /**
     * @before
     *
     * @return void
     */
    public function setUpLocal()
    {
        $this->afterApplicationCreated(function () {

            $this->multiSeed(AcademicPeriodSeeder::class, GroupSeeder::class);

            $student = $this->createUser(roles: RoleName::STUDENT);

            $this->teacher = $this->createUser(false, 'prof');

            $this->job = JobDefinition::factory()
                ->afterCreating(function (JobDefinition $jobD) {
                    $jobD->providers()->attach($this->teacher->id);

                    //Image
                    $this->createAttachment('storage.png', image: true)->attachJobDefinition($jobD);
                })
                ->create();

            $this->formPage = '/jobs-apply/'.$this->job->id;
        });
    }

    /**
     * A basic feature test example.
     */
    public function test_user_can_apply_for_a_job_and_only_once(): void
    {
        $date = now()->format(DateFormat::HTML_FORMAT);

        //ok
        $this->visit($this->formPage)
            ->type($date, 'start_date')
            ->type($date, 'end_date')
            ->type($this->job->id, 'job_definition_id')
            ->select($this->teacher->id, 'client-0')
            ->press(__('Apply'))
            ->seePageIs('/dashboard')
            ->seeText(__('New contract successfully registered'));

        //ko (already registered)
        $this->visit($this->formPage)
            ->type($date, 'start_date')
            ->type($date, 'end_date')
            ->type($this->job->id, 'job_definition_id')
            ->select($this->teacher->id, 'client-0')
            ->press(__('Apply'))
            ->seePageIs($this->formPage)
            ->seeText(__('There already is a contract for this job'));
    }

    public function test_user_can_apply_for_a_job_with_any_teacher(): void
    {
        $date = now()->format(DateFormat::HTML_FORMAT);

        $otherProvider = $this->createUser(false, 'prof');

        //ko (already registered)
        $temp = $this->visit($this->formPage);

        //As the crawler checks for valid options, the easiest way to test this scenario
        //is to change the provider meanwhile the form is sent...
        $this->job->providers()->sync($otherProvider->id);

        $temp
            ->type($date, 'start_date')
            ->type($date, 'end_date')
            ->type($this->job->id, 'job_definition_id')
            ->select($this->teacher->id, 'client-0')
            ->press(__('Apply'))
            ->seePageIs('/dashboard')
            ->seeText(__('New contract successfully registered'));
    }

    /**
     * A basic feature test example.
     */
    public function test_user_cannot_apply_with_end_date_in_the_past(): void
    {
        $startDate = now();
        $endDate = now()->subDay();
        self::assertNotEquals($startDate, $endDate);

        //ok
        $this->visit($this->formPage)
            ->type($startDate->format(DateFormat::HTML_FORMAT), 'start_date')
            ->type($endDate->format(DateFormat::HTML_FORMAT), 'end_date')
            ->type($this->job->id, 'job_definition_id')
            ->select($this->teacher->id, 'client-0')
            ->press(__('Apply'))
            ->seePageIs($this->formPage)
            ->seeText(__('validation.after_or_equal', ['attribute' => 'end date', 'date' => 'start date']));
    }
}
