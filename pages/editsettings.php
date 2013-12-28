<?php
//  AcmlmBoard XD - Board Settings editing page
//  Access: administrators

$title = __("Edit settings");

CheckPermission('admin.editsettings');

$plugin = "main";
if(isset($_GET["id"]))
	$plugin = $_GET["id"];
if(isset($_POST["_plugin"]))
	$plugin = $_POST["_plugin"];

if(!ctype_alnum($plugin))
	Kill(__("No."));

if($plugin == "main")
	MakeCrumbs(array(actionLink("admin") => __("Admin"), actionLink("editsettings") => __("Edit settings")), "");
else
	MakeCrumbs(array(actionLink("admin") => __("Admin"), actionLink("pluginmanager") => __("Plugin manager"), '' => $plugins[$plugin]["name"]), "");

$settings = Settings::getSettingsFile($plugin);
$oursettings = Settings::$settingsArray[$plugin];
$invalidsettings = array();

if(isset($_POST["_plugin"]))
{
	//Save the settings.
	$valid = true;

	foreach($_POST as $key => $value)
	{
		if($key == "_plugin") continue;

		//Don't accept unexisting settings.
		if(!isset($settings[$key])) continue;
		
		// don't save settings if the user isn't allowed to change them
		if ($settings[$key]['rootonly'] && !$loguser['root'])
			continue;

		//Save the entered settings for re-editing
		$oursettings[$key] = $value;

		if(!Settings::validate($value, $settings[$key]["type"], $settings[$key]["options"]))
		{
			$valid = false;
			$invalidsettings[$key] = true;
		}
		else
			Settings::$settingsArray[$plugin][$key] = $value;
	}

	if($valid)
	{
		Settings::save($plugin);
		if(isset($_POST["_exit"]))
		{
			if($plugin == "main")
				die(header("Location: ".actionLink("admin")));
			else
				die(header("Location: ".actionLink("pluginmanager")));
		}
		else
			Alert(__("Settings were successfully saved!"));
	}
	else
		Alert(__("Settings were not saved because there were invalid values. Please correct them and try again."));
}

$plugintext = "";
if($plugin != "main")
	$plugintext = " for plugin ".$plugin;
print "
	<form action=\"".actionLink("editsettings")."\" method=\"post\">
		<input type=\"hidden\" name=\"_plugin\" value=\"$plugin\">
		<table class=\"outline margin width100\">

			<tr class=\"header1\">
				<th colspan=\"2\">
					".__("Settings")."$plugintext
				</th>
			</tr>";

$settingfields = array();
$settingfields[''] = ''; // ensures the uncategorized entries come first

foreach($settings as $name => $data)
{
	if ($data['rootonly'] && !$loguser['root'])
		continue;
		
	$friendlyname = $name;
	if(isset($data["name"]))
		$friendlyname = $data["name"];

	$type = $data["type"];
	$help = $data["help"];
	$options = $data["options"];
	$value = $oursettings[$name];

	$input = "[Bad setting type]";

	$value = htmlspecialchars($value);

	if($type == "boolean")
		$input = makeSelect($name, $value, array(1=>"Yes", 0=>"No"));
	else if($type == "options")
		$input = makeSelect($name, $value, $options);
	else if($type == "integer" || $type == "float")
		$input = "<input type=\"text\" id=\"$name\" name=\"$name\" value=\"$value\" />";
	else if($type == "text")
		$input = "<input type=\"text\" id=\"$name\" name=\"$name\" value=\"$value\" class=\"width75\"/>";
	else if($type == "password")
		$input = "<input type=\"password\" id=\"$name\" name=\"$name\" value=\"$value\" class=\"width75\"/>";
	else if($type == "textbox" || $type == "textbbcode" || $type == "texthtml")
		$input = "<textarea id=\"$name\" name=\"$name\" rows=\"8\" style=\"width: 98%;\">$value</textarea>";
	else if($type == "forum")
		$input = makeForumList($name, $value);
	else if ($type == 'group')
		$input = makeSelect($name, $value, $grouplist);
	else if($type == "theme")
		$input = makeThemeList($name, $value);
	else if($type == "layout")
		$input = makeLayoutList($name, $value);
	else if($type == "language")
		$input = makeLangList($name, $value);

	$invalidicon = "";
	if($invalidsettings[$name])
		$invalidicon = "[INVALID]";

	if($help)
		$help = "<br><small>$help</small>";

	$settingfields[$data['category']] .= "<tr class=\"cell0\">
				<td class=\"cell1 center\">
					<label for=\"$name\">$friendlyname</label>$help
				</td>
				<td>
					$input
					$invalidicon
				</td>
			</tr>";
}

