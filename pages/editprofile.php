<?php

//Check Stuff
if(!$loguserid)
	Kill(__("You must be logged in to edit your profile."));

if (isset($_POST['action']) && $loguser['token'] != $_POST['key'])
	Kill(__("No."));

if(isset($_POST['editusermode']) && $_POST['editusermode'] != 0)
	$_GET['id'] = $_POST['userid'];

$editUserMode = false;

if (HasPermission('admin.editusers'))
{
	$userid = (isset($_GET['id'])) ? (int)$_GET['id'] : $loguserid;
	if (isset($_GET['id'])) $editUserMode = true;
}
else
{
	CheckPermission('user.editprofile');
	$userid = $loguserid;
}

$user = Fetch(Query("select * from {users} where id={0}", $userid));
$usergroup = $usergroups[$user['primarygroup']];

$isroot = $usergroup['id'] == Settings::get('rootGroup');
$isbanned = $usergroup['id'] == Settings::get('bannedGroup');

if($editUserMode && $loguserid != $userid && $usergroup['rank'] > $loguserGroup['rank'])
	Kill(__("You may not edit a user whose rank is above yours."));

//Breadcrumbs
$uname = $user["name"];
if($user["displayname"])
	$uname = $user["displayname"];

makeCrumbs(array(actionLink("profile", $userid, "", $user["name"]) => htmlspecialchars($uname), '' => __("Edit profile")), "");

loadRanksets();
$ranksets = $ranksetNames;
$ranksets = array_reverse($ranksets);
$ranksets[""] = __("None");
$ranksets = array_reverse($ranksets);

foreach($dateformats as $format)
	$datelist[$format] = ($format ? $format.' ('.cdate($format).')':'');
foreach($timeformats as $format)
	$timelist[$format] = ($format ? $format.' ('.cdate($format).')':'');

$sexes = array(__("Male"), __("Female"), __("N/A"));

$groups = array();
$r = Query("SELECT id,title FROM {usergroups} WHERE type=0 AND rank<={0} ORDER BY rank", $loguserGroup['rank']);
while ($g = Fetch($r))
	$groups[$g['id']] = htmlspecialchars($g['title']);

//Editprofile.php: Welcome to the Hell of Nested Arrays!
$general = array(
	"appearance" => array(
		"name" => __("Appearance"),
		"items" => array(
			"displayname" => array(
				"caption" => __("Display name"),
				"type" => "text",
				"width" => "98%",
				"length" => 32,
				"hint" => __("Leave this empty to use your login name."),
				"callback" => "HandleDisplayname",
			),
			"rankset" => array(
				"caption" => __("Rankset"),
				"type" => "select",
				"options" => $ranksets,
			),
			"title" => array(
				"caption" => __("Title"),
				"type" => "text",
				"width" => "98%",
				"length" => 255,
			),
		),
	),
	"avatar" => array(
		"name" => __("Avatar"),
		"items" => array(
			"picture" => array(
				"caption" => __("Avatar"),
				"type" => "displaypic",
				"errorname" => "picture",
				"hint" => format(__("Maximum size is {0} by {0} pixels."), 100),
			),
			"minipic" => array(
				"caption" => __("Minipic"),
				"type" => "minipic",
				"errorname" => "minipic",
				"hint" => format(__("Maximum size is {0} by {0} pixels."), 16),
			),
		),
	),
	"presentation" => array(
		"name" => __("Presentation"),
		"items" => array(
			"threadsperpage" => array(
				"caption" => __("Threads per page"),
				"type" => "number",
				"min" => 50,
				"max" => 99,
			),
			"postsperpage" => array(
				"caption" => __("Posts per page"),
				"type" => "number",
				"min" => 20,
				"max" => 99,
			),
			"dateformat" => array(
				"caption" => __("Date format"),
				"type" => "datetime",
				"presets" => $datelist,
				"presetname" => "presetdate",
			),
			"timeformat" => array(
				"caption" => __("Time format"),
				"type" => "datetime",
				"presets" => $timelist,
				"presetname" => "presettime",
			),
			"fontsize" => array(
				"caption" => __("Font scale"),
				"type" => "number",
				"min" => 20,
				"max" => 200,
			),
		),
	),
	"options" => array(
		"name" => __("Options"),
		"items" => array(
			"blocklayouts" => array(
				"caption" => __("Block all layouts"),
				"type" => "checkbox",
			),
		),
	),
);

