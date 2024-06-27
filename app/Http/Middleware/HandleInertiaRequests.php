<?php

namespace App\Http\Middleware;

use App\View\Components\RootLayout;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'apps';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /* no need for further splitting for now...
        if ($request->is(config('app.manager_prefix').'*')) {
            $this->rootView = 'apps/manager';
            //Config::set('inertia.ssr.enabled', false);
        }
        else if ($request->is(config('app.smarties_prefix').'*')) {
            $this->rootView = 'apps/smarties';
            //Config::set('inertia.ssr.enabled', false);
        }
        */

        //https://inertiajs.com/shared-data
        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'auth' => function () use ($request) {
                return [
                    'user' => $request->user() ? [
                        'id' => $request->user()->id,
                        'first_name' => $request->user()->first_name,
                        'last_name' => $request->user()->last_name,
                        'email' => $request->user()->email,
                    ] : null,
                ];
            },
            'flash' => function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                    'error' => $request->session()->get('error'),
                    'message' => $request->session()->get('message'),
                ];
            },
            'theme' =>session('theme')??'light',
            'lang' => str_replace('_', '-', app()->getLocale()),
            'version' => RootLayout::computeVersion(),
            'environment'=>app()->environment(),
        ]);
    }
}
