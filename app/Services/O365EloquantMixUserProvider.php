<?php

/**
 * Use Eloquent model for data and o365 for password check...
 */

namespace App\Services;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Facades\Log;

class O365EloquantMixUserProvider extends EloquentUserProvider
{
    protected $endpoint;

    /**
     * Create a new database user provider.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  string  $model
     * @return void
     */
    public function __construct($model,$endpoint)
    {
        $this->model = $model;
        $this->endpoint = $endpoint;
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

        $plain = $this->getPassword($credentials);
        $username = $this->getUsername($credentials);

        $imap_endpoint = $this->getEndpointURI($username);

        $imap_stream = @imap_open($imap_endpoint, $username, $plain, OP_HALFOPEN, 1);
        if ($imap_stream === false) {

            $error = var_export(imap_errors(), true);
            Log::info("Login tentative from $username on ".$imap_endpoint ." failed: ".$error);

            return false;
        }
        else {
            return imap_close($imap_stream);
        }

    }

    function getUsername(array $credentials):string
    {
        return $credentials['username'];
    }

    function getPassword(array $credentials):string
    {
        return $credentials['password'];
    }

    function getEndpointURI(string $username):string
    {
        return '{'.$this->endpoint."/imap/ssl/authuser=$username}";
    }

}