$personal = array(
	"personal" => array(
		"name" => __("Personal information"),
		"items" => array(
			"sex" => array(
				"caption" => __("Sex"),
				"type" => "radiogroup",
				"options" => $sexes,
			),
			"realname" => array(
				"caption" => __("Real name"),
				"type" => "text",
				"width" => "98%",
				"length" => 60,
			),
			"location" => array(
				"caption" => __("Location"),
				"type" => "text",
				"width" => "98%",
				"length" => 60,
			),
			"birthday" => array(
				"caption" => __("Birthday"),
				"type" => "birthday",
				"width" => "98%",
				"length" => 60,
				"extra" => "<span class=\"smallFonts\">".format(__("(example: {0})"), $birthdayExample)."</span>",
			),
			"bio" => array(
				"caption" => __("Bio"),
				"type" => "textarea",
			),
			"timezone" => array(
				"caption" => __("Timezone offset"),
				"type" => "timezone",
			),
		),
	),
	"contact" => array(
		"name" => __("Contact information"),
		"items" => array(
			"homepageurl" => array(
				"caption" => __("Homepage URL"),
				"type" => "text",
				"width" => "98%",
				"length" => 60,
			),
			"homepagename" => array(
				"caption" => __("Homepage name"),
				"type" => "text",
				"width" => "98%",
				"length" => 60,
			),
		),
	),
);

$account = array(
	"confirm" => array(
		"name" => __("Password confirmation"),
		"items" => array(
			"info" => array(
				"caption" => "",
				"type" => "label",
				"value" => __("Enter your password in order to edit account settings")
			),
			"currpassword" => array(
				"caption" => __("Password"),
				"type" => "passwordonce",
				"callback" => "",
			),
		),
	),
	"login" => array(
		"name" => __("Login information"),
		"items" => array(
			"name" => array(
				"caption" => __("User name"),
				"type" => "text",
				"length" => 20,
				"callback" => "HandleUsername",
			),
			"password" => array(
				"caption" => __("Password"),
				"type" => "password",
				"callback" => "HandlePassword",
			),
		),
	),
	"email" => array(
		"name" => __("Email information"),
		"items" => array(
			"email" => array(
				"caption" => __("Email address"),
				"type" => "text",
				"width" => "50%",
				"length" => 60,
			),
			"showemail" => array(
				"caption" => __("Make email public"),
				"type" => "checkbox",
			),
		),
	),
	"admin" => array(
		"name" => __("Administrative stuff"),
		"items" => array(
			"primarygroup" => array(
				"caption" => __("Primary group"),
				"type" => "select",
				"options" => $groups,
			),
			'dopermaban' => array(
				'caption' => __('Make ban permanent'),
				'type' => 'checkbox',
				'callback' => 'dummycallback',
			),
			"globalblock" => array(
				"caption" => __("Globally block layout"),
				"type" => "checkbox",
			),
			'flags' => array(
				'caption' => __('Misc. settings'),
				'type' => 'bitmask',
				'options' => array(0x1 => __('IP banned'), 0x2 => __('Errorbanned')),
			),
		),
	),
);

$layout = array(
	"postlayout" => array(
		"name" => __("Post layout"),
		"items" => array(
			"postheader" => array(
				"caption" => __("Header"),
				"type" => "textarea",
				"rows" => 16,
			),
			"signature" => array(
				"caption" => __("Footer"),
				"type" => "textarea",
				"rows" => 16,
			),
			"signsep" => array(
				"caption" => __("Show signature separator"),
				"type" => "checkbox",
				"negative" => true,
			),
		),
	),
);

//Allow plugins to add their own fields
$bucket = "edituser"; include("lib/pluginloader.php");

//Make some more checks.
if(!$editUserMode)
{
	if (($user['posts'] < Settings::get("customTitleThreshold") && !HasPermission('user.havetitle')) ||
		!HasPermission('user.edittitle'))
		unset($general['appearance']['items']['title']);
	
	$account['login']['items']['name']['type'] = "label";
	$account['login']['items']['name']['value'] = $user["name"];
	unset($account['admin']);
	
	if (!HasPermission('user.editdisplayname'))
		unset($general['appearance']['items']['displayname']);
	if (!HasPermission('user.editpostlayout'))
		unset($layout);
	if (!HasPermission('user.editbio'))
		unset($personal['personal']['items']['bio']);
	if (!HasPermission('user.editavatars'))
		unset($general['avatar']);
}
else
{
	if (!$isbanned || $user['tempbantime'] == 0)
		unset($account['admin']['items']['dopermaban']);
}

