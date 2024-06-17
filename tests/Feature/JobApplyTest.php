<?php

use App\Models\JobDefinition;

test('Teacher cannot apply for a job', function () {

    $this->seed(\Database\Seeders\AcademicPeriodSeeder::class);

    $this->CreateUser(roles: 'prof');
    $prof = $this->CreateUser(false, 'prof');

    $_this = $this;
    $job = JobDefinition::factory()
        ->afterCreating(function (JobDefinition $job) use ($prof, $_this) {
            $job->providers()->attach($prof->id);
            $_this->createAttachment('storage.png', image: true)->attachJobDefinition($job);
        })
        ->create();

    //No form (readonly view)
    $response = $this->get('/jobs-apply/'.$job->id);
    $response->assertStatus(200);
    $response->assertDontSeeText('<form>');

    //Cannot apply
    $this->post('/contracts')->assertStatus(403);
});
