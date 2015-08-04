<?php
session_start();

require 'foursquare-php-sdk/FoursquareApi.php';

$client_key = "Y1LVKKLWLO44MBTMLV2NQQVGUJZ5PQGR25XEZLMCZIMEQXHO";
$client_secret = "HJ2OWQRQT4OBJXQAJXQB3DS3V5P2PXJFOGB2ZFAOUMQ3HAKK";

$_SESSION['client_key'] = $client_key;
$_SESSION['client_secret'] = $client_secret;

// Carrega Foursquare API library
$foursquare = new FoursquareApi($client_key,$client_secret);

return $foursquare;