if($isroot && isset($account['admin']['items']['primarygroup']))
{
	if($isroot)
	{
		$account['admin']['items']['primarygroup']['type'] = "label";
		$account['admin']['items']['primarygroup']['value'] = htmlspecialchars($usergroup['title']);
	}
}

// Now that we have everything set up, we can link 'em into a set of tabs.
$tabs = array(
	"general" => array(
		"name" => __("General"),
		"page" => $general,
	),
	"personal" => array(
		"name" => __("Personal"),
		"page" => $personal,
	),
	"account" => array(
		"name" => __("Account settings"),
		"page" => $account,
	),
	"postlayout" => array(
		"name" => __("Post layout"),
		"page" => $layout,
	),
	"theme" => array(
		"name" => __("Theme"),
		"width" => "80%",
	),
);

/*
if (isset($_POST['theme']) && $user['id'] == $loguserid)
{
	$theme = $_POST['theme'];
	$themeFile = $theme.".css";
	if(!file_exists("css/".$themeFile))
		$themeFile = $theme.".php";
	$logopic = "img/themes/default/logo.png";
	if(file_exists("img/themes/".$theme."/logo.png"))
		$logopic = "img/themes/".$theme."/logo.png";
}*/

$_POST['action'] = (isset($_POST['action']) ? $_POST['action'] : "");

/* QUERY PART
 * ----------
 */

$failed = false;

