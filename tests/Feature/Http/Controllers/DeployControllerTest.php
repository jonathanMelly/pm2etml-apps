<?php

beforeEach(function (){
    /* @var $this \Tests\TestCase */
    $this->beforeApplicationDestroyed(function(){
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    });
});

test('Call optimize', function () {
    /* @var $this \Tests\TestCase */
    /* @var $response \Illuminate\Testing\TestResponse */
    $response = $this->get('deploy/optimize');

    $response->assertStatus(200);
    $output = $response->getContent();

    $this->assertEquals(\App\Http\Controllers\DeployController::SUCCESS_MESSAGE,$output);

});
