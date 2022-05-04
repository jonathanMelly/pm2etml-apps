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

class ClientContractsEvaluateFormTest extends BrowserKitTestCase
{

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

        });
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_teacher_can_evaluate_1_contract()
    {
        //TODO guarantee enough data to avoid random test crash

        /* @var $job JobDefinition */
        $localJob = $this->teacher->getJobDefinitionsWithActiveContracts(AcademicPeriod::current())
            ->firstOrFail();
        $jobId = $localJob->id;
        $contractIds = $this->teacher->contractsAsAClientForJob($localJob)
            //->whereNull('success_date')
            ->take(2)
            ->get('id')->pluck('id')->toArray();


        $this->visit('/contracts/evaluate/'.(implode(',',$contractIds)))
            ->submitForm(trans('Save evaluation results'), [
                'contractsEvaluations' => '{"'.$contractIds[0].'":true,"'.$contractIds[1].'":false}',
            ])
            ->seePageIs('/dashboard')
            ->seeText('2 contrats mis à jour');

    }

}
