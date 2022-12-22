<?php
require_once("sso-config.php");

//Generate random correlationId and store in session
try {
    $randomBytes = random_bytes(32);
} catch (Exception $e) {
    die("Cannot generate random number : ". $e->getMessage());
}
$ssoCorrelationId = bin2hex($randomBytes);
$_SESSION[SESSION_SSO_KEY]=$ssoCorrelationId;

//Configure URLs
$LOGIN_CALLBACK_URI="http".((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')?'s':'')."://" . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['PHP_SELF']) . "/callback.php";
$SSO_URL= SSO_PORTAL . "redirect?redirectUri=$LOGIN_CALLBACK_URI?correlationId=$ssoCorrelationId";

//Redirect to SSO Login
header("Location: $SSO_URL");
