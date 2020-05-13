<?php

include('salty.php');

if(isset($_REQUEST['payload'])) {
	$payload = $_REQUEST['payload'];
	$key = $_REQUEST['key'];
	$salty_key = salty_key($key);
	
	$pl = $payload;
	
	$pl = str_replace('-- BEGIN SALTY ENCRYPTED MESSAGE --', '', $pl);
	$pl = str_replace('-- END SALTY ENCRYPTED MESSAGE --', '', $pl);
	$pl = str_replace("\n", '', $pl);
	$pl = str_replace("\r", '', $pl);
	$pl = str_replace(' ', '', $pl);
	
	// is the resulting payload valid?
	$dec = salty_decrypt($pl, $salty_key);
	
	$result = "\n".'<div id="salty">';
	
	if($dec === false) {
		$result .= "\n".'<p><span class="detected">Auto-detected: unencrypted plaintext</span></p>';
		$enc = salty_encrypt($payload, $salty_key); //generates random encrypted string (Base64 related)
		$encrypted = str_split($enc, 2);
		$encrypted = '-- BEGIN SALTY ENCRYPTED MESSAGE --'."\n".implode(' ', $encrypted)."\n".'-- END SALTY ENCRYPTED MESSAGE --';
		$dec = salty_decrypt($enc, $salty_key); //generates random encrypted string (Base64 related)
		$result .= "\n".'<h3>Shareable cipher</h3>';
		$result .= "\n".'<p class="output uncompressed">'.htmlentities($encrypted).'</p>';
		$enc2 = salty_encrypt($payload, $salty_key); //generates random encrypted string (Base64 related)
		$result .= "\n".'<h3>Compressed version</h3>';
		$result .= "\n".'<p class="output breakable compressed">'.htmlentities($enc2).'</p><p><span class="chars">'.strlen($enc2).' chars</span></p>';
	}
	else {
		$result .= "\n".'<p><span class="detected">Auto-detected: Salty cipher</span></p>';
		$result .= "\n".'<h3>Decrypted</h3>';
		$result .= "\n".'<p class="output decrypted">'.htmlentities($dec).'</p>';
	}
	
	$result .= "\n".'</div>';
	
}
else {
	$payload = 'The quick brown fox jumped over the lazy sleeping dog.';
	$key = 'hunter2';
	
	$result = null;
}


?><!DOCTYPE html>
<html lang="en">
<head>
<title>Salty: Portable NaCl-powered encryption</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width">
<meta property="og:title" content="Salty: Portable NaCl-powered encryption">
<meta property="og:url" content="https://salty.fyi/">
<meta property="og:description" content="Portable NaCl-powered encryption">
<link rel="stylesheet" type="text/css" href="/style.css?v=20200209">
<script src="https://kit.fontawesome.com/99c1e8e2fb.js" crossorigin="anonymous"></script>
</head>

<body>

<main>

<h1>Salty</h1>

<p><em>Portable NaCl-powered encryption</em></p>

<p>Salty makes it easy to send strongly-encrypted messages with a shared key. It uses <a href="https://nacl.cr.yp.to">NaCl</a> for encryption and <a href="http://base91.sourceforge.net">basE91</a> for portability.</p>

<p>With Salty, you can encrypt a message as long as 185 characters and the resulting cipher will still fit in a tweet (~277 characters), making it ideal for encrypting tweets or other length-restricted communication. You can use it anywhere, though, with text of any length.</p>

<h2>Try it out</h2>

<p>Enter a payload and provide a key below. Your payload can be unencrypted plaintext or a Salty-encrypted cipher (detected automatically).</p>

<form action="/#salty" method="post">

<label for="payload">Payload</label>
<p><textarea name="payload" id="payload"><?php echo $payload; ?></textarea></p>

<label for="key">Key</label>
<p><input type="text" name="key" id="key" value="<?php echo $key; ?>"></p>

<input type="submit" value="Go">

</form>

<?php

echo $result;

?>

<h2>Source</h2>

<p>Salty is open source and <a href="http://github.com/neatnik/salty">available on GitHub</a>.</p>

<h2>More info</h2>

<p>Detailed information can be found in the project’s <a href="https://github.com/neatnik/salty/blob/master/README.md">README</a> file.</p>

</main>

</body>
</html>