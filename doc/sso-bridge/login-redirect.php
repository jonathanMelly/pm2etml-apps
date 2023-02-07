<?php
require_once("sso-config.php");

$ssoCorrelationId=@json_decode(@file_get_contents(SSO_PORTAL."bridge/cid"),true)["correlationId"];
if($ssoCorrelationId=="")
{
    die("Cannot initiate SSO");
}
$_SESSION[SESSION_SSO_KEY]=$ssoCorrelationId;

//Configure URLs
$LOGIN_CALLBACK_URI="http".((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')?'s':'')."://" . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['PHP_SELF']) . "/callback.php";
$SSO_URL= SSO_PORTAL . "redirect?redirectUri=$LOGIN_CALLBACK_URI?correlationId=$ssoCorrelationId";

//Redirect to SSO Login
header("Location: $SSO_URL");
