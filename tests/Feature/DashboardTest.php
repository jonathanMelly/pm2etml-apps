<?php

use App\Models\User;
use Database\Seeders\ContractSeeder;
use Database\Seeders\JobSeeder;
use Database\Seeders\UserV1Seeder;
use Illuminate\Testing\TestResponse;

beforeEach(function()
{
    $this->multiseed(\Database\Seeders\AcademicPeriodSeeder::class,UserV1Seeder::class);
});

test('Prof can see FAQ tool and url shortener', function () {
    //Given
    $prof = $this->CreateUser(roles: \App\Constants\RoleName::TEACHER);

    //When
    $response = $this->get('/dashboard');

    //Then
    assertSeeAll($response);

});

test('Root teacher can see FAQ tool and url shortener', function () {
    //Given
    $prof = $this->CreateUser(true,\App\Constants\RoleName::ADMIN,\App\Constants\RoleName::TEACHER);

    //When
    $response = $this->get('/dashboard');

    //Then
    assertSeeAll($response);

});

test('Root only can see FAQ tool and url shortener', function () {
    //Given
    $prof = $this->CreateUser(true,\App\Constants\RoleName::ADMIN);

    //When
    $response = $this->get('/dashboard');

    //Then
    assertSeeAll($response);

});

function assertSeeAll(TestResponse $response)
{
    $response->assertSeeText("dis.section-inf.ch");
    $response->assertSeeText("ici.section-inf.ch");
}

test('Eleve cannot see FAQ tool/url shortener but git', function () {
    //Given
    $eleve = $this->CreateUser(roles: \App\Constants\RoleName::STUDENT);


    //When
    $response = $this->get('/dashboard');

    //Then
    $response->assertDontSeeText("dis.section-inf.ch");
    $response->assertDontSeeText("ici.section-inf.ch");
    $response->assertSeeText("git.section-inf.ch");
});

test('Student see his contracts as a worker', function () {
    //Given
    $eleve = User::role(\App\Constants\RoleName::STUDENT)->firstOrFail();
    $this->be($eleve);
    $this->seed(JobSeeder::class);
    $this->seed(ContractSeeder::class);

    $contracts = $eleve->contractsAsAWorker()->get();
    \PHPUnit\Framework\assertGreaterThan(0,$contracts->count());

    //When
    $response = $this->get('/dashboard');

    //Then
    foreach ($contracts as $contract)
    {
        $response->assertSeeText(Str::words($contract->jobDefinition->title,3));
    }

});

test('Teacher see his contracts as a client', function () {
    //Given
    $this->seed(JobSeeder::class);
    $this->seed(ContractSeeder::class);

    $teacher = User::role(\App\Constants\RoleName::TEACHER)->first();
    $this->be($teacher);


    $jobDefinition = $teacher->getJobDefinitionsWithActiveContracts(\App\Models\AcademicPeriod::current())
        //->where('one_shot','=','false')
        ->firstOrFail();
    $contracts = $teacher->contractsAsAClientForJob($jobDefinition)->get();
    \PHPUnit\Framework\assertGreaterThan(0,$contracts->count());

    //When
    $response = $this->get('/dashboard');

    //Then
    $response->assertSeeText($jobDefinition->title);
    foreach ($contracts as $contract)
    {
        foreach ($contract->workers as $worker)
        {
            $response->assertSeeText($worker->user->getFirstnameL());
        }

    }

});
