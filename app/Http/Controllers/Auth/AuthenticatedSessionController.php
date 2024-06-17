<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
    public function create(Request $request)
    {
        //old login (used for staging)
        if (! config('auth.sso_login') || $request->hasAny('standard', 'old', 'no-sso', 'nosso', 'basic')) {
            return view('auth.login');
        }

        return view('auth.login-sso');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if (config('auth.sso_login')) {
            //Logout SSO if needed
            try {
                $azureLogoutUrl = sso()->getLogoutUrl(route('login'));

                return redirect($azureLogoutUrl);
            } catch (\Exception $e) {
                Log::debug('Not sso session, redirecting to standard /');
            }
        }

        return redirect('/');

    }
}
