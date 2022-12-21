<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SSOBridgeController extends Controller
{
    public function logout(Request $request)
    {
        $azureLogoutUrl = sso()->getLogoutUrl($request->input("redirectUri"));
        return redirect($azureLogoutUrl);
    }

    public function check(Request $request)
    {
        $correlationId = $request->input("correlationId");
        $ssoEmail = \Cache::pull(AuthenticatedSessionController::SSO_BRIDGE_CORRELATION_ID_PREFIX_CACHE_KEY . $correlationId,"invalid");

        return json_encode($ssoEmail);
    }
}
