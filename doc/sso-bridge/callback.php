<?php
require_once("sso-config.php");

$email = file_get_contents(SSO_PORTAL."bridge/check?correlationId=".$_SESSION[SESSION_SSO_KEY]);

//TODO LOGIN user in your app with $email (select * from users where email=$email)...
echo "Welcome $email, you are authenticated";