foreach ($settingfields as $cat=>$fields)
{
	if ($cat) echo '<tr class="header1"><th colspan=2>'.htmlspecialchars($cat).'</th></tr>';
	
	echo $fields;
}

print "			<tr class=\"header1\"><th colspan=2>&nbsp;</th></tr>
				<tr class=\"cell2\">
				<td style=\"width:20%;\">
				</td>
				<td>
					<input type=\"submit\" name=\"_exit\" value=\"".__("Save and Exit")."\" />
					<input type=\"submit\" name=\"_action\" value=\"".__("Save")."\" />
					<input type=\"hidden\" name=\"key\" value=\"{31}\" />
				</td>
			</tr>
		</table>
	</form>
";

function makeSelect($fieldName, $checkedIndex, $choicesList, $extras = "")
{
	$checks[$checkedIndex] = " selected=\"selected\"";
	foreach($choicesList as $key=>$val)
		$options .= format("
						<option value=\"{0}\"{1}>{2}</option>", $key, $checks[$key], $val);
	$result = format(
"
					<select id=\"{0}\" name=\"{0}\" size=\"1\" {1} >{2}
					</select>", $fieldName, $extras, $options);
	return $result;
}

function prepare($text)
{
	$s = str_replace("\\'", "'", addslashes($text));
	return $s;
}


function makeThemeList($fieldname, $value)
{
	$themes = array();
	$dir = @opendir("themes");
	while ($file = readdir($dir))
	{
		if ($file != "." && $file != "..")
		{
			$name = explode("\n", @file_get_contents("./themes/".$file."/themeinfo.txt"));
			$themes[$file] = trim($name[0]);
		}
	}
	closedir($dir);
	return makeSelect($fieldname, $value, $themes);
}

function makeLayoutList($fieldname, $value)
{
	$layouts = array();
	$dir = @opendir("layouts");
	while ($file = readdir($dir))
	{
		if (endsWith($file, ".php"))
		{
			$layout = substr($file, 0, strlen($file)-4);
			$layouts[$layout] = @file_get_contents("./layouts/".$layout.".info.txt");
		}
	}
	closedir($dir);
	return makeSelect($fieldname, $value, $layouts);
}

function makeLangList($fieldname, $value)
{
	$data = array();
	$dir = @opendir("lib/lang");
	while ($file = readdir($dir))
	{
		//print $file;
		if (endsWith($file, "_lang.php"))
		{
			$file = substr($file, 0, strlen($file)-9);
			$data[$file] = $file;
		}
	}
	$data["en_US"] = "en_US";
	closedir($dir);
	return makeSelect($fieldname, $value, $data);
}

//From the PHP Manual User Comments
function foldersize($path)
{
	$total_size = 0;
	$files = scandir($path);
	$files = array_slice($files, 2);
	foreach($files as $t)
	{
		if(is_dir($t))
		{
			//Recurse here
			$size = foldersize($path . "/" . $t);
			$total_size += $size;
		}
		else
		{
			$size = filesize($path . "/" . $t);
			$total_size += $size;
		}
	}
	return $total_size;
}

?>
