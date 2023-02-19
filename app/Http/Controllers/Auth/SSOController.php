<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SSOController extends Controller
{

    const SSO_BRIDGE_CORRELATION_ID_GENERATOR = "sso-bridge-correlationId-generator:";
    const SSO_BRIDGE_REDIRECT_URI_SESSION_KEY = 'sso-bridge-uri';
    const SSO_BRIDGE_CID_SESSION_KEY = 'sso-bridge-cid';
    const SSO_BRIDGE_CORRELATION_ID_PREFIX_CACHE_KEY = "sso-bridge-correlationId:";
    const SSO_CORRELATION_ID_PARAM_NAME="correlationId";
    const SSO_API_KEY_PARAM_NAME="token";

    public function logout(Request $request)
    {
        $azureLogoutUrl = sso()->getLogoutUrl($request->input("redirectUri"));
        return redirect($azureLogoutUrl);
    }

    public function check(Request $request)
    {
        $correlationId = $request->input(self::SSO_CORRELATION_ID_PARAM_NAME);

        if(Config::get('auth.sso_bridge_api_key_mandatory')) {
            if ($request->missing(self::SSO_API_KEY_PARAM_NAME))
            {
                Log::warning("Missing api key in check sso bridge call",["request"=>$request]);
                abort(403);
            }
            else{
                $clientToken=$request->input(self::SSO_API_KEY_PARAM_NAME);
                if($clientToken!=Config::get('auth.sso_bridge_api_key'))
                {
                    Log::warning("Bad api key given in check sso bridge call",["request"=>$request]);
                    abort(403);
                }
            }
        }

        $ssoData = \Cache::pull(self::SSO_BRIDGE_CORRELATION_ID_PREFIX_CACHE_KEY . $correlationId,
            ["error"=>"invalid correlationId $correlationId"]);

        return json_encode($ssoData);
    }

    public function ssoLoginRedirect(Request $request)
    {
        //Handle SSO from external source which MUST give a correlationId saved in session (on external app)
        $redirectUri = $request->get('redirectUri');
        if(isset($redirectUri)) {

            //V2
            $correlationId = $request->get(self::SSO_CORRELATION_ID_PARAM_NAME);

            //Check fon V1 (wait for clients to upgrade)
            if(!isset($correlationId))
            {
                //CorrelationId must be kept in session of external app
                $parts = explode("?correlationId=",$redirectUri);

                if(sizeof($parts)!=2)
                {
                    $message = "Cannot retrieve correlationId V1 from redirect URI";
                    Log::warning($message,["redirect"=>$redirectUri]);
                    abort(403,$message);
                }
                $redirectUri=$parts[0];
                $correlationId = $parts[1];
                Log::warning("Client using cid V1 (included in redirectURI)",["request"=>$request]);
            }

            //Bridge request
            if (isset($correlationId)) {
                if (Config::get('auth.sso_bridge_api_key_mandatory')) {
                    $token = $request->get(self::SSO_API_KEY_PARAM_NAME);
                    if ($token != Config::get('auth.sso_bridge_api_key'))
                    {
                        Log::warning("Bad token for SSO bridge",["token"=>$token,"request"=>$request]);
                        abort(403);
                    }
                }

                Session::put(self::SSO_BRIDGE_REDIRECT_URI_SESSION_KEY,$redirectUri);

                //Store it for callback
                Session::put(self::SSO_BRIDGE_CID_SESSION_KEY,$correlationId);
            }
            else
            {
                abort(403,"Missing correlationId for sso bridge request");
            }

        }

        return sso()->redirect();
    }

    public function ssoCallback()
    {

        $ssoUser = sso()->user();
        Log::debug(var_export($ssoUser,true));
        $ssoUserInfos = $ssoUser->user;

        //Avatar seems to be null ... Look at https://github.com/SocialiteProviders/Microsoft/blob/master/MicrosoftUser.php#L7
        $email = $ssoUserInfos["mail"];
        $username= $ssoUserInfos["userPrincipalName"];

        //Bridge ?
        if(Session::exists(self::SSO_BRIDGE_REDIRECT_URI_SESSION_KEY))
        {
            //Expects that redirect uri has form http://...?correlationId=12345
            //and will return http://...
            $redirectURI = Session::pull(self::SSO_BRIDGE_REDIRECT_URI_SESSION_KEY);

            $correlationId = Session::pull(self::SSO_BRIDGE_CID_SESSION_KEY);
            $response = redirect($redirectURI);

            //Check is done here because we have a chance to give feedback to user (would be more logic in ssoRedirect)
            if(\Cache::pull(self::SSO_BRIDGE_CORRELATION_ID_GENERATOR . $correlationId,null)==null)
            {
                Log::warning("Received sso with correlationId not generated by us/ missing from the cache",["correlationId"=>$correlationId]);
                $response->header("Warning",[110,"sso-bridge","CorrelationId should ideally be generated from us, please use ".route('ssobridge.create-correlation-id')]);
            }

            \Cache::add(self::SSO_BRIDGE_CORRELATION_ID_PREFIX_CACHE_KEY . $correlationId,
                ["username"=>$username,"email"=>$email],config('auth.sso_bridge_session_ttl'));

            Log::info("Redirecting sso-bridge-request with cid $correlationId to $redirectURI");

            return $response;
        }
        //Local LOGIN
        else {
            //Try to match with email or username as data source is not very accurate (best effort)
            $user = User::query()
                ->where('email','=',$email)
                ->orWhere('username','=',$username)
                ->first();
            if($user!=null)
            {
                Auth::login($user);
                return redirect('/dashboard');
            }
        }

        //Default
        Log::warning("Unknown validated SSO account with username=$username and email=$email");
        return redirect('login')->withErrors(['username' => __('Unknown account')]);

    }

    //Help use consistent correlationId
    public function createCorrelationId(Request $request)
    {
        //Generate random correlationId and store in session
        try {
            $randomBytes = random_bytes(32);
        } catch (Exception $e) {
            Log::error("Cannot generate random number ", ["exception"=>$e]);
            abort(500,"Cannot generate random number");
        }
        $ssoCorrelationId = bin2hex($randomBytes);

        Cache::put(self::SSO_BRIDGE_CORRELATION_ID_GENERATOR . $ssoCorrelationId,
            $request->getClientIp()." | ".now()->toDateTimeLocalString(),60*15);

        return json_encode([self::SSO_CORRELATION_ID_PARAM_NAME=>$ssoCorrelationId]);
    }
}
