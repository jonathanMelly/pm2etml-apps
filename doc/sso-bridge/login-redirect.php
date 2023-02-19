<?php
require_once("lib-sso.php");

$apiKey="PLEASE ASK FOR A TOKEN";
$cid = generateCorrelationId();

login($cid,$apiKey,["homepage"=>"home.php"]);
