<?php
require_once("lib-sso.php");

$token="PLEASE ASK FOR IT";
$cid = $_SESSION[SESSION_SSO_KEY];
$ssoResult = RetrieveSSOLoginInfos($token,$cid);

if($ssoResult->IsSuccess())
{
    //TODO Auth user in your app (select * from users where email=$ssolResult->email ...)
    //$email = $ssoResult->email;
    //$username = $ssoResult->username;
}




