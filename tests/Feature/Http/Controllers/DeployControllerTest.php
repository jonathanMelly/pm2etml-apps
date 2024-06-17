<?php

beforeEach(function () {
    /* @var $this \Tests\TestCase */
    $this->beforeApplicationDestroyed(function () {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        //\Illuminate\Support\Facades\Artisan::call('up');
    });
});

test('Call optimize with site up as admin', function () {
    /* @var $this \Tests\TestCase */
    /* @var $response \Illuminate\Testing\TestResponse */

    $this->createUser(true, 'root');

    $response = $this->get('deploy/optimize');

    $response->assertStatus(200);
    $output = $response->getContent();

    $this->assertEquals(\App\Http\Controllers\DeployController::SUCCESS_MESSAGE, $output);

});

test('Call optimize with site up as not an admin', function () {
    /* @var $this \Tests\TestCase */
    /* @var $response \Illuminate\Testing\TestResponse */

    $this->createUser(false, 'root');

    $response = $this->get('deploy/optimize');

    $response->assertStatus(403);

});
