<?php

/**
 * Use Eloquent model for data and o365 for password check...
 */

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

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
    public function __construct($model, $endpoint)
    {
        $this->model = $model;
        $this->endpoint = $endpoint;
    }

    public function validateCredentialsRaw(string $user, string $password): bool
    {
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            $hostAndPort = explode(':', $this->endpoint);

            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host = $hostAndPort[0];                     //Set the SMTP server to send through
            $mail->SMTPAuth = true;                                   //Enable SMTP authentication
            $mail->Username = $user;                     //SMTP username
            $mail->Password = $password;                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            $mail->Port = $hostAndPort[1];                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            if ($mail->smtpConnect()) {
                $mail->smtpClose();

                return true;
            } else {
                Log::info('Cannot connect to '.$mail->Host." (auth for user $user)");
            }

            return false;

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'auth')) {
                Log::info("Bad credentials for user $user");
            } else {
                Log::error("Cannot auth $user: {$mail->ErrorInfo}, exception: ".$e->getMessage().' trace:'.$e->getTraceAsString());
            }

            return false;
        }
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(\Illuminate\Contracts\Auth\Authenticatable $user, array $credentials): bool
    {
        $plain = $this->getPassword($credentials);
        $username = $this->getUsername($user, $credentials);

        return $this->validateCredentialsRaw($username, $plain);
    }

    public function getUsername(\Illuminate\Contracts\Auth\Authenticatable $user, array $credentials): string
    {
        if (is_a($user, User::class)) {
            return $user->username;
        }

        return $credentials['username'];
    }

    public function getPassword(array $credentials): string
    {
        return $credentials['password'];
    }

    public function rehashPasswordIfRequired(UserContract $user, array $credentials, bool $force = false)
    {
        //Nothing to be done here
    }
}
