<?php

// **Anveo Direct Inbound SMS Webhook Relay**
//
// Author: jeff@jhowe.net  Version 1: 10/02/2021
//
// Purpose: Forward inbound texts sent to your Anveo webhook
// to a mobile number of your choice.
//
// Script will process and parse any SMS sent to your webhook from Anveo
// and forward the text to a number of your choosing.


//----- Begin Script --------//

//----- Request validation --------//

// Verify request is coming from the anveo gateway IP: 129.146.230.9 currently
// Get remote IP
if (!empty($_SERVER['HTTP_CLIENT_IP']))
  {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
  }
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
  {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
  }
else
  {
    $ip_address = $_SERVER['REMOTE_ADDR'];
  }
// Kill session if request is not from valid anveo IP
if ($ip_address != "129.146.230.9") {
  // Request is not from anveo, exit
  exit();
}

//----- Process request data --------//

// Parse incoming SMS GET variables
$to = $_GET['phonenumber'];
$from = $_GET['from'];
$message = $_GET['message'];
// prepare relay message to forward
$relay_msg = "From: ".$from." Message: ".$message;
// urlencode the message - for readability
$relay_msg = urlencode($relay_msg);

// Use curl to forward the SMS $relay_msg to the anveo gateway
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"https://www.anveo.com/api/v1.asp");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,"action=sms&apikey=<anveo api key>&from=$to&destination=<11-digit mobile number>&message=$relay_msg");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close ($ch);

?>