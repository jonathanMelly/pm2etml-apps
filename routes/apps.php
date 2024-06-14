<?php
/**
 * @param $appName
 * @param $callback
 * @return void
 */
function defineAppsRoute($appName, $callback, $middlewares=['auth','app']): void
{
    if(config('app.'.$appName.'_enabled') || !app()->environment('production')){
        Route::group(
            [
                'prefix' => 'apps/'.$appName,
                'as' => $appName . '.',
                'middleware' => $middlewares
            ],
            $callback
        );
    }
}

require_once __DIR__ . '/apps-manager.php';
require_once __DIR__ . '/apps-smarties.php';
