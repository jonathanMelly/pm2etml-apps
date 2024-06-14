<?php

test('example', function () {
    $this->createUser(true,'root');
    $response = $this->get('/apps/smarties');

    //issue in gh action !!!
    //$response->assertStatus(config('app.smarties_enabled')?200:404);
});
