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
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    public function ssoRedirect(Request $request)
    {
        //Handle SSO from external source which MUST give a security token in the URI (will be called back upon success)
        //Expects that uri has form http://...?token=12345
        $uri = $request->get('uri');
        if(isset($uri))
        {
            Session::put('sso-uri',$uri);
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

        $user = User::query()->where('email','=',$ssoUser->getEmail())->first();
        Log::debug(var_export($user,true));

        if($user!=null)
        {
            if(Session::exists('sso-uri'))
            {
                //TODO handle logout after that...
                //Expects that redirect uri has form http://...?token=12345
                //and will return http://...?token=12345&email=bob@kelso.com
                $customRedirect = Session::pull('sso-uri');
                return redirect($customRedirect.'&email='.$user->email);
            }

            Auth::login($user);
            return redirect('/dashboard');
        }

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
            /* @var $sso \SocialiteProviders\Azure\Provider */
            $sso = sso();
            $user = $sso->user();
            if($user!=null)
            {
                Log::debug('Sso session, redirecting to sso logout');
                $azureLogoutUrl = $sso->getLogoutUrl(route('login'));
                return redirect($azureLogoutUrl);
            }

        }
        catch(\Exception $e)
        {
            Log::debug('Not sso session, redirecting to standard /');
            return redirect('/');
        }


    }
}