if($_POST['action'] == __("Edit profile"))
{
	// catch spamvertisers early
	if ((time() - $user['regdate']) < 300 && preg_match('@^\w+\d+$@', $user['name']))
	{
		$lolbio = strtolower($_POST['bio']);
		
		if ((substr($lolbio,0,7) == 'http://'
				|| substr($lolbio,0,12) == '[url]http://'
				|| substr($lolbio,0,12) == '[url=http://')
			&& ((substr($_POST['email'],0,strlen($user['name'])) == $user['name'])
				|| (substr($user['name'],0,6) == 'iphone')))
		{
			Query("UPDATE {users} SET primarygroup={0}, title={1} WHERE id={2}",
				Settings::get('bannedGroup'), 'Spamvertising', $loguserid);
			
			die(header('Location: '.actionLink('index')));
		}
	}
	
	
	$passwordEntered = false;

	if($_POST["currpassword"] != "")
	{
		$sha = doHash($_POST["currpassword"].$salt.$loguser['pss']);
		if($loguser['password'] == $sha)
			$passwordEntered = true;
		else
		{
			Alert(__("Invalid password"));
			$failed = true;
			$selectedTab = "account";
			$tabs["account"]["page"]["confirm"]["items"]["currpassword"]["fail"] = true;
		}
	}

	$query = "UPDATE {$dbpref}users SET ";
	$sets = array();
	$pluginSettings = unserialize($user['pluginsettings']);

	foreach($tabs as $id => &$tab)
	{
		if(!isset($tab['page'])) continue;
		if($id == "account" && !$passwordEntered) continue;

		foreach($tab['page'] as $id => &$section)
		{
			foreach($section['items'] as $field => &$item)
			{
				if($item['callback'])
				{
					$ret = $item['callback']($field, $item);
					if($ret === true)
						continue;
					else if($ret != "")
					{
						Alert($ret, __('Error'));
						$failed = true;
						$selectedTab = $id;
						$item["fail"] = true;
					}
				}

				switch($item['type'])
				{
					case "label":
						break;
					case "text":
					case "textarea":
						$sets[] = $field." = '".SqlEscape($_POST[$field])."'";
						break;
					case "password":
						if($_POST[$field])
							$sets[] = $field." = '".SqlEscape($_POST[$field])."'";
						break;
					case "select":
						$val = $_POST[$field];
						if (array_key_exists($val, $item['options']))
							$sets[] = $field." = '".sqlEscape($val)."'";
						break;
					case "number":
						$num = (int)$_POST[$field];
						if($num < 1)
							$num = $item['min'];
						elseif($num > $item['max'])
							$num = $item['max'];
						$sets[] = $field." = ".$num;
						break;
					case "datetime":
						if($_POST[$item['presetname']] != -1)
							$_POST[$field] = $_POST[$item['presetname']];
						$sets[] = $field." = '".SqlEscape($_POST[$field])."'";
						break;
					case "checkbox":
						$val = (int)($_POST[$field] == "on");
						if($item['negative'])
							$val = (int)($_POST[$field] != "on");
						$sets[] = $field." = ".$val;
						break;
					case "radiogroup":
						if (array_key_exists($_POST[$field], $item['options']))
							$sets[] = $field." = '".SqlEscape($_POST[$field])."'";
						break;
					case "birthday":
						if($_POST[$field])
						{
							$val = @stringtotimestamp($_POST[$field]);
							if($val > time())
								$val = 0;
						}
						else
							$val = 0;
						$sets[] = $field." = '".$val."'";
						break;
					case "timezone":
						$val = ((int)$_POST[$field.'H'] * 3600) + ((int)$_POST[$field.'M'] * 60) * ((int)$_POST[$field.'H'] < 0 ? -1 : 1);
						$sets[] = $field." = ".$val;
						break;

					//TODO: These two are copypasta, fixit
					case "displaypic":
						if($_POST['remove'.$field])
						{
							@unlink($dataDir."avatars/$userid");
							$sets[] = $field." = ''";
							continue;
						}
						if($_FILES[$field]['name'] == "" || $_FILES[$field]['error'] == UPLOAD_ERR_NO_FILE)
							continue;
						$res = HandlePicture($field, 0, $item['errorname']);
						if($res === true)
							$sets[] = $field." = '#INTERNAL#'";
						else
						{
							Alert($res);
							$failed = true;
							$item["fail"] = true;
						}
						break;
					case "minipic":
						if($_POST['remove'.$field])
						{
							@unlink($dataDir."minipic/$userid");
							$sets[] = $field." = ''";
							continue;
						}
						if($_FILES[$field]['name'] == "" || $_FILES[$field]['error'] == UPLOAD_ERR_NO_FILE)
							continue;
						$res = HandlePicture($field, 1, $item['errorname']);
						if($res === true)
							$sets[] = $field." = '#INTERNAL#'";
						else
						{
							Alert($res);
							$failed = true;
							$item["fail"] = true;
						}
						break;
					case "bitmask":
						$val = 0;
						foreach ($_POST[$field] as $bit)
							if ($bit && array_key_exists($bit, $item['options']))
								$val |= $bit;
						$sets[] = $field." = ".(int)$val;
						break;
				}
			}
		}
	}

	//Force theme names to be alphanumeric to avoid possible directory traversal exploits ~Dirbaio
	if(preg_match("/^[a-zA-Z0-9_]+$/", $_POST['theme']))
		$sets[] = "theme = '".SqlEscape($_POST['theme'])."'";

	$sets[] = "pluginsettings = '".SqlEscape(serialize($pluginSettings))."'";
	if ($editUserMode && ((int)$_POST['primarygroup'] != $user['primarygroup'] || $_POST['dopermaban'])) 
	{
		$sets[] = "tempbantime = 0";
	}

	$query .= join($sets, ", ")." WHERE id = ".$userid;
	if(!$failed)
	{
		RawQuery($query);
		//if($loguserid == $userid)
		//	$loguser = Fetch(Query("select * from {users} where id={0}", $loguserid));

		$his = "[b]".$user['name']."[/]'s";
		if($loguserid == $userid)
			$his = HisHer($user['sex']);
		Report("[b]".$loguser['name']."[/] edited ".$his." profile. -> [g]#HERE#?uid=".$userid, 1);

		die(header("Location: ".actionLink("profile", $userid)));
	}
}

//If failed, get values from $_POST
//Else, get them from $user

foreach($tabs as &$tab)
{
	if(!isset($tab['page'])) continue;

	foreach($tab['page'] as &$section)
	{
		foreach($section['items'] as $field => &$item)
		{
			if ($item['type'] == "label" || $item['type'] == "password")
				continue;

			if(!$failed)
			{
				if(!isset($item["value"]))
					$item["value"] = $user[$field];
			}
			else
			{
				if ($item['type'] == 'checkbox')
					$item['value'] = ($_POST[$field] == 'on') ^ $item['negative'];
				elseif ($item['type'] == 'timezone')
					$item['value'] = ((int)$_POST[$field.'H'] * 3600) + ((int)$_POST[$field.'M'] * 60) * ((int)$_POST[$field.'H'] < 0 ? -1 : 1);
				elseif ($item['type'] == 'birthday')
					$item['value'] = @stringtotimestamp($_POST['birthday']);
				else
					$item['value'] = $_POST[$field];
			}
		}
		unset($item);
	}
	unset($section);
}
unset($tab);

