<?php

beforeEach(function (){
    /* @var $this \Tests\TestCase */
    $this->beforeApplicationDestroyed(function(){
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    });
});

test('Call optimize', function () {
    /* @var $response \Illuminate\Testing\TestResponse */
    $response = $this->get('deploy/optimize');

    $response->assertStatus(200);
    $response->assertSeeText('Configuration cached successfully!\nRoute cache cleared!\nRoutes cached successfully!\nFiles cached successfully!');

});
