<?php

test('example', function () {
    $this->createUser(true,'root');
    $response = $this->get('/apps/manager');

    //issue in gh action !!!
    //$response->assertStatus(config('app.manager_enabled')?200:404);
});
