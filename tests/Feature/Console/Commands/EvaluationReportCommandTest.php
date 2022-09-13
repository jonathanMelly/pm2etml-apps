<?php

use App\Models\Contract;
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
    \App\Models\WorkerContractEvaluationLog::get()->each(function($el){$el->reported_at=null;$el->save();});

    //$logCount = \App\Models\WorkerContractEvaluationLog::count();
    $workersContracts = \App\Models\WorkerContract::get()->take(10);
    $this->assertGreaterThan(0,$workersContracts->count());

    $clients=[];
    foreach($workersContracts as $contract)
    {
        foreach ($contract->contract()->first()->clients()->get() as $client)
        {
            if(!in_array($client->email,$clients))
            {
                $clients[]=$client->email;
            }
        }

        /* @var $contract Contract */
        if($contract->alreadyEvaluated())
        {
            $contract->evaluate(!$contract->success);
        }
        else
        {
            $contract->evaluate(false,'missed');
        }
    }

    $this->artisan('mail:evaluation')->assertExitCode(0);

    // Assert that a mailable was sent...
    Mail::assertSent(\App\Mail\EvaluationChanged::class,sizeof($clients));

});
