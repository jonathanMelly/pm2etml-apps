<?php

test('example', function () {
    $this->createUser(true,'root');
    $response = $this->get('/apps/smarties');

    $response->assertStatus(config('app.smarties_enabled')?200:404);
});
