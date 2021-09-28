<?php

// **BulkVS Inbound Text Webhook SMS Relay**
//
// Author: jeff@jhowe.net  Version 1: 09/28/2021
//
// Purpose: Forward inbound texts sent to your BulkVS webhook
// to a mobile number of your choice.
//
// Script will process and parse any SMS sent to your webhook from BulkVS
// and forward the text to a number of your choosing.

// Process requests sending data - BulkVS sends request data using POST in JSON format
$json = file_get_contents('php://input');

// Kill script if it's an empty request
if (empty($json)) {
  exit();
}

// Decode the json data
$decoded_json = json_decode($json);

// Validate request is coming from bulkvs gateway IP's:  199.255.157.195 or 69.12.88.195 currently
// Get requester's IP address
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

if (($ip_address != "199.255.157.195") && ($ip_address != "69.12.88.195")) {
  // Address is not from bulkvs, exit
  exit();
}

// Request validated - parse the SMS 'to', 'from', and 'message' into variables from the json data
$to = $decoded_json->To[0];
$from = $decoded_json->From;
$message = $decoded_json->Message;
// decode any special characters to readable format
$message = urldecode($message);

// Construct relay message
$sms_text = "From: ".$from." Message: ".$message;

// Construct relay data payload with bulkvs keys, to, from, and message details
$payload = json_encode( array(	"apikey" => "<bulkvs apikey>",
	"apisecret" => "<bulkvs secret>",
	"from" => "<11-digit bulkvs number sms will be sent from>",
	"to" => "<11-digit recipient number>",
	"message" => $sms_text,
) );

// Construct bulkvs SMS URL and forward the message using bulkvs gateway
$ch = curl_init( "https://portal.bulkvs.com/sendSMS" );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$result = curl_exec($ch);
curl_close($ch);

?>