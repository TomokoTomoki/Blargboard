<?php

$starttime = microtime(true);

$ajaxPage = false;
if(isset($_GET['ajax']))
	$ajaxPage = true;

require('lib/common.php');

$layout_crumbs = '';
$layout_actionlinks = '';

if (isset($_GET['forcelayout']))
{
	setcookie('forcelayout', (int)$_GET['forcelayout'], time()+365*24*3600, $boardroot, "", false, true);
	die(header('Location: '.$_SERVER['HTTP_REFERER']));
}

$layout_birthdays = getBirthdaysText();

$tpl->assign('logusername', htmlspecialchars($loguser['displayname'] ?: $loguser['name']));
$tpl->assign('loguserlink', UserLink($loguser));

$metaStuff = array(
	'description' => Settings::get('metaDescription'),
	'tags' => Settings::get('metaTags')
);


//=======================
// Do the page

$mainPage = 'home';

if (isset($_GET['page']))
	$page = $_GET['page'];
else
	$page = $mainPage;
if(!ctype_alnum($page))
	$page = $mainPage;

// TODO make them take URL names into account? or nuke them?
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
	if (rand(0,100) <= 75)
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
				
				chdir('plugins/'.$self['dir']);
				$page = "pages/".$page.".php";
				if(!file_exists($page))
					throw new Exception(404);
				include($page);
				unset($self);
			}
			else 
			{
				$page = 'pages/'.$page.'.php';
				if(!file_exists($page))
					throw new Exception(404);
				include($page);
			}
		}
		catch(Exception $e)
		{
			chdir(BOARD_CWD);
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
	
	chdir(BOARD_CWD);
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

require('sidebar.php');

$mobileswitch = '';
if ($mobileLayout) $mobileswitch .= 'Mobile view - ';
if ($_COOKIE['forcelayout']) $mobileswitch .= '<a href="?forcelayout=0" rel="nofollow">Auto view</a>';
else if ($mobileLayout) $mobileswitch .= '<a href="?forcelayout=-1" rel="nofollow">Force normal view</a>';
else $mobileswitch .= '<a href="?forcelayout=1" rel="nofollow">Force mobile view [BETA]</a>';


//=======================
// Notification bars

$notifications = getNotifications();


//=======================
// Misc stuff

$layout_time = formatdatenow();
$layout_onlineusers = getOnlineUsersText();
$layout_birthdays = getBirthdaysText();
$layout_views = '<span id="viewCount">'.number_format($misc['views']).'</span> '.__('views');

$layout_title = htmlspecialchars(Settings::get('boardname'));
if($title != '')
	$layout_title .= ' &raquo; '.$title;


//=======================
// Board logo and theme

$layout_logopic = 'img/logo.png';
if (!file_exists($layout_logopic))
	$layout_logopic = 'img/logo.jpg';
$layout_logopic = resourceLink($layout_logopic);

$favicon = resourceLink('img/favicon.ico');

$themefile = "themes/$theme/style.css";
if(!file_exists($themefile))
	$themefile = "themes/$theme/style.php";

$layout_credits = 
'<img src="'.resourceLink('img/poweredbyblarg.png').'" style="float: left; margin-right: 3px;">
<a href="http://kuribo64.net/blargboard/" target="_blank">Blargboard 1.1</a> &middot; by StapleButter<br>
Based off <a href="http://abxd.dirbaio.net/" target="_blank">ABXD</a> by Dirbaio, Kawa &amp; co.<br>';
	

$layout_contents = "<div id=\"page_contents\">$layout_contents</div>";

//=======================
// Print everything!

//if($debugMode)
//	$layout_contents.="<table class=\"outline margin width100\"><tr class=header0><th colspan=4>List of queries
//	                   <tr class=header1><th>Query<th>Backtrace$querytext</table>";

$perfdata = 'Page rendered in '.sprintf('%.03f',microtime(true)-$starttime).' seconds (with '.$queries.' SQL queries and '.sprintf('%.03f',memory_get_usage() / 1024).'K of RAM)';

?>
<!doctype html>
<html lang="en">
<head>
	<title><?php print $layout_title; ?></title>
	
	<meta http-equiv="Content-Type" content="text/html; CHARSET=utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=10">
	<meta name="description" content="<?php print $metaStuff['description']; ?>">
	<meta name="keywords" content="<?php print $metaStuff['tags']; ?>">
	
	<link rel="shortcut icon" type="image/x-icon" href="<?php print $favicon;?>">
	<link rel="stylesheet" type="text/css" href="<?php print resourceLink("css/common.css");?>">
	<link rel="stylesheet" type="text/css" id="theme_css" href="<?php print resourceLink($themefile); ?>">
	<link rel="stylesheet" type="text/css" href="<?php print resourceLink('css/font-awesome.min.css'); ?>">

	<script type="text/javascript" src="<?php print resourceLink("js/jquery.js");?>"></script>
	<script type="text/javascript" src="<?php print resourceLink("js/tricks.js");?>"></script>
	<script type="text/javascript" src="<?php print resourceLink("js/jquery.tablednd_0_5.js");?>"></script>
	<script type="text/javascript" src="<?php print resourceLink("js/jquery.scrollTo-1.4.2-min.js");?>"></script>
	<script type="text/javascript" src="<?php print resourceLink("js/jscolor/jscolor.js");?>"></script>
	<script type="text/javascript">boardroot = <?php print json_encode($boardroot); ?>;</script>

	<?php $bucket = "pageHeader"; include("./lib/pluginloader.php"); ?>
	
	<?php if ($mobileLayout) { ?>
	<meta name="viewport" content="user-scalable=yes, initial-scale=1.0, width=device-width">
	<script type="text/javascript" src="<?php echo resourceLink('js/mobile.js'); ?>"></script>
	<?php if ($oldAndroid) { ?>
	<style type="text/css"> 
	#mobile-sidebar { height: auto!important; max-height: none!important; } 
	#realbody { max-height: none!important; max-width: none!important; overflow: scroll!important; } 
	</style>
	<?php } ?>
	
	<?php } ?>
</head>
<body style="width:100%; font-size: <?php echo $loguser['fontsize']; ?>%;">
<form action="<?php echo actionLink('login'); ?>" method="post" id="logout" style="display:none;"><input type="hidden" name="action" value="logout"></form>
<?php 
	if (Settings::get('maintenance'))
		echo '<div style="font-size:30px; font-weight:bold; color:red; background:black; padding:5px; border:2px solid red; position:absolute; top:30px; left:30px;">MAINTENANCE MODE</div>';

	RenderTemplate('pagelayout', array(
		'layout_contents' => $layout_contents,
		'layout_crumbs' => $layout_crumbs,
		'layout_actionlinks' => $layout_actionlinks,
		'headerlinks' => $headerlinks,
		'sidelinks' => $sidelinks,
		'layout_userpanel' => $layout_userpanel,
		'notifications' => $notifications,
		'boardname' => Settings::get('boardname'),
		'poratitle' => Settings::get('PoRATitle'),
		'poratext' => Settings::get('PoRAText'),
		'layout_logopic' => $layout_logopic,
		'layout_time' => $layout_time,
		'layout_views' => $layout_views,
		'layout_onlineusers' => $layout_onlineusers,
		'layout_birthdays' => $layout_birthdays,
		'layout_credits' => $layout_credits,
		'mobileswitch' => $mobileswitch,
		'perfdata' => $perfdata)); 
?>
</body>
</html>
<?php

$bucket = "finish"; include('lib/pluginloader.php');

?>

