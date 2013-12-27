<?php

function validateSuspiciousTag($stuff)
{
	// only allow Youtube embeds
	if (preg_match('@^<iframe\s+(width="\d+%?"\s*height="\d+%?"\s*)?src="http://www\.youtube\.com/embed/[a-zA-Z0-9_-]{11}"(\s*frameborder="\d+"\s*allowfullscreen\s*)?>$@si', $stuff[0]))
		return $stuff[0];
	
	return '&lt;malicious tag&gt;';
}

function securityFilter($text)
{
	$text = preg_replace_callback('@<(script|iframe|object|embed|meta|plaintext|noscript|base).*?>@si', 'validateSuspiciousTag', $text);
	$text = preg_replace('@(on)([\w+]\s*=)@si', '$1<span></span>$2', $text);
	
	$text = nl2br($text);
	
	return $text;
}

?>