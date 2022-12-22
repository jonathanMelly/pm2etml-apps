<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AuthenticatedSessionController extends Controller
{
    const SSO_BRIDGE_REDIRECT_URI_SESSION_KEY = 'sso-bridge-uri';
    const SSO_BRIDGE_CORRELATION_ID_PREFIX_CACHE_KEY = "sso-bridge-correlationId:";

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        //old login (used for staging)
        if(!config('auth.sso_login') || $request->hasAny('standard','old','no-sso','nosso','basic'))
        {
            return view("auth.login");
        }

        return view("auth.login-sso");
    }

    public function ssoRedirect(Request $request)
    {
        //Handle SSO from external source which MUST give a correlationId saved in session (on external app)
        $uri = $request->get('redirectUri');
        if(isset($uri))
        {
            Session::put(self::SSO_BRIDGE_REDIRECT_URI_SESSION_KEY,$uri);
        }

        return sso()->redirect();
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }


    public function ssoCallback()
    {

        $ssoUser = sso()->user();
        Log::debug(var_export($ssoUser,true));
        $ssoUserInfos = $ssoUser->user;

        //This seems to give usernameo365...
        //$email = $ssoUser->getEmail();
        //Avatar seems to be null ... Look at https://github.com/SocialiteProviders/Microsoft/blob/master/MicrosoftUser.php#L7
        //to implement it...

        $email = $ssoUserInfos["mail"];
        $username= $ssoUserInfos["userPrincipalName"];

        //Bridge ?
        if(Session::exists(self::SSO_BRIDGE_REDIRECT_URI_SESSION_KEY))
        {
            //Expects that redirect uri has form http://...?correlationId=12345
            //and will return http://...
            $customRedirect = Session::pull(self::SSO_BRIDGE_REDIRECT_URI_SESSION_KEY);

            //CorrelationId must be kept in session of external app
            $parts = explode("?correlationId=",$customRedirect);
            $uri=$parts[0];
            $correlationId = $parts[1];

            \Cache::add(self::SSO_BRIDGE_CORRELATION_ID_PREFIX_CACHE_KEY . $correlationId,
                ["username"=>$username,"email"=>$email],config('auth.sso_bridge_session_ttl'));
            Log::info("Redirecting sso-bridge-request with cid $correlationId to $uri");
            return redirect($uri);
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

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        //Logout SSO if needed
        try {
            $azureLogoutUrl = sso()->getLogoutUrl(route('login'));
            return redirect($azureLogoutUrl);
        }
        catch(\Exception $e)
        {
            Log::debug('Not sso session, redirecting to standard /');
            return redirect('/');
        }


    }
}
