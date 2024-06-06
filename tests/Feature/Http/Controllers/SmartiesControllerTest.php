<?php

test('example', function () {
    $this->createUser(true,'root');
    $response = $this->get('/apps/manager');

    $response->assertStatus(200);
});
