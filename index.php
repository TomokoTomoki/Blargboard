<?php

$starttime = microtime(true);

$ajaxPage = false;
if(isset($_GET["ajax"]))
	$ajaxPage = true;

require('lib/common.php');

if (isset($_GET['forcelayout']))
{
	setcookie('forcelayout', (int)$_GET['forcelayout'], time()+365*24*3600, $boardroot, "", false, true);
	die(header('Location: '.$_SERVER['HTTP_REFERER']));
}

$layout_birthdays = getBirthdaysText();

$metaStuff = array(
	'description' => Settings::get('metaDescription'),
	'tags' => Settings::get('metaTags')
);


//=======================
// Do the page

$mainPage = 'home';

if (isset($_GET['page']))
	$page = $_GET["page"];
else
	$page = $mainPage;
if(!ctype_alnum($page))
	$page = $mainPage;

if($page == $mainPage)
{
	if(isset($_GET['fid']) && (int)$_GET['fid'] > 0 && !isset($_GET['action']))
		die(header("Location: ".actionLink("forum", (int)$_GET['fid'])));
	if(isset($_GET['tid']) && (int)$_GET['tid'] > 0)
		die(header("Location: ".actionLink("thread", (int)$_GET['tid'])));
	if(isset($_GET['uid']) && (int)$_GET['uid'] > 0)
		die(header("Location: ".actionLink("profile", (int)$_GET['uid'])));
	if(isset($_GET['pid']) && (int)$_GET['pid'] > 0)
		die(header("Location: ".actionLink("post", (int)$_GET['pid'])));
}

define('CURRENT_PAGE', $page);

ob_start();
$layout_crumbs = "";

$fakeerror = false;
if ($loguser['flags'] & 0x2)
{
	if (rand(0,100) <= 70)
	{
		Alert("Could not load requested page: failed to connect to the database. Try again later.", 'Error');
		$fakeerror = true;
	}
}

if (!$fakeerror)
{
	try {
		try {
			if(array_key_exists($page, $pluginpages))
			{
				$plugin = $pluginpages[$page];
				$self = $plugins[$plugin];
				
				$page = "./plugins/".$self['dir']."/page_".$page.".php";
				if(!file_exists($page))
					throw new Exception(404);
				include($page);
				unset($self);
			}
			else {
				$page = 'pages/'.$page.'.php';
				if(!file_exists($page))
					throw new Exception(404);
				include($page);
			}
		}
		catch(Exception $e)
		{
			if ($e->getMessage() != 404)
			{
				throw $e;
			}
			require('pages/404.php');
		}
	}
	catch(KillException $e)
	{
		// Nothing. Just ignore this exception.
	}
}

if($ajaxPage)
{
	ob_end_flush();
	die();
}

$layout_contents = ob_get_contents();
ob_end_clean();

//Do these things only if it's not an ajax page.
include("lib/views.php");
setLastActivity();

//=======================
// Panels and footer

require('userpanel.php');

ob_start();
require('footer.php');
$layout_footer = ob_get_contents();
ob_end_clean();


//=======================
// Notification bars

$notifications = getNotifications();


//=======================
// Misc stuff

$layout_time = formatdatenow();
$layout_onlineusers = getOnlineUsersText();
$layout_birthdays = getBirthdaysText();
$layout_views = '<span id="viewCount">'.number_format($misc['views']).'</span> '.__('views');

$layout_title = htmlspecialchars(Settings::get("boardname"));
if($title != "")
	$layout_title .= " &raquo; ".$title;


//=======================
// Board logo and theme

function checkForImage(&$image, $external, $file)
{
	global $dataDir, $dataUrl;

	if($image) return;

	if($external)
	{
		if(file_exists($dataDir.$file))
			$image = $dataUrl.$file;
	}
	else
	{
		if(file_exists($file))
			$image = resourceLink($file);
	}
}

/*checkForImage($layout_logopic, true, "logos/logo_$theme.png");
checkForImage($layout_logopic, true, "logos/logo_$theme.jpg");
checkForImage($layout_logopic, true, "logos/logo_$theme.gif");
checkForImage($layout_logopic, true, "logos/logo.png");
checkForImage($layout_logopic, true, "logos/logo.jpg");
checkForImage($layout_logopic, true, "logos/logo.gif");
checkForImage($layout_logopic, false, "themes/$theme/logo.png");
checkForImage($layout_logopic, false, "themes/$theme/logo.jpg");
checkForImage($layout_logopic, false, "themes/$theme/logo.gif");
checkForImage($layout_logopic, false, "themes/$theme/logo.png");
checkForImage($layout_logopic, false, "img/logo.png");*/
// HAX NeriticNet style banner
/*require('data/logos/lolrandom/banners.php');
$layout_logopic = $dataUrl.'logos/lolrandom/'.$banners[array_rand($banners)];*/
$layout_logopic = $dataUrl.'logos/logo.jpg';

checkForImage($layout_favicon, true, "logos/favicon.gif");
checkForImage($layout_favicon, true, "logos/favicon.ico");
checkForImage($layout_favicon, false, "img/favicon.ico");

$layout_themefile = "themes/$theme/style.css";
if(!file_exists($layout_themefile))
	$layout_themefile = "themes/$theme/style.php";

$layout_contents = "<div id=\"page_contents\">$layout_contents</div>";
//=======================
// PoRA box

if(Settings::get("showPoRA"))
{
	$layout_pora = '
		<div class="PoRT nom">
			<table class="message outline">
				<tr class="header0"><th>'.Settings::get("PoRATitle").'</th></tr>
				<tr class="cell0"><td>'.Settings::get("PoRAText").'</td></tr>
			</table>
		</div>';
}
else
	$layout_pora = "";

//=======================
// Print everything!

$layout = 'abxd';

if($debugMode)
	$layout_contents.="<table class=\"outline margin width100\"><tr class=header0><th colspan=4>List of queries
	                   <tr class=header1><th>Query<th>Backtrace$querytext</table>";

if(!file_exists("layouts/$layout.php"))
	$layout = "abxd";
if ($mobileLayout) require("layouts/{$layout}_mobile.php");
else
require("layouts/$layout.php"); echo (isset($times) ? $times : "");

$bucket = "finish"; include('lib/pluginloader.php');

?>

