<?php

test('Apply dracula theme', function () {
    $response = $this->get('/?theme=dracula');
    $response->assertSessionHas('theme', 'dracula');
});