if($failed)
	$loguser['theme'] = $_POST['theme'];

function dummycallback($field, $item)
{
	return true;
}

function HandlePicture($field, $type, $errorname)
{
	global $userid, $dataDir;
	if($type == 0)
	{
		$extensions = array(".png",".jpg",".gif");
		$maxDim = 200;
		$maxSize = 600 * 1024;
	}
	else if($type == 1)
	{
		$extensions = array(".png", ".gif");
		$maxDim = 16;
		$maxSize = 100 * 1024;
	}

	$fileName = $_FILES[$field]['name'];
	$fileSize = $_FILES[$field]['size'];
	$tempFile = $_FILES[$field]['tmp_name'];
	list($width, $height, $fileType) = getimagesize($tempFile);

	if ($type == 0 && ($width > 300 || $height > 300))
		return __("That avatar is definitely too big. The avatar field is meant for an avatar, not a wallpaper.");

	$extension = strtolower(strrchr($fileName, "."));
	if(!in_array($extension, $extensions))
		return format(__("Invalid extension used for {0}. Allowed: {1}"), $errorname, join($extensions, ", "));

	if($fileSize > $maxSize && !$allowOversize)
		return format(__("File size for {0} is too high. The limit is {1} bytes, the uploaded image is {2} bytes."), $errorname, $maxSize, $fileSize)."</li>";

	switch($fileType)
	{
		case 1:
			$sourceImage = imagecreatefromgif($tempFile);
			break;
		case 2:
			$sourceImage = imagecreatefromjpeg($tempFile);
			break;
		case 3:
			$sourceImage = imagecreatefrompng($tempFile);
			break;
	}

	$oversize = ($width > $maxDim || $height > $maxDim);
	if ($type == 0)
	{
		$targetFile = $dataDir."avatars/".$userid;

		if(!$oversize)
		{
			//Just copy it over.
			copy($tempFile, $targetFile);
		}
		else
		{
			//Resample that mother!
			$ratio = $width / $height;
			if($ratio > 1)
			{
				$targetImage = imagecreatetruecolor($maxDim, floor($maxDim / $ratio));
				imagecopyresampled($targetImage, $sourceImage, 0,0,0,0, $maxDim, $maxDim / $ratio, $width, $height);
			} else
			{
				$targetImage = imagecreatetruecolor(floor($maxDim * $ratio), $maxDim);
				imagecopyresampled($targetImage, $sourceImage, 0,0,0,0, $maxDim * $ratio, $maxDim, $width, $height);
			}
			imagepng($targetImage, $targetFile);
			imagedestroy($targetImage);
		}
	}
	elseif ($type == 1)
	{
		$targetFile = $dataDir."minipics/".$userid;

		if ($oversize)
		{
			//Don't allow minipics over $maxDim for anypony.
			return format(__("Dimensions of {0} must be at most {1} by {1} pixels."), $errorname, $maxDim);
		}
		else
			copy($tempFile, $targetFile);
	}
	return true;
}

// Special field-specific callbacks
function HandlePassword($field, $item)
{
	global $sets, $salt, $user, $loguser, $loguserid;
	if($_POST[$field] != "" && $_POST['repeat'.$field] != "" && $_POST['repeat'.$field] !== $_POST[$field])
	{
		return __("To change your password, you must type it twice without error.");
	}

	if($_POST[$field] != "" && $_POST['repeat'.$field] == "")
		$_POST[$field] = "";

	if($_POST[$field])
	{
		$newsalt = Shake();
		$sha = doHash($_POST[$field].$salt.$newsalt);
		$_POST[$field] = $sha;
		$sets[] = "pss = '".$newsalt."'";

		//Now logout all the sessions that aren't this one, for security.
		Query("DELETE FROM {sessions} WHERE id != {0} and user = {1}", doHash($_COOKIE['logsession'].$salt), $user["id"]);
	}

	return false;
}

function HandleDisplayname($field, $item)
{
	global $user;
	if(!IsReallyEmpty($_POST[$field]) || $_POST[$field] == $user['name'])
	{
		// unset the display name if it's really empty or the same as the login name.
		$_POST[$field] = "";
	}
	else
	{
		$dispCheck = FetchResult("select count(*) from {users} where id != {0} and (name = {1} or displayname = {1})", $user['id'], $_POST[$field]);
		if($dispCheck)
		{

			return format(__("The display name you entered, \"{0}\", is already taken."), SqlEscape($_POST[$field]));
		}
		else if($_POST[$field] !== ($_POST[$field] = preg_replace('/(?! )[\pC\pZ]/u', '', $_POST[$field])))
		{

			return __("The display name you entered cannot contain control characters.");
		}
	}
}

