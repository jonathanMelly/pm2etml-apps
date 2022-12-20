<?php

switch (session_status())
{
    case PHP_SESSION_DISABLED   :
        die("Session must be enabled for SSO bridge");

    case PHP_SESSION_NONE:
        session_start();
        break;
}



header("Location: https://intranet.pm2etml.ch/auth/redirect")
