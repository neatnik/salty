<!DOCTYPE html>
<html lang="en">
<head>
<title>Salty</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
	font-family: sans-serif;
}
main {
	margin: auto;
	max-width: 50em;
	line-height: 1.5em;
}
input, textarea {
	display: block;
	width: 100%;
}
pre {
	white-space: pre-wrap;
	white-space: -moz-pre-wrap;
	white-space: -pre-wrap;
	white-space: -o-pre-wrap;
	word-wrap: break-word;
}
</style>
</head>
<body>

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
	
	// is the resulting payload something that comes back as valid?
	$dec = salty_decrypt($pl, $salty_key);
	
	$result = "\n".'<section id="results"><h2>Results</h2>';
	
	if($dec === false) {
		$result .= "\n".'<p><span class="detected"><i class="fas fa-radar"></i> Detected unencrypted plaintext.</span></p>';
		$enc = salty_encrypt($payload, $salty_key); //generates random encrypted string (Base64 related)
		$encrypted = str_split($enc, 2);
		$encrypted = '-- BEGIN SALTY ENCRYPTED MESSAGE --'."\n".implode(' ', $encrypted)."\n".'-- END SALTY ENCRYPTED MESSAGE --';
		$dec = salty_decrypt($enc, $salty_key); //generates random encrypted string (Base64 related)
		$result .= "\n".'<h3>Shareable cipher</h3>';
		$result .= "\n".'<pre>'.htmlentities($encrypted).'</pre>';
		$enc2 = salty_encrypt($payload, $salty_key); //generates random encrypted string (Base64 related)
		$result .= "\n".'<h3>Compressed version</h3>';
		$result .= "\n".'<pre>'.htmlentities($enc2).'</pre><p><small><span class="chars">'.strlen($enc2).' chars</span></small></p>';
	}
	else {
		$result .= "\n".'<p><span class="detected"><i class="fas fa-radar"></i> Detected Salty cipher.</span></p>';
		$result .= "\n".'<h3>Decrypted</h3>';
		$result .= "\n".'<pre>'.htmlentities($dec).'</pre>';
	}
	
	$result .= "\n</section>";
	
}
else {
	$payload = 'The quick brown fox jumped over the lazy sleeping dog.';
	$key = 'hunter2';
	
	$result = null;
}

?>

<main>

<h1>Salty</h1>

<p><em>Portable NaCl-powered encryption</em></p>

<p>Salty makes it easy to send strongly-encrypted messages with a shared key. It uses <a href="https://nacl.cr.yp.to">NaCl</a> for encryption and <a href="http://base91.sourceforge.net">basE91</a> for portability.</p>

<p>With Salty, you can encrypt a message as long as 185 characters and the resulting cipher will still fit in a tweet (~277 characters), making it ideal for encrypting tweets or other length-restricted communication. You can use it anywhere, though, with text of any length.</p>

<form action="#results" method="post">
	<fieldset>
		<legend>Try it out</legend>
		<p>Enter a payload and provide a key below. Your payload can be unencrypted plaintext or a Salty-encrypted cipher (detected automatically).</p>
		<p>
			<label for="payload">Payload</label>
			<textarea name="payload" id="payload"><?php echo $payload; ?></textarea>
		</p>
		<p>
			<label for="key">Key</label>
			<input type="text" name="key" id="key" value="<?php echo $key; ?>">
		</p>
		<input type="submit" value="Go">
	</fieldset>
</form>

<?php echo $result; ?>

<h2>More info</h2>

<p><i class="fab fa-github-alt"></i> Salty is open source and <a href="http://github.com/neatnik/salty">available on GitHub</a>. Detailed information can be found in the project’s <a href="https://github.com/neatnik/salty/blob/master/README.md">README</a> file.</p>

</main>