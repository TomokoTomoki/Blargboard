<?php
// AcmlmBoard XD support - Main hub

header('Cache-control: no-cache, private');
header('X-Frame-Options: DENY');

// I can't believe there are PRODUCTION servers that have E_NOTICE turned on. What are they THINKING? -- Kawa
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);

if(!is_file('config/database.php'))
	die(header('Location: install.php'));


$boardroot = preg_replace('{/[^/]*$}', '/', $_SERVER['SCRIPT_NAME']);
define('BOARD_CWD', getcwd());

$dataDir = 'data/';
$dataUrl = $boardroot.$dataDir;


// Deslash GPC variables if we have magic quotes on
if (get_magic_quotes_gpc())
{
	function AutoDeslash($val)
	{
		if (is_array($val))
			return array_map('AutoDeslash', $val);
		else if (is_string($val))
			return stripslashes($val);
		else
			return $val;
	}

	$_REQUEST = array_map('AutoDeslash', $_REQUEST);
	$_GET = array_map('AutoDeslash', $_GET);
	$_POST = array_map('AutoDeslash', $_POST);
	$_COOKIE = array_map('AutoDeslash', $_COOKIE);
}

function usectime()
{
	$t = gettimeofday();
	return $t['sec'] + ($t['usec'] / 1000000);
}

$forumBoards = array('' => 'Main forums');


include('config/salt.php');

include("settingsfile.php");

include("debug.php");
include("mysql.php");
include("mysqlfunctions.php");
include("settingssystem.php");
Settings::load();
Settings::checkPlugin("main");

include("feedback.php");
include("language.php");
include("snippets.php");
include("links.php");

class KillException extends Exception { }
date_default_timezone_set("GMT");
$timeStart = usectime();

$title = "";

//WARNING: These things need to be kept in a certain order of execution.

$thisURL = $_SERVER['SCRIPT_NAME'];
if($q = $_SERVER['QUERY_STRING'])
	$thisURL .= "?$q";

include("pluginsystem.php");
loadFieldLists();
include("loguser.php");
include("permissions.php");

if (Settings::get('maintenance') && !$loguser['root'] && (!isset($_GET['page']) || $_GET['page'] != 'login'))
{
	die('The board is in maintenance mode, please try again later. Our apologies for the inconvenience.');
}

include('firewall.php');
include("ranksets.php");
include("bbcode_parser.php");
include("bbcode_text.php");
include("bbcode_callbacks.php");
include("bbcode_main.php");
include("post.php");
include("onlineusers.php");

$theme = $loguser['theme'];
include('lib/layout.php');

//Classes

include('smarty/Smarty.class.php');
$tpl = new Smarty;
$tpl->assign('config', array('date' => $loguser['dateformat'], 'time' => $loguser['timeformat']));
$tpl->assign('loguserid', $loguserid);

$mainPage = "board";
$bucket = "init"; include(BOARD_CWD."/lib/pluginloader.php");

