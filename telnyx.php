<?php

/* This script receives SMS webhooks from Telnyx and converts to SIP MESSAGEs for Asterisk.
 * It must be accessible by Telnyx (permissions, firewall, etc.)
 * Set the local UDP port variable below according to what SIP port you are using, default 5060.
 * Create a SIP trunk for 127.0.0.1:5099 so that Asterisk will accept messages from this script.
 */

$localUdpPort = '5060'; // don't change this unless you use a non-standard listening port
$logfile = '/tmp/sms.log'; // all SMSes and errors will be logged here for debugging purposes

// Record the message
$postdata = file_get_contents("php://input");
$fh = fopen($logfile, "a");
fwrite($fh, "$postdata\n\n");
fclose($fh);
// Respond to Telnyx
echo 'OK' . "\n";

// Find the recipient in astdb
$sms = json_decode($postdata, true);
if ($sms['data']['event_type'] != 'message.received') {
	exit;
}
$payload = $sms['data']['payload'];
foreach ($payload['to'] as $k=>$v) {
	if (preg_match("/\+1([2-9][0-9][0-9][2-9][0-9]{6})/", $v['phone_number'] , $matches)) {
		$to = $matches[1];
		$output = shell_exec('/usr/sbin/asterisk -rx "database showkey accountcode"');
		$count = preg_match_all("#AMPUSER/([0-9]+)/accountcode.*: $to\s*$#m", $output, $exts);
		if ($count) {
			require_once('php-sip/PhpSIP.class.php');
			$smsout = new PhpSIP('127.0.0.1', '5099');
			foreach ($exts[1] as $ext) {
				$smsout->newCall();
				$smsout->setMethod('MESSAGE');
				$smsout->setFrom('sip:' . $payload['from']['phone_number'] . '@127.0.0.1');
				$smsout->setContentType('text/plain; charset=UTF-8');
				$smsout->setBody($payload['text']);
				$smsout->setUri('sip:' . $ext . '@127.0.0.1:' . $localUdpPort);
				$res = $smsout->send(); 
			}
		} else {
			$fh = fopen($logfile, "a");
			fwrite($fh, date($payload['received_at']) . " - Nowhere to send " . $payload['id'] . "\n\n");
			fclose($fh);
		}
	}
}
?>
