<?php

/**
 * Use Eloquent model for data and o365 for password check...
 */

namespace App\Providers;

use Illuminate\Support\Facades\Log;

class O365EloquantMixTestUserProvider extends O365EloquantMixUserProvider
{

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param \Illuminate\Contracts\Auth\Authenticatable $user
	 * @param array $credentials
	 *
	 * @return bool
	 */
	function validateCredentials(\Illuminate\Contracts\Auth\Authenticatable $user, array $credentials) {

        $plain = $this->getPassword($credentials);

        /*
        $username = $this->getUsername($credentials);

        $imap_endpoint = $this->getEndpointURI($username);
        Log::debug("Login tentative from $username on ".$imap_endpoint);
        */

        //WARNING, env is working int TEST because there is no cache !!!!!! (not working in prod)
        $validPassword = env('DASHBOARD_PASSWORD','pentest');

        return $plain===$validPassword;

        //return $this->hasher->check($plain, $user->getAuthPassword());
	}

}
