<?php

//Main post parsing function.
function applyNetiquetteToLinks($match)
{
	if (substr($match[1], 0, 7) != 'http://')
		return $match[0];

	if (stripos($match[1], 'http://'.$_SERVER['SERVER_NAME']) === 0)
		return $match[0];

	$res = $match[0];
	$res .= ' target="_blank"';
	if(Settings::get("nofollow"))
		$res .= ' rel="nofollow"';
	return $res;
}

function cleanUpPost($postText, $poster = "", $noSmilies = false, $noBr = false)
{
	global $postNoSmilies, $postNoBr, $smilies, $postPoster;
	static $orig, $repl;

	$postNoSmilies = $noSmilies;
	$postNoBr = $noBr;
	$postPoster = $poster;

	$s = $postText;

	$s = parseBBCode($s);

	$s = preg_replace_callback("@<a[^>]+href\s*=\s*\"(.*?)\"@si", 'ApplyNetiquetteToLinks', $s);
	$s = preg_replace_callback("@<a[^>]+href\s*=\s*'(.*?)'@si", 'ApplyNetiquetteToLinks', $s);
	$s = preg_replace_callback("@<a[^>]+href\s*=\s*([^\"'][^\s>]*)@si", 'ApplyNetiquetteToLinks', $s);

	$s = str_ireplace('4shared', 'shittyfilehost', $s);
	//$s = str_ireplace('imageshack', 'imageshit', $s);

	$s = str_ireplace('autoplay', 'auto<i></i>play', $s);

	$s = securityPostFilter($s);

	return $s;
}

//Security filters.
//The functions below are CRITICAL for the post security.
//Should always run LAST and on the WHOLE post.

function filterJS($match)
{
	$url = html_entity_decode($match[2]);
	$url = str_replace(" ", "", $url);
	$url = str_replace("\t", "", $url);
	$url = str_replace("\r", "", $url);
	$url = str_replace("\n", "", $url);
	if (stristr($url, "javascript:"))
		return "";
	return $match[0];
}

//Scans for any numerical entities that decode to the 7-bit printable ASCII range and removes them.
//This makes a last-minute hack impossible where a javascript: link is given completely in absurd and malformed entities.
function eatThatPork($s)
{
	$s = preg_replace_callback("/(&#)(x*)([a-f0-9]+(?![a-f0-9]))(;*)/i", "checkKosher", $s);
	return $s;
}

function checkKosher($matches)
{
	$num = ltrim($matches[3], "0");
	if($matches[2])
		$num = hexdec($num);
	if($num < 127)
		return ""; //"&#xA4;";
	else
		return "&#x".dechex($num).";";
}

function securityPostFilter($s)
{
	$s = str_replace("\r\n","\n", $s);

	$s = EatThatPork($s);

	$s = preg_replace("@(on)(\w+?\s*?)=@si", '$1$2&#x3D;', $s);
	
	$s = preg_replace('@<(/?(?:script|meta|xmp|plaintext|noscript|iframe|embed|object|base|textarea).*?)>@si', '&lt;$1&gt;', $s);
	
	// hack
	$s = preg_replace('@\[ythax\]([a-zA-Z0-9-_]{11})/(&amp;loop=1)?\[/ythax\]@i', 
		'<iframe width="560" height="315" src="http://www.youtube.com/embed/$1$2" frameborder="0" allowfullscreen></iframe>', $s);

	$s = preg_replace("'-moz-binding'si"," -mo<em></em>z-binding", $s);
	//$s = preg_replace("'filter:'si","filter<em></em>:>", $s);
	//$s = preg_replace("'javascript:'si","javascript<em></em>:>", $s);

	$s = preg_replace_callback("@(href|src)\s*=\s*\"([^\"]+)\"@si", "FilterJS", $s);
	$s = preg_replace_callback("@(href|src)\s*=\s*'([^']+)'@si", "FilterJS", $s);
	$s = preg_replace_callback("@(href|src)\s*=\s*([^\s>]+)@si", "FilterJS", $s);

	return $s;
}
