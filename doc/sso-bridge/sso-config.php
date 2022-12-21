<?php
const SSO_PORTAL = "https://intranet.pm2etml.ch/auth/";
const SESSION_SSO_KEY = "sso_correlation_id";

//Get correlation in session
switch (session_status())
{
    case PHP_SESSION_DISABLED   :
        die("Session must be enabled for SSO bridge");

    case PHP_SESSION_NONE:
        session_start();
        break;
}
