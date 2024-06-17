<?php

test('manager controller shows up', function () {
    $this->createUser(true, 'root');
    $response = $this->get('/apps/manager');

    $response->assertStatus(config('app.manager_enabled') ? 200 : 404);
});