function HandleUsername($field, $item)
{
	global $user;
	if(!IsReallyEmpty($_POST[$field]))
		$_POST[$field] = $user[$field];

	$dispCheck = FetchResult("select count(*) from {users} where id != {0} and (name = {1} or displayname = {1})", $user['id'], $_POST[$field]);
	if($dispCheck)
	{

		return format(__("The login name you entered, \"{0}\", is already taken."), SqlEscape($_POST[$field]));
	}
	else if($_POST[$field] !== ($_POST[$field] = preg_replace('/(?! )[\pC\pZ]/u', '', $_POST[$field])))
	{

		return __("The login name you entered cannot contain control characters.");
	}
}


/* EDITOR PART
 * -----------
 */

//Dirbaio: Rewrote this so that it scans the themes dir.
$dir = "themes/";
$themeList = "";
$themes = array();

// Open a known directory, and proceed to read its contents
if (is_dir($dir))
{
    if ($dh = opendir($dir))
    {
        while (($file = readdir($dh)) !== false)
        {
            if(filetype($dir . $file) != "dir") continue;
            if($file == ".." || $file == ".") continue;
            $infofile = $dir.$file."/themeinfo.txt";

            if(file_exists($infofile))
            {
		        $themeinfo = file_get_contents($infofile);
		        $themeinfo = explode("\n", $themeinfo, 2);

		        $themes[$file]["name"] = trim($themeinfo[0]);
		        $themes[$file]["author"] = trim($themeinfo[1]);
		    }
		    else
		    {
		        $themes[$file]["name"] = $file;
		        $themes[$file]["author"] = "";
		    }
        }
        closedir($dh);
    }
}

asort($themes);

$themeList .= "
	<div style=\"text-align: right;\">
		<input type=\"text\" placeholder=\"".__("Search")."\" id=\"search\" onkeyup=\"searchThemes(this.value);\" />
	</div>";

