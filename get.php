<?php
if(isset($_GET['error'])) die("Please use get.php");

$ajaxPage = TRUE;
include("lib/common.php");

$full = GetFullURL();
$here = substr($full, 0, strrpos($full, "/"));
$fromoutside = (stripos($_SERVER['HTTP_REFERER'], $here) === FALSE);

if (is_numeric($_GET['id']))
{
	if ($fromoutside && $_SERVER['HTTP_REFERER'])
		die(header('Location: '.actionLink('downloads')));
	else
		die('This link is outdated, please tell whoever posted it to update it.<br><br>&bull; '.actionLinkTag('Downloads', 'downloads').'<br>&bull; '.actionLinkTag('Misc. files', 'uploader'));
}

$force = isset($_GET['force']) && !$fromoutside;

if(isset($_GET['id']))
	$entry = Query("select * from {uploader} where id = {0}", $_GET['id']);
//else if(isset($_GET['file'])) // this is unused so uh
//	$entry = Query("select * from {uploader} where filename = {0}", $_GET['file']);
else
	die("Nothing specified.");

if(NumRows($entry))
{
	$entry = Fetch($entry);
	if (!HasPermission('uploader.deletefiles') && $entry['deldate'] != 0)
		die(__("No such file."));
	
	
	$isdownloads = ($entry['category'] != -1) && FetchResult("SELECT showindownloads FROM {uploader_categories} WHERE id={0}", $entry['category']);
	if ($isdownloads && !$force)
	{
		$lastid = FetchResult("SELECT id FROM {uploader} WHERE category={0} ORDER BY date DESC LIMIT 1", $entry['category']);
		if ($lastid != $entry['id'])
			die(header('Location: '.actionLink('downloads', $entry['id'])));
	}

	//Count downloads!
	if (!$isBot)
		Query("update {uploader} set downloads = downloads+1 where id = {0}", $entry['id']);

	if($entry['private'])
		$path = $dataDir."uploader/".$entry['user']."/".$entry['physicalname'];
	else
		$path = $dataDir."uploader/".$entry['physicalname'];

	if(!file_exists($path))
		die("No such file.");
	
	$fsize = filesize($path);
	$parts = pathinfo($entry['filename']);
	$ext = strtolower($parts["extension"]);
	$download = true;
	
	switch ($ext)
	{
		case "gif": $ctype="image/gif"; $download = false; break;
		case 'bmp': $path = 'img/nobmp.png'; $fsize = filesize($path);
		case "apng":
		case "png": $ctype="image/png"; $download = false; break;
		case "jpeg":
		case "jpg": $ctype="image/jpg"; $download = false; break;
		case "css": $ctype="text/css"; $download = false; break;
		case "txt": $ctype="text/plain"; $download = false; break;
		case "swf": $ctype="application/x-shockwave-flash"; $download = false; break;
		case "pdf": $ctype="application/pdf"; $download = false; break;
		case 'mp3': $ctype = 'audio/mpeg'; $download = false; break;
		default: $ctype="application/force-download"; break;
	} 

	$cachetime = 604800; // 1 week. Should be more than okay. Uploaded files aren't supposed to change.
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: private, post-check={$cachetime}, pre-check=999999999, min-fresh={$cachetime}, max-age={$cachetime}");
	header("Content-Type: ".$ctype);
	if($download)
		header("Content-Disposition: attachment; filename=\"".$entry['filename']."\";");
	else
		header("Content-Disposition: filename=\"".$entry['filename']."\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".$fsize);

	readfile($path);
}
else
{
	die(__("No such file."));
}

?>
