<?php

test('example', function () {
    $this->createUser(true,'root');
    $response = $this->get('/apps/smarties');

    $response->assertStatus(200);
});
