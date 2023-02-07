<?php

//Call this page after your internal logout stuff OR update URL_AFTER_LOGOUT... if you want
//to invalidate SSO token

require_once("sso-config.php");

$URL_AFTER_LOGOUT="https://" . $_SERVER['SERVER_NAME'] . "/";
header("Location: ".SSO_PORTAL."bridge/logout?redirectUri=$URL_AFTER_LOGOUT");
