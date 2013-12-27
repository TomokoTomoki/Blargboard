<?php

$bbcodeCallbacks["youtube"] = "bbcodeYoutube";
$bbcodeCallbacks["video"] = "bbcodeVideo";
$bbcodeCallbacks["tindeck"] = "bbcodeTindeck";
$tagParseStatus["youtube"] = 2;
$tagParseStatus["video"] = 2;
$tagParseStatus["tindeck"] = 2;



function getYoutubeIdFromUrl($url) {
    $pattern =
        '%^# Match any youtube URL
        (?:https?://)?  # Optional scheme. Either http or https
        (?:www\.)?      # Optional www subdomain
        (?:             # Group host alternatives
          youtu\.be/    # Either youtu.be,
        | youtube\.com  # or youtube.com
          (?:           # Group path alternatives
            /embed/     # Either /embed/
          | /v/         # or /v/
          | /watch\?v=  # or /watch\?v=
          )             # End path alternatives.
        )               # End host alternatives.
        ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
        $%x'
        ;
    $result = preg_match($pattern, $url, $matches);
    if (false !== $result) {
        return $matches[1];
    }
    return false;
}

function bbcodeYoutube($contents, $arg)
{
	$contents = trim($contents);
	$id = getYoutubeIdFromUrl($contents);
	if($id)
		$contents = $id;

	if(!preg_match("/^[\-0-9_a-zA-Z]+$/", $contents))
		return "[Invalid youtube video ID]";

	$args = "";

	if($arg == "loop")
		$args .= "&amp;loop=1";

	return '[ythax]'.$contents.'/'.$args.'[/ythax]';
}

function bbcodeVideo($contents, $arg)
{
	return "<video src=\"$contents\" width=\"425\" height=\"344\"  controls=\"controls\">Video not supported &mdash; <a href=\"$contents\">download</a></video>";
}

function bbcodeTindeck($contents, $arg)
{
	return "<a href=\"http://tindeck.com/listen/$contents\"><img src=\"http://tindeck.com/image/$contents/stats.png\" alt=\"Tindeck\" /></a>";
}

?>
