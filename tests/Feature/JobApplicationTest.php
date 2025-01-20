<?php
use Database\Seeders\UserV1Seeder;

beforeEach(function () {
    $this->multiseed(\Database\Seeders\AcademicPeriodSeeder::class, UserV1Seeder::class);
});

test('Prof can see JobApplication page with a pending application of a student in 2 jobs...', function () {

    /* @var $this \Tests\TestCase */

    //Given
    ['client' => $client0, 'job' => $job0, 'workerContracts' => $workerContracts0] =
        $this->createClientAndJob(5);
    ['client' => $client, 'job' => $job, 'workerContracts' => $workerContracts] =
        $this->createClientAndJob(5);
    $this->be($client);

    /* @var $wc \App\Models\WorkerContract */
    $wc = $workerContracts[0];
    $wc->application_status = 1;
    $wc->save();

    $wc = $workerContracts0[0];
    $wc->application_status = 1;
    $wc->save();

    //configure job for application mode
    /* @var $job \App\Models\JobDefinition */
    $job->required_xp_years=4;
    $job->by_application = true;
    $job->save();

    $job0->required_xp_years=4;
    $job0->by_application = true;
    $job0->save();

    //When
    $response = $this->get(route('applications'));

    //Then
    $response->assertSeeText(__('Give the job'));
    $response->assertSeeText($job->title);
    $response->assertSeeText($job0->title);
    $response->assertStatus(200);

});
