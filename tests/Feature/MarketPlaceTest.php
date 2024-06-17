<?php

test('Filter by priority and provider and xp_years', function () {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */
    /* @var $job1 \App\Models\JobDefinition */

    ['client' => $client1,'job' => $job1] = $this->createClientAndJob(0);
    $job1->title = 'mandatory-job-title';
    $job1->priority = \App\Enums\JobPriority::MANDATORY;
    $job1->required_xp_years = 2;
    $job1->save();

    ['client' => $client2,'job' => $job2] = $this->createClientAndJob(0);
    $job2->title = 'free-job-title';
    $job2->priority = \App\Enums\JobPriority::FREE;
    $job2->save();

    //WHEN
    $response = $this->get('/marketplace?provider='.$client1->id
        .'&priority='.$job1->priority->value
        .'&required_xp_years='.$job1->required_xp_years);
    $response2 = $this->get(route('marketplace', ['provider' => $client2->id]));

    //THEN
    $response->assertStatus(200);
    $response->assertSeeText($job1->title);
    $response->assertDontSeeText($job2->title);

    $response2->assertStatus(200);
    $response2->assertDontSeeText($job1->title);
    $response2->assertSeeText($job2->title);
});

test('Filter by sizes', function ($periods, $sizeStr) {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */
    /* @var $job1 \App\Models\JobDefinition */

    ['client' => $client1,'job' => $job1] = $this->createClientAndJob(0);
    $job1->allocated_time = $periods;
    $job1->save();

    //WHEN
    $response = $this->get(route('marketplace', ['size' => $sizeStr]));

    //THEN
    $response->assertStatus(200);
    $response->assertSeeText($job1->title);

})
    ->with([
        [30, 'sm'],
        [90, 'md'],
        [120, 'lg'],
        [89, 'sm'],
        [119, 'md'],
        [150, 'lg'],
    ]);

test('Filter by fulltext on provider', function ($field) {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */
    /* @var $job1 \App\Models\JobDefinition */
    /* @var $client1 \App\Models\User */

    ['client' => $client1,'job' => $job1] = $this->createClientAndJob(0);
    $client1->setAttribute($field, 'hello match bye');
    $client1->save();

    //WHEN
    $response = $this->get(route('marketplace', ['fulltext' => 'match']));
    $responseBad = $this->get(route('marketplace', ['fulltext' => 'natch']));

    //THEN
    $response->assertStatus(200);
    $response->assertSeeText($job1->title);
    $responseBad->assertDontSeeText($job1->title);

})->with([
    'firstname', 'lastname',
]);

test('Filter by fulltext on job', function ($field) {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */
    /* @var $job1 \App\Models\JobDefinition */
    /* @var $client1 \App\Models\User */

    ['client' => $client1,'job' => $job1] = $this->createClientAndJob(0);
    $job1->setAttribute($field, 'hello match bye');
    $job1->save();

    //WHEN
    $response = $this->get(route('marketplace', ['fulltext' => 'match']));
    $responseBad = $this->get(route('marketplace', ['fulltext' => 'natch']));

    //THEN
    $response->assertStatus(200);
    $response->assertSeeText($job1->title);
    $responseBad->assertDontSeeText($job1->title);

})->with([
    'title', 'description',
]);
