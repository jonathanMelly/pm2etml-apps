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
     * Create a new database user provider.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  string  $model
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
        Log::info(__CLASS__ . "built");
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $credentials
     *
     * @return bool
     */
    function validateCredentials(\Illuminate\Contracts\Auth\Authenticatable $user, array $credentials)
    {

        $plain = $credentials['password'];
        $username = $credentials['username'];

        $imap_endpoint = '{'.env('LOGIN_SMTP_ENDPOINT')."/imap/ssl/authuser=$username}";
        Log::debug("Login tentative from $username on ".$imap_endpoint);

        $imap_stream = @imap_open($imap_endpoint, $username, $plain, OP_HALFOPEN, 1);
        if ($imap_stream === false) {
            return false;
        }
        else {
            return imap_close($imap_stream);
        }

    }

}
