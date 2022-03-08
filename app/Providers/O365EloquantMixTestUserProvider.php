<?php

/**
 * Use Eloquent model for data and o365 for password check...
 */

namespace App\Providers;

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
        $plain = $credentials['password'];

        return $plain==="pentest";

        //return $this->hasher->check($plain, $user->getAuthPassword());
	}

}
