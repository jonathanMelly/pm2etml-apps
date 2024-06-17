<?php

test('SSO Bridge : Correlation ID for SSO is well generated and put into cache', function () {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */

    $response = $this->get(route('ssobridge.create-correlation-id', ['token' => 'key123']));

    $response->assertStatus(200);

    $id = json_decode($response->content(), true)['correlationId'];

    $this->assertEquals(64, strlen($id));

    \PHPUnit\Framework\assertTrue(\Illuminate\Support\Facades\Cache::has(\App\Http\Controllers\Auth\SSOController::SSO_BRIDGE_CORRELATION_ID_GENERATOR.$id), 'Missing cache entry for correlationid');
});

test('SSO Bridge login: correlationId V2 and custom params well parsed', function () {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */

    //GIVEN
    $cid = 45;
    $subquery = 'https://www.client.com/callback?custom=456&homepage=46';

    //WHEN
    $response = $this->get(route('sso-login-redirect').'?correlationId='.$cid.'&token=key123&bob=test&redirectUri='.urlencode($subquery).'&after=123');

    $response->assertStatus(302);
    \PHPUnit\Framework\assertEquals($subquery, \Illuminate\Support\Facades\Session::get(\App\Http\Controllers\Auth\SSOController::SSO_BRIDGE_REDIRECT_URI_SESSION_KEY));
    \PHPUnit\Framework\assertEquals($cid, \Illuminate\Support\Facades\Session::get(\App\Http\Controllers\Auth\SSOController::SSO_BRIDGE_CID_SESSION_KEY));

});

test('SSO Bridge login: correlationId V1 well parsed', function () {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */

    //GIVEN
    $cid = 45;
    $subquery = 'https://www.client.com/callback?'.'correlationId='.$cid;

    //WHEN
    $response = $this->get(route('sso-login-redirect').'?token=key123&redirectUri='.urlencode($subquery).'&after=123');

    $response->assertStatus(302);
    \PHPUnit\Framework\assertEquals($cid, \Illuminate\Support\Facades\Session::get(\App\Http\Controllers\Auth\SSOController::SSO_BRIDGE_CID_SESSION_KEY));

});

test('SSO Bridge check: token not enforced OK', function () {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */

    //GIVEN
    \Illuminate\Support\Facades\Config::set('auth.sso_bridge_api_key_mandatory', false);

    //WHEN
    $response = $this->get(route('ssobridge.check', ['correlationId' => 'bad', 'missingToken' => 'missing']));

    $response->assertStatus(200);
    $response->assertJson(['error' => 'invalid correlationId bad']);

});

test('SSO Bridge check: token enforced KO if missing', function () {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */

    //GIVEN
    \Illuminate\Support\Facades\Config::set('auth.sso_bridge_api_key_mandatory', true);

    //WHEN
    $response = $this->get(route('ssobridge.check', ['correlationId' => 'bad', 'missingToken' => 'missing']));
    $response->assertStatus(403);

});

test('SSO bridge login: bad/missing parameters', function ($param) {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */

    //GIVEN

    //WHEN
    $response = $this->get(route('sso-login-redirect').'?redirectUri=test&'.$param);

    //THEN
    $response->assertStatus(403);

})->with(['correlationIdMissing=notgiven', '']);

test('SSO login: standard sso (no bridge)', function () {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */

    //GIVEN

    //WHEN
    $response = $this->get(route('sso-login-redirect').'?NoredirectUri=no');

    //THEN
    $response->assertStatus(302);
    \PHPUnit\Framework\assertFalse(\Illuminate\Support\Facades\Session::exists(\App\Http\Controllers\Auth\SSOController::SSO_BRIDGE_REDIRECT_URI_SESSION_KEY));
    \PHPUnit\Framework\assertFalse(\Illuminate\Support\Facades\Session::exists(\App\Http\Controllers\Auth\SSOController::SSO_BRIDGE_CID_SESSION_KEY));

});

test('SSO bridge : callback with auto-generatedId ', function () {
    /* @var $response \Illuminate\Testing\TestResponse */
    /* @var $this \Tests\TestCase */

    //Given
    $cid = 45;
    $redirectURI = 'https://www.client.com/check';
    $response = $this->get(route('sso-login-redirect').'?correlationId='.$cid.'&token=key123&redirectUri='.urlencode($redirectURI));
    $response->assertStatus(302);

    $user = new Laravel\Socialite\Two\User();
    $user->user = ['mail' => 'bob@eduvaud.ch', 'userPrincipalName' => 'px52@eduvaud.ch'];
    $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
    $provider->shouldReceive('user')->andReturn($user);
    $provider->shouldReceive('redirect');
    Socialite::shouldReceive('driver')->with('azure')->andReturn($provider);

    //when
    $response = $this->get(route('sso-callback'));

    //then
    $response->assertStatus(302);
    $response->assertHeader('Location', $redirectURI);
    $response->assertHeader('Warning', 110);

    //and then
    $response = $this->get(route('ssobridge.check', ['correlationId' => $cid, 'token' => 'key123']));
    $response->assertStatus(200);
    $checkData = json_decode($response->content(), true);
    \PHPUnit\Framework\assertEquals('bob@eduvaud.ch', $checkData['email']);
    \PHPUnit\Framework\assertEquals('px52@eduvaud.ch', $checkData['username']);

});
