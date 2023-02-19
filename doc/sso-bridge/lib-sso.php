<?php
const SSO_PORTAL = "https://intranet.pm2etml.ch/auth/";
const SESSION_SSO_KEY = "sso_bridge_correlation_id";

//Get correlation in session
switch (session_status())
{
    case PHP_SESSION_DISABLED   :
        die("Session must be enabled for SSO bridge");

    case PHP_SESSION_NONE:
        session_start();
        break;
}

function login(string $cid,string $apiKey,array $customRedirectParameters=[]): void
{
    //Configure URLs
    $LOGIN_CALLBACK_URI = "http" . ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/callback.php";

    $redirectUri = $LOGIN_CALLBACK_URI;
    foreach ($customRedirectParameters as $name =>$value)
    {
        $redirectUri.="&".$name."=".$value;
    }

    $SSO_URL= SSO_PORTAL . "redirect?". "correlationId=" . $cid."&token=".$apiKey."&redirectUri=".urlencode($redirectUri);

    //Redirect to SSO Login
    header("Location: $SSO_URL");
}

function generateCorrelationId()
{
    $ssoCorrelationId=@json_decode(@file_get_contents(SSO_PORTAL."bridge/cid"),true)["correlationId"];
    if($ssoCorrelationId=="")
    {
        try {
            $randomBytes = random_bytes(32);
        } catch (Exception $e) {
            die("Cannot generate valid correlationId");
        }
        $ssoCorrelationId = bin2hex($randomBytes);
    }
    $_SESSION[SESSION_SSO_KEY]=$ssoCorrelationId;

    return $ssoCorrelationId;
}
