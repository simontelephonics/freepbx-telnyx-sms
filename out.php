<?php

/* This script is called by the dialplan to send a message using the Telnyx v2 API.
 * Enter your telnyx v2 API key below.
 * This script takes normalized 10-digit US/CAN numbers and adds the leading +1 for the API.
 * You might want to do it differently if you send SMS to non-US/CAN locations.
 */

$logfile = '/tmp/sms.log'; // all SMSes and errors will be logged here for debugging purposes

// Record the message
$postdata = file_get_contents("php://input");
$fh = fopen($logfile, "a");
fwrite($fh, "$postdata\n\n");
fclose($fh);

// set up curl and send message
$telnyxUrl = 'https://api.telnyx.com/v2/messages/long_code';
$telnyxKey = 'YOUR_API_KEY_HERE';

$telnyxBody = json_encode(
	array(
		"from" => "+1" . $_GET["from"],
		"to"   => "+1" . $_GET["to"],
		"text" => $postdata
	));
$httpopts = array('http' => 
	array(
		'method' => 'POST',
		'header' => array(
			'Content-type: application/json',
			'Accept: application/json',
			'Authorization: Bearer '. $telnyxKey
		),
		'timeout' => '3',
		'content' => $telnyxBody
	));
$httpcontext = stream_context_create($httpopts);
$httpresult = file_get_contents($telnyxUrl, false, $httpcontext);

// Respond to Asterisk
echo $httpresult;

?>
