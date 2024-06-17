<?php

/**
 * Use Eloquent model for data and o365 for password check...
 */

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class O365EloquantMixTestUserProvider extends O365EloquantMixUserProvider
{
    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(\Illuminate\Contracts\Auth\Authenticatable $user, array $credentials): bool
    {

        $plain = $this->getPassword($credentials);

        //This is IMPORTANT to let as it checks standard o365 provider requirements...
        $username = $this->getUsername($user, $credentials);
        assert($username != null);

        $validPassword = config('auth.fake_password');

        return $plain === $validPassword;

    }

    public function rehashPasswordIfRequired(UserContract $user, array $credentials, bool $force = false)
    {
        //Nothing to be done here
    }
}
