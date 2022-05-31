<?php

namespace Tests\Feature;

use App\Constants\RoleName;
use App\Models\User;
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
     * @return void
     */
    public function setUpLocal()
    {
        $this->afterApplicationCreated(function() {

            $this->multiSeed(
                AcademicPeriodSeeder::class,
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
    public function test_teacher_can_evaluate_2_contracts_1okAnd1Ko()
    {
        $contractsCount=2;

        $clientAndJob = $this->createClientAndJob($contractsCount);

        $this->teacher=$clientAndJob['client'];

        $contractIds = $this->teacher->contractsAsAClientForJob($clientAndJob['job'])
            //->whereNull('success_date')
            ->take($contractsCount)
            ->get('id')->pluck('id')->toArray();

        $comment = "doit chercher par lui-même 15 minutes avant de demander de l’aide";

        $this->visit('/contracts/evaluate/'.(implode(',',$contractIds)))
            ->submitForm(trans('Save evaluation results'), [
                'contracts' => $contractIds,
                'success-'.$contractIds[0]=>'true',
                'success-'.$contractIds[1]=>'false',
                'comment-'.$contractIds[1]=>$comment,

            ])
            ->seeText($contractsCount.' contrats mis à jour')
            ->see($comment)
            ->seePageIs('/dashboard');

    }

}
