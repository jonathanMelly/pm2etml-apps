<?php

/**
 * Use Eloquent model for data and o365 for password check...
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Facades\Log;

class O365EloquantMixUserProvider extends EloquentUserProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        Log::info(__CLASS__ . "registered");
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    //
    }

    /**
     * Create a new database user provider.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  string  $model
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $credentials
     *
     * @return bool
     */
    function validateCredentials(\Illuminate\Contracts\Auth\Authenticatable $user, array $credentials) : bool
    {

        $plain = $credentials['password'];
        $username = $credentials['username'];

        $imap_stream = @imap_open('{'.env('LOGIN_SMTP_ENDPOINT', 'smtp')."/imap/ssl/authuser=$username}", $username, $plain, OP_HALFOPEN, 1);
        if ($imap_stream === false) {
            return false;
        }
        else {
            return imap_close($imap_stream);
        }

    }

}