foreach($themes as $themeKey => $themeData)
{
	$themeName = $themeData["name"];
	$themeAuthor = $themeData["author"];

	$qCount = "select count(*) from {users} where theme='".$themeKey."'";
	$numUsers = FetchResult($qCount);

	$preview = "themes/".$themeKey."/preview.png";
	if(!is_file($preview))
		$preview = "img/nopreview.png";
	$preview = resourceLink($preview);

	$preview = "<img src=\"".$preview."\" alt=\"".$themeName."\" style=\"margin-bottom: 0.5em\" />";

	if($themeAuthor)
		$byline = "<br />".nl2br($themeAuthor);
	else
		$byline = "";

	if($themeKey == $user['theme'])
		$selected = " checked=\"checked\"";
	else
		$selected = "";

	$themeList .= format(
"
	<div style=\"display: inline-block;\" class=\"theme\" title=\"{0}\">
		<input style=\"display: none;\" type=\"radio\" name=\"theme\" value=\"{3}\"{4} id=\"{3}\" onchange=\"ChangeTheme(this.value);\" />
		<label style=\"display: inline-block; clear: left; padding: 0.5em; {6} width: 260px; vertical-align: top\" onmousedown=\"void();\" for=\"{3}\">
			{2}<br />
			<strong>{0}</strong>
			{1}<br />
			{5}
		</label>
	</div>
",	$themeName, $byline, $preview, $themeKey, $selected, Plural($numUsers, "user"), "");
}

$pagewidth = 100;
$tempbanform = '';

if(!isset($selectedTab))
{
	$selectedTab = "general";
	foreach($tabs as $id => $tab)
	{
		if(isset($_GET[$id]))
		{
			$selectedTab = $id;
			break;
		}
	}
}

Write("<div class=\"margin width100\" id=\"tabs\">");
foreach($tabs as $id => $tab)
{
	if ($id != 'theme' && empty($tab['page']))
		continue;
		
	$selected = ($selectedTab == $id) ? " selected" : "";
	Write("
	<button id=\"{2}Button\" class=\"tab{1}\" onclick=\"showEditProfilePart('{2}');\">{0}</button>
	", $tab['name'], $selected, $id);
}
Write("
</div>
{0}
<form action=\"".actionLink("editprofile")."\" method=\"post\" enctype=\"multipart/form-data\">
", $tempbanform);

foreach($tabs as $id => $tab)
{
	if(isset($tab['page']) && !empty($tab['page']))
		BuildPage($tab['page'], $id);
	elseif($id == "theme")
		Write("
	<table class=\"outline margin eptable\" style=\"width:{$pagewidth}%;{1}\" id=\"{0}\">
		<tr class=\"header0\"><th>".__("Theme")."</th></tr>
		<tr class=\"cell0\"><td class=\"themeselector\">{2}</td></tr>
	</table>
",	$id, ($id != $selectedTab) ? " display: none;" : "",
	$themeList);
}

$editUserFields = "";
if($editUserMode)
{
	$editUserFields = format(
"
		<input type=\"hidden\" name=\"editusermode\" value=\"1\" />
		<input type=\"hidden\" name=\"userid\" value=\"{0}\" />
", $userid);
}

Write(
"
	<table class=\"outline margin\" style=\"width:{$pagewidth}%;\" id=\"button\">
		<tr class=\"header0\"><th colspan=\"2\">&nbsp;</th></tr>
		<tr class=\"cell1\"><td style=\"width:150px;\">&nbsp;</td><td>
		{2}
		<input type=\"submit\" id=\"submit\" name=\"action\" value=\"".__("Edit profile")."\" />
		<input type=\"hidden\" name=\"id\" value=\"{0}\" />
		<input type=\"hidden\" name=\"key\" value=\"{1}\" />
		</td></tr>
	</table>
</form>
", $id, $loguser['token'], $editUserFields);

function BuildPage($page, $id)
{
	global $selectedTab, $loguser, $pagewidth;

	//TODO: This should be done in JS.
	//So that a user who doesn't have Javascript will see all the tabs.
	$display = ($id != $selectedTab) ? " display: none;" : "";

	$cellClass = 0;
	$output = "<table class=\"outline margin eptable\" style=\"width:{$pagewidth}%; {$display}\" id=\"".$id."\">\n";
	foreach($page as $pageID => $section)
	{
		$output .= "<tr class=\"header0\"><th colspan=\"2\">".$section['name']."</th></tr>\n";
		foreach($section['items'] as $field => $item)
		{
			$output .= "<tr class=\"cell".$cellClass."\">\n";
			$output .= "<td style=\"width:150px;\" class=\"center\">\n";
			if(isset($item["fail"])) $output .= "[ERROR] ";
			if($item['type'] != "checkbox")
				$output .= "<label for=\"".$field."\">".$item['caption']."</label>\n";

			if(isset($item['hint']))
				$output .= "<img src=\"".resourceLink("img/icons/icon5.png")."\" title=\"".$item['hint']."\" alt=\"[?]\" />\n";
			$output .= "</td>\n";
			$output .= "<td>\n";

			if(isset($item['before']))
				$output .= " ".$item['before'];

			// Yes, some cases are missing the break; at the end.
			// This is intentional, but I don't think it's a good idea...
			switch($item['type'])
			{
				case "label":
					$output .= htmlspecialchars($item['value'])."\n";
					break;
				case "birthday":
					$item['type'] = "text";
					//$item['value'] = gmdate("F j, Y", $item['value']);
					$item['value'] = timestamptostring($item['value']);
				case "password":
					if($item['type'] == "password")
						$item['extra'] = "/ ".__("Repeat:")." <input type=\"password\" name=\"repeat".$field."\" size=\"".$item['size']."\" maxlength=\"".$item['length']."\" />";
				case "passwordonce":
					if(!isset($item['size']))
						$item['size'] = 13;
					if(!isset($item['length']))
						$item['length'] = 32;
					if($item["type"] == "passwordonce")
						$item["type"] = "password";
				case "text":
					$output .= "<input id=\"".$field."\" name=\"".$field."\" type=\"".$item['type']."\" value=\"".htmlspecialchars($item['value'])."\"";
					if(isset($item['size']))
						$output .= " size=\"".$item['size']."\"";
					if(isset($item['length']))
						$output .= " maxlength=\"".$item['length']."\"";
					if(isset($item['width']))
						$output .= " style=\"width: ".$item['width'].";\"";
					if(isset($item['more']))
						$output .= " ".$item['more'];
					$output .= " />\n";
					break;
				case "textarea":
					if(!isset($item['rows']))
						$item['rows'] = 8;
					$output .= "<textarea id=\"".$field."\" name=\"".$field."\" rows=\"".$item['rows']."\" style=\"width: 98%;\">".htmlspecialchars($item['value'])."</textarea>";
					break;
				case "checkbox":
					$output .= "<label><input id=\"".$field."\" name=\"".$field."\" type=\"checkbox\"";
					if((isset($item['negative']) && !$item['value']) || (!isset($item['negative']) && $item['value']))
						$output .= " checked=\"checked\"";
					$output .= " /> ".$item['caption']."</label>\n";
					break;
				case "select":
					$disabled = isset($item['disabled']) ? $item['disabled'] : false;
					$disabled = $disabled ? "disabled=\"disabled\" " : "";
					$checks = array();
					$checks[$item['value']] = " selected=\"selected\"";
					$options = "";
					foreach($item['options'] as $key => $val)
						$options .= format("<option value=\"{0}\"{1}>{2}</option>", $key, $checks[$key], $val);
					$output .= format("<select id=\"{0}\" name=\"{0}\" size=\"1\" {2}>\n{1}\n</select>\n", $field, $options, $disabled);
					break;
				case "radiogroup":
					$checks = array();
					$checks[$item['value']] = " checked=\"checked\"";
					foreach($item['options'] as $key => $val)
						$output .= format("<label><input type=\"radio\" name=\"{1}\" value=\"{0}\"{2} />{3}</label>", $key, $field, $checks[$key], $val);
					break;
				case "displaypic":
				case "minipic":
					$output .= "<input type=\"file\" id=\"".$field."\" name=\"".$field."\" style=\"width: 98%;\" />\n";
					$output .= "<label><input type=\"checkbox\" name=\"remove".$field."\" /> ".__("Remove")."</label>\n";
					break;
				case "number":
					//$output .= "<input type=\"number\" id=\"".$field."\" name=\"".$field."\" value=\"".$item['value']."\" />";
					$output .= "<input type=\"text\" id=\"".$field."\" name=\"".$field."\" value=\"".$item['value']."\" size=\"6\" maxlength=\"4\" />";
					break;
				case "datetime":
					$output .= "<input type=\"text\" id=\"".$field."\" name=\"".$field."\" value=\"".$item['value']."\" />\n";
					$output .= __("or preset:")."\n";
					$options = "<option value=\"-1\">".__("[select]")."</option>";
					foreach($item['presets'] as $key => $val)
						$options .= format("<option value=\"{0}\">{1}</option>", $key, $val);
					$output .= format("<select id=\"{0}\" name=\"{0}\" size=\"1\" >\n{1}\n</select>\n", $item['presetname'], $options);
					break;
				case "timezone":
					$output .= "<input type=\"text\" name=\"".$field."H\" size=\"2\" maxlength=\"3\" value=\"".(int)($item['value']/3600)."\" />\n";
					$output .= ":\n";
					$output .= "<input type=\"text\" name=\"".$field."M\" size=\"2\" maxlength=\"3\" value=\"".floor(abs($item['value']/60)%60)."\" />";
					break;
				case "bitmask":
					foreach($item['options'] as $key => $val)
						$output .= format("<label><input type=\"checkbox\" name=\"{1}[]\" value=\"{0}\"{2} /> {3}</label> &nbsp;", 
							$key, $field, ($item['value'] & $key) ? ' checked="checked"' : '', $val);
					break;
			}
			if(isset($item['extra']))
				$output .= " ".$item['extra'];

			$output .= "</td>\n";
			$output .= "</tr>\n";
			$cellClass = ($cellClass + 1) % 2;
		}
	}
	$output .= "</table>";
	Write($output);
}


function IsReallyEmpty($subject)
{
	$trimmed = trim(preg_replace("/&.*;/", "", $subject));
	return strlen($trimmed) != 0;
}

/*function Karma()
{
	global $userid;
	$votes = Query("select uid from {uservotes} where voter={0}", $userid);
	if(NumRows($votes))
		while($karmaChameleon = Fetch($votes))
			RecalculateKarma($karmaChameleon['uid']);
}*/

?>
<script type="text/javascript">
var homepagename = "<?php echo addslashes($tabs['personal']['page']['contact']['items']['homepagename']['value']); ?>";
setTimeout(function()
{
	// kill Firefox's dumb autofill
	$('#homepagename').val(homepagename);
	$('#currpassword').keyup();
}, 200);

$('#currpassword').keyup(function()
{
	var fields = $('#account').find('input:not(#currpassword),select');
	if (this.value == '')
		fields.attr('disabled', 'disabled');
	else
		fields.removeAttr('disabled');
});
</script>
