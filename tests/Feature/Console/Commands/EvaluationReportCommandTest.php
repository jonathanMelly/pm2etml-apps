<?php

use App\Models\Contract;
use App\Models\WorkerContract;
use Database\Seeders\AcademicPeriodSeeder;
use Database\Seeders\ContractSeeder;
use Database\Seeders\JobSeeder;
use Database\Seeders\UserV1Seeder;

test('A mail is sent for each client having updated evaluations', function () {
    /* @var $this \Tests\TestCase */

    Mail::fake();

    $this->multiSeed(
        AcademicPeriodSeeder::class,
        UserV1Seeder::class,
        JobSeeder::class,
        ContractSeeder::class);

    //cleanup for test later (a bit dirty but easier than building all data instead of using seeder...
    \App\Models\WorkerContractEvaluationLog::get()->each(function ($el) {
        $el->reported_at = null;
        $el->save();
    });
    $initialReports = \App\Models\WorkerContractEvaluationLog::whereNull('reported_at')->get()
        ->transform(fn ($wcel) => $wcel->contract);

    //$logCount = \App\Models\WorkerContractEvaluationLog::count();
    $contracts = WorkerContract::get()->take(10)->transform(fn ($wc) => $wc->contract)->merge($initialReports);
    $this->assertGreaterThan(3, $contracts->count());

    //Simulate a user quitting while still having evaluated contracts to report...
    /* @var $c Contract */
    $c = $contracts[0];
    $gm = $c->workersContracts()->firstOrFail()->groupMember()->firstOrFail();
    $gm->user->delete();
    $gm->delete();//simulate a student that quits (caused issues in the past)

    $clients = [];
    $i=0;
    foreach ($contracts as $contract) {
        /* @var $contract Contract */
        foreach ($contract->clients as $client) {
            if (! in_array($client->email, $clients)) {
                $clients[] = $client->email;
            }
        }

        $success = random_int(1, 2) == 1;
        /* @var $wc WorkerContract */
        $wc = $contract->workersContracts[0];

        $wc->evaluate($success, $success ? 'congrats' : 'missed');
        $this->assertTrue($wc->alreadyEvaluated(), 'Contract should have been marked as evaluated');
    }

    //TODO remove when confirmed that test is ok (randomness of data...)
    var_dump($clients);
    ob_flush();

    $this->artisan('mail:evaluation')->assertExitCode(0);

    // Assert that a mailable was sent...
    Mail::assertSent(\App\Mail\EvaluationChanged::class, count($clients));

});
