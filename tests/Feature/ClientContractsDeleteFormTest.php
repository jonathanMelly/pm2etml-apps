<?php

namespace Tests\Feature;

use App\Constants\RoleName;
use App\Models\AcademicPeriod;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\JobDefinition;
use App\Models\User;
use Database\Seeders\ContractSeeder;
use Database\Seeders\GroupSeeder;
use Database\Seeders\JobSeeder;
use Database\Seeders\PermissionV1Seeder;
use Database\Seeders\UserV1Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\BrowserKitTestCase;

class ClientContractsDeleteFormTest extends BrowserKitTestCase
{
    protected JobDefinition $job;
    protected $formPage;

    /* @var $teacher User */
    protected User $teacher;

    /**
     * @before
     * @return void
     */
    public function setUpLocal()
    {
        $this->afterApplicationCreated(function() {

            $this->multiSeed(
                UserV1Seeder::class,
                JobSeeder::class,
                ContractSeeder::class);


            $this->teacher = User::role(RoleName::TEACHER)->firstOrFail();
            $this->be($this->teacher);

            $this->formPage="/dashboard";
        });
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_teacher_can_delete_two_contracts()
    {
        /* @var $job JobDefinition */
        $job = $this->teacher->getJobDefinitionsWithActiveContracts(AcademicPeriod::current())->firstOrFail();
        $jobId = $job->id;
        $contractIds = $this->teacher->contractsAsAClientForJob($job)->take(2)
            ->get('id')->pluck('id')->toArray();

        $contractFields = 'job-'.$jobId.'-contracts';

        $this->visit($this->formPage)
            ->submitForm(trans('Yes'), [
                $contractFields => $contractIds,
                'job_id' => $jobId
            ])
            ->seePageIs('/dashboard')
            ->seeText('2 contrats supprimés');


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
