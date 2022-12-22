<?php
require_once("sso-config.php");

$ssoResult = file_get_contents(SSO_PORTAL."bridge/check?correlationId=".$_SESSION[SESSION_SSO_KEY]);
$loginInfos=json_decode($ssoResult,true);

if(!array_key_exists("error",$loginInfos))
{
    //TODO LOGIN user in your app with $email (select * from users where email=$email)...
    echo "Welcome ".$loginInfos["username"].", you are authenticated (email:".$loginInfos["email"]." )";
}
else{
    die("Login error: ".$loginInfos["error"]);
}




