<?php /*

          /)
 _   _   // _/_
/_)_(_(_(/_ (__(_/_
              .-/
             (_/

Salty: Portable NaCl-powered encryption
https://salty.fyi

Released as free software under the terms of the MIT License.

Copyright (c) 2021 Neatnik LLC
All rights reserved.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

// Your salt should be a hex representation of 16 bytes of cryptographically secure random binary data.
// You can generate a good random salt value from the PHP CLI like this: php -r "echo bin2hex(random_bytes(16));"
// The resulting string will look like this: 7674ffcd9882e411415ea1ab7726642d

define('SALT', sodium_hex2bin('add your hex string here'));

// basE91 encoding
// Copyright (c) 2005-2006 Joachim Henke
// http://base91.sourceforge.net/

$b91_enctab = array(
	'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
	'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
	'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
	'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
	'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!', '#', '$',
	'%', '&', '(', ')', '*', '+', ',', '.', '/', ':', ';', '<', '=',
	'>', '?', '@', '[', ']', '^', '_', '`', '{', '|', '}', '~', '"'
);

$b91_dectab = array_flip($b91_enctab);

function base91_decode($d) {
	global $b91_dectab;
	$n = $b = $o = null;
	$l = strlen($d);
	$v = -1;
	for ($i = 0; $i < $l; ++$i) {
		$c = $b91_dectab[$d[$i]];
		if(!isset($c))
			continue;
		if($v < 0)
			$v = $c;
		else {
			$v += $c * 91;
			$b |= $v << $n;
			$n += ($v & 8191) > 88 ? 13 : 14;
			do {
				$o .= chr($b & 255);
				$b >>= 8;
				$n -= 8;
			} while ($n > 7);
			$v = -1;
		}
	}
	if($v + 1)
		$o .= chr(($b | $v << $n) & 255);
	return $o;
}

function base91_encode($d) {
	global $b91_enctab;
	$n = $b = $o = null;
	$l = strlen($d);
	for ($i = 0; $i < $l; ++$i) {
		$b |= ord($d[$i]) << $n;
		$n += 8;
		if($n > 13) {
			$v = $b & 8191;
			if($v > 88) {
				$b >>= 13;
				$n -= 13;
			} else {
				$v = $b & 16383;
				$b >>= 14;
				$n -= 14;
			}
			$o .= $b91_enctab[$v % 91] . $b91_enctab[$v / 91];
		}
	}
	if($n) {
		$o .= $b91_enctab[$b % 91];
		if($n > 7 || $b > 90)
			$o .= $b91_enctab[$b / 91];
	}
	return $o;
}

// basE91 code ends here; Salty code begins

function salty_encrypt($message, $key) {
	$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
	$cipher = base91_encode($nonce.sodium_crypto_secretbox($message, $nonce, $key));
	sodium_memzero($message);
	sodium_memzero($key);
	return $cipher;
}

function salty_decrypt($encrypted, $key) {
	$decoded = base91_decode($encrypted);
	if($decoded === false) return false;
	if(mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) return false;
	$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
	$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
	$plain = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
	if($plain === false) return false;
	sodium_memzero($ciphertext);
	sodium_memzero($key);
	return $plain;
}

function salty_key($key) {
	$key = sodium_crypto_pwhash(32, $key, SALT, SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE);
	return $key;
}

// API

if(isset($_REQUEST['action'])) {
	
	if($_REQUEST['action'] !== 'encrypt' && $_REQUEST['action'] !== 'decrypt') {
		$response['http_status'] = 400;
		$response['response'] = 'Error: action must be one of \'encrypt\' or \'decrypt\'.';
	}
	
	else if(!isset($_REQUEST['payload'])) {
		$response['http_status'] = 400;
		$response['response'] = 'Error: missing payload parameter.';
	}
	
	else if(!isset($_REQUEST['key'])) {
		$response['http_status'] = 400;
		$response['response'] = 'Error: missing key parameter.';
	}
	
	else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'encrypt') {
		$response['http_status'] = 200;
		$response['response'] = salty_encrypt($_REQUEST['payload'], salty_key($_REQUEST['key']));
	}
	
	else if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'decrypt') {
		
		$result = salty_decrypt($_REQUEST['payload'], salty_key($_REQUEST['key']));
		
		if(!$result) {
			$response['http_status'] = 400;
			$response['response'] = 'Error: incorrect key';
		}
		else {
			$response['http_status'] = 200;
			$response['response'] = salty_decrypt($_REQUEST['payload'], salty_key($_REQUEST['key']));
		}
	}
	
	http_response_code($response['http_status']);
	header('Content-type: application/json');
	die(json_encode($response, JSON_PRETTY_PRINT));
	
}
