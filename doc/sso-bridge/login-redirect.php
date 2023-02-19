<?php
require_once("lib-sso.php");

$apiKey="PLEASE ASK FOR A TOKEN";
$cid = GenerateCorrelationId($apiKey);

InitiateSSOLogin($cid,["homepage"=>"home.php"]/*Example, can be empty*/);
