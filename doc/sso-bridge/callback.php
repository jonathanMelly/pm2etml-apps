<?php
require_once("sso-config.php");

$ssoResult = file_get_contents(SSO_PORTAL."bridge/check?correlationId=".$_SESSION[SESSION_SSO_KEY]);
$loginInfos=json_decode($ssoResult);

if(!array_key_exists($loginInfos["error"]))
{
    //TODO LOGIN user in your app with $email (select * from users where email=$email)...
    echo "Welcome ".$loginInfos["username"].", you are authenticated (email:".$loginInfos["email"]." )";
}
else{
    die($loginInfos["error"]);
}




