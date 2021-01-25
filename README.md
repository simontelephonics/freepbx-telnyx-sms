# freepbx-telnyx-sms
Scripts for sending and receiving SMS between FreePBX and Telnyx

## Installation

Add the dialplan blocks to `/etc/asterisk/extensions_custom.conf`, adjusting them according to your environment:
* extension length
* catch-all e-mail to receive undeliverable texts
* number normalization

Make a directory under (wwwroot) called `sms` and ensure it can be reached by HTTPS from outside.

Place the `out.php` and `telnyx.php` scripts into `sms` and adjust them according to your environment.

out.php:
* add your Telnyx API token

telnyx.php:
* note the instructions in the comments at the top of the file

Place the included `php-sip` library in a directory of that name under `sms`.

## FreePBX configuration

### Trunk
Set up a PJSIP trunk for 127.0.0.1 as follows:

![PJSIP trunk](https://user-images.githubusercontent.com/5303782/105723214-85843f80-5ef4-11eb-94e1-6e38e35e448b.png)

Set the Message Context:

![PJSIP advanced tab](https://user-images.githubusercontent.com/5303782/105723266-96cd4c00-5ef4-11eb-856c-a9640a2f7a1e.png)

...

![PJSIP message context field](https://user-images.githubusercontent.com/5303782/105723305-a0ef4a80-5ef4-11eb-82ba-1be9766a9e9e.png)

### Extensions
For each extension that will participate in SMS, set the Account Code to the normalized DID this extension will send and receive as, and set the Message Context:

![Extension settings](https://user-images.githubusercontent.com/5303782/105723337-ab114900-5ef4-11eb-99d0-333328a07479.png)

...

![Extension account code](https://user-images.githubusercontent.com/5303782/105723364-b2385700-5ef4-11eb-9332-f533f1317dc7.png)

...

![Extension message context](https://user-images.githubusercontent.com/5303782/105723387-b8c6ce80-5ef4-11eb-887c-34201324a265.png)


## Telnyx configuration

Set up a Messaging Profile (APIv1) in Telnyx:

![Telnyx messaging profile](https://user-images.githubusercontent.com/5303782/105724305-b4e77c00-5ef5-11eb-846d-8b58e958b14b.png)

Specify the path to the `telnyx.php` script in Inbound Settings:

![Messaging profile inbound settings](https://user-images.githubusercontent.com/5303782/105724385-cf215a00-5ef5-11eb-8b28-0cfd1feaa182.png)

Copy the Profile Secret from the Outbound Settings section and use it as the token in your `out.php` script.

Save this profile and assign it to the DIDs you want to enable for SMS.

## Usage

SMS from Telnyx will be delivered to the `telnyx.php` script specifying a DID. Any extension whose Account Code has that DID will receive the SMS. 

SMS from extensions will be sent to Telnyx using the caller ID in the extension's Account Code field. You can only send from numbers that are on your account.

Extensions can text among themselves through the Asterisk dialplan without engaging Telnyx. 

These scripts are normalized for US/CAN 10-digit DIDs and would need to be adjusted for international SMS or handling of short codes.
