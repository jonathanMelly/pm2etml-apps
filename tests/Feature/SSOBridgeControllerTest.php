<?php

test('Correlation ID for SSO is well generated and put into cache', function () {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */

    $response = $this->get(route('ssobridge.create-correlation-id'));

    $response->assertStatus(200);

    $id=json_decode($response->content(),true)["correlationId"];

    $this->assertEquals(64,strlen($id));

    \PHPUnit\Framework\assertTrue(\Illuminate\Support\Facades\Cache::has(\App\Http\Controllers\Auth\SSOBridgeController::SSO_BRIDGE_CORRELATION_ID_GENERATOR.$id),"Missing cache entry for correlationid");
});
