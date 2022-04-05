<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\JobDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\BrowserKitTestCase;
use Tests\TestCase;

class JobApplyFormTest extends BrowserKitTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_can_apply_for_a_job()
    {
        $this->CreateUser(roles: RoleName::STUDENT);
        $prof = $this->CreateUser(false,'prof');

        $job = JobDefinition::factory()
            ->afterCreating(function(JobDefinition $job) use($prof)
            {
                $job->providers()->attach($prof->id);
            })
            ->create();

        $this->visit("/jobs-apply/".$job->id)
            ->type(now(), 'start_date')
            ->type(now(), 'end_date')
            ->type($job->id, 'job_definition_id')
            ->select($prof->id, 'client')
            ->press(__('Apply'))
            ->seePageIs('/dashboard')
            ->seeText(__('Congrats, you have been hired for the job'))
        ;
    }
}
