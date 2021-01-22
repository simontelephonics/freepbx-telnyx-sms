<?php

/* This script is called by the dialplan to send a message using the Telnyx API.
 * Enter your telnyx API token below.
 * This script takes normalized 10-digit US/CAN numbers and adds the leading +1 for the API.
 * You might want to do it differently if you send SMS to non-US/CAN locations.
 */

$logfile = '/tmp/sms.log'; // all SMSes and errors will be logged here for debugging purposes

// Record the message
$postdata = file_get_contents("php://input");
$fh = fopen($logfile, "a");
fwrite($fh, date(DATE_W3C) . " - Received SMS from PBX: to " . $_GET["to"] . ", from " . $_GET["from"] . "\n");
fwrite($fh, "$postdata\n\n");
fclose($fh);

// set up curl and send message
$telnyxUrl = 'https://sms.telnyx.com/messages';
$telnyxToken = 'YOUR-TOKEN-HERE';

$telnyxBody = json_encode(
	array(
		"from" => "+1" . $_GET["from"],
		"to"   => "+1" . $_GET["to"],
		"body" => $postdata
	));
$httpopts = array('http' => 
	array(
		'method' => 'POST',
		'header' => array(
			'Content-type: application/json',
			'X-Profile-Secret: '. $telnyxToken
		),
		'timeout' => '3',
		'content' => $telnyxBody
	));
$httpcontext = stream_context_create($httpopts);
$httpresult = file_get_contents($telnyxUrl, false, $httpcontext);

// Respond to Asterisk
echo $httpresult;

?>
