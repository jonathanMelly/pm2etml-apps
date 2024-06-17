<?php

use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\ContractSeeder;
use Database\Seeders\JobSeeder;
use Database\Seeders\UserV1Seeder;

test('Evaluation changed email contains relevant informations', function () {

    $this->multiSeed(
        AcademicPeriodSeeder::class,
        UserV1Seeder::class,
        JobSeeder::class,
        ContractSeeder::class);

    $contract = \App\Models\Contract::firstOrFail();
    $gm = $contract->workers()->firstOrFail();
    $group = $gm->group()->first()->groupName()->first()->name;
    $worker = $gm->user()->first()->getFirstnameL();
    $job = $contract->jobDefinition()->first()->name;

    //be sure to have a log
    $contract->workersContracts()->firstOrFail()->evaluate(true);
    $log = \App\Models\WorkerContractEvaluationLog::query()->latest()->firstOrFail();

    $informations[] = [
        'group' => $group,
        'name' => $worker,
        'log' => $log,
        'job' => $job,
    ];

    $mailable = new \App\Mail\EvaluationChanged($informations);

    $mailable->assertSeeInHtml($group);
    $mailable->assertSeeInOrderInHtml([$group, $worker, $job]);

    $mailable->assertSeeInText($group);
    $mailable->assertSeeInOrderInText([$group, $worker, $job]);
});
