<?php

/**
 * Use Eloquent model for data and o365 for password check...
 */

namespace App\Services;

use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\assertNotNull;

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
	function validateCredentials(\Illuminate\Contracts\Auth\Authenticatable $user, array $credentials) : bool {

        $plain = $this->getPassword($credentials);

        //This is IMPORTANT to let as it checks standard o365 provider requirements...
        $username = $this->getUsername($user,$credentials);
        assertNotNull($username);

        $validPassword = config('auth.fake_password');

        return $plain===$validPassword;

	}

}
