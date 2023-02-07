<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SSOBridgeController extends Controller
{

    const SSO_BRIDGE_CORRELATION_ID_GENERATOR = "sso-bridge-correlationId-generator:";

    public function logout(Request $request)
    {
        $azureLogoutUrl = sso()->getLogoutUrl($request->input("redirectUri"));
        return redirect($azureLogoutUrl);
    }

    public function check(Request $request)
    {
        $correlationId = $request->input("correlationId");
        $ssoData = \Cache::pull(AuthenticatedSessionController::SSO_BRIDGE_CORRELATION_ID_PREFIX_CACHE_KEY . $correlationId,
            ["error"=>"invalid correlationId $correlationId"]);

        return json_encode($ssoData);
    }

    //Help use consistent correlationId
    public function createCorrelationId(Request $request)
    {
        //Security delay (as ip based throttling is a bit hard...)
        sleep(1);

        //Generate random correlationId and store in session
        try {
            $randomBytes = random_bytes(32);
        } catch (Exception $e) {
            Log::error("Cannot generate random number ", ["exception"=>$e]);
            abort(500,"Cannot generate random number");
        }
        $ssoCorrelationId = bin2hex($randomBytes);

        Cache::put(self::SSO_BRIDGE_CORRELATION_ID_GENERATOR . $ssoCorrelationId,$request->getClientIp()." | ".now()->toDateTimeLocalString(),60*15);

        return json_encode(["correlationId"=>$ssoCorrelationId]);
    }
}
