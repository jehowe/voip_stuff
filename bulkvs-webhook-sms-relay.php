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


//----- Begin Script --------//

//----- Request validation - two steps --------//

// Step 1: Verify request is coming from bulkvs gateway IP's: 199.255.157.195 or 69.12.88.195 currently
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
// Kill session if request is not from valid bulkvs IP's
if (($ip_address != "199.255.157.195") && ($ip_address != "69.12.88.195")) {
  // Request is not from bulkvs, exit
  exit();
}

// Collect request data - BulkVS sends request data using POST in JSON format
$json = file_get_contents('php://input');

// Step 2: Kill script if it's an empty request
if (empty($json)) {
  exit();
}

//----- Process request data --------//

// Decode the json data
$decoded_json = json_decode($json);

// Parse incoming SMS 'To', 'From', and 'Message' json data into variables
$to = $decoded_json->To[0];
$from = $decoded_json->From;
$message = $decoded_json->Message;
// Decode any special characters if found in the message to readable format
$message = urldecode($message);

// Construct outbound SMS relay message
$sms_text = "From: ".$from." Message: ".$message;

// Construct outbound SMS relay data into json payload with bulkvs keys, to, from, and message details
$payload = json_encode( array(	"apikey" => "<bulkvs apikey>",
	"apisecret" => "<bulkvs secret>",
	"from" => $to,
	"to" => "<11-digit recipient number>",
	"message" => $sms_text
) );

// Construct bulkvs SMS URL and forward the message using curl to the bulkvs gateway
$ch = curl_init( "https://portal.bulkvs.com/sendSMS" );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
$result = curl_exec($ch);
curl_close($ch);

?>
