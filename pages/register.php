<?php
//  AcmlmBoard XD - User account registration page
//  Access: any, but meant for guests.

require('config/kurikey.php');


$title = __("Register");

$sexes = array(__("Male"), __("Female"), __("N/A"));

if($_POST['register'])
{
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$kuridata = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, md5($kurikey, true), base64_decode($_POST['kuridata']), MCRYPT_MODE_ECB, $iv);
	if (!$kuridata) Kill('Hack attempt detected');
	
	$kuridata = explode('|', $kuridata);
	if (count($kuridata) != 3) Kill('Hack attempt detected');
	$kuriseed = intval($kuridata[0]);
	$check = intval($kuridata[1]);
	$kurichallenge = $kuridata[2];
	$kurichallenge = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, md5($kurikey.$check, true), base64_decode($kurichallenge), MCRYPT_MODE_ECB, $iv);
	if (!$kurichallenge) Kill('Hack attempt detected');
	
	$kurichallenge = explode('|', $kurichallenge);
	if (count($kurichallenge) != 3) Kill('Hack attempt detected');
	if ($kurichallenge[0] != $kuridata[0]) Kill('Hack attempt detected');
	if ($kurichallenge[1] != $kuridata[1]) Kill('Hack attempt detected');
	
	$ngoombas = intval($kurichallenge[2]);
	if ($ngoombas != (int)$_POST['kurichallenge'])
		$err = __('You failed the challenge. Look harder.');
	else
	{
		$name = $_POST['name'];
		$cname = trim(str_replace(" ","", strtolower($name)));
		
		$failure = 'eyelyke2cheet';
		if (substr(strtolower($cname), 0, strlen($failure)) == $failure)
		{
			Query("INSERT INTO {ipbans} (ip,reason,date) VALUES ({0},{1},{2})", $_SERVER['REMOTE_ADDR'], '[eyeamamoron] please die already', 0);
			die(header('Location: index.php'));
		}

		$rUsers = Query("select name, displayname from {users}");
		while($user = Fetch($rUsers))
		{
			$uname = trim(str_replace(" ", "", strtolower($user['name'])));
			if($uname == $cname)
				break;
			$uname = trim(str_replace(" ", "", strtolower($user['displayname'])));
			if($uname == $cname)
				break;
		}

		$ipKnown = FetchResult("select COUNT(*) from {users} where lastip={0}", $_SERVER['REMOTE_ADDR']);

		if (stripos($_POST['email'], '@dispostable.com') !== FALSE)
			$err = __('Registration failed. Try again later.');
		elseif($uname == $cname)
			$err = __("This user name is already taken. Please choose another.");
		else if($name == "" || $cname == "")
			$err = __("The user name must not be empty. Please choose one.");
		elseif($ipKnown >= 3)
			$err = __("Another user is already using this IP address.");
		else if(!$_POST['readFaq'])
			$err = format(__("You really should {0}read the FAQ{1}&hellip;"), "<a href=\"".actionLink("faq")."\">", "</a>");
		else if ($_POST['likesCake'])
			$err = __("Robots not allowed.");
		else if(Settings::get("registrationWord") != "" && strcasecmp($_POST['theWord'], Settings::get("registrationWord")))
			$err = format(__("That's not the right word. Are you sure you really {0}read the FAQ{1}?"), "<a href=\"".actionLink("faq")."\">", "</a>");
		else if(strlen($_POST['pass']) < 4)
			$err = __("Your password must be at least four characters long.");
		else if ($_POST['pass'] !== $_POST['pass2'])
			$err = __("The passwords you entered don't match.");
		else if (preg_match("@^(MKDS|MK7|SM64DS|SMG|NSMB)\d*?@si", $uname))
			$err = __("Come on, you could be a little more original with your username!");
	}

	if($err)
	{
		Alert($err, __('Error'));
	}
	else
	{
		$newsalt = Shake();
		$sha = doHash($_POST['pass'].$salt.$newsalt);
		$uid = FetchResult("SELECT id+1 FROM {users} WHERE (SELECT COUNT(*) FROM {users} u2 WHERE u2.id={users}.id+1)=0 ORDER BY id ASC LIMIT 1");
		if($uid < 1) $uid = 1;

		$rUsers = Query("insert into {users} (id, name, password, pss, regdate, lastactivity, lastip, email, sex, theme) values ({0}, {1}, {2}, {3}, {4}, {4}, {5}, {6}, {7}, {8})", $uid, $_POST['name'], $sha, $newsalt, time(), $_SERVER['REMOTE_ADDR'], $_POST['email'], (int)$_POST['sex'], Settings::get("defaultTheme"));

		if($uid == 1)
			Query("update {users} set primarygroup = {0} where id = 1", Settings::get('rootGroup'));

		Report("New user: [b]".$_POST['name']."[/] (#".$uid.") -> [g]#HERE#?uid=".$uid);

		$user = Fetch(Query("select * from {users} where id={0}", $uid));
		$user["rawpass"] = $_POST["pass"];

		$bucket = "newuser"; include("lib/pluginloader.php");
		
		
		$rLogUser = Query("select id, pss, password from {users} where 1");
		$matches = array();

		while($testuser = Fetch($rLogUser))
		{
			if($testuser["id"] == $user["id"])
				continue;

			$sha = doHash($_POST['pass'].$salt.$testuser['pss']);
			if($testuser['password'] === $sha)
				$matches[] = $testuser['id'];
		}
		
		if (count($matches) > 0)
			Query("INSERT INTO {passmatches} (date,ip,user,matches) VALUES (UNIX_TIMESTAMP(),{0},{1},{2})", $_SERVER['REMOTE_ADDR'], $user['id'], implode(',',$matches));
		
		// END SPY CODE
		
		
		// mark threads older than 15min as read
		Query("INSERT INTO {threadsread} (id,thread,date) SELECT {0}, id, {1} FROM {threads} WHERE lastpostdate<={2}", $uid, time(), time()-900);


		if($_POST['autologin'])
		{
			$sessionID = Shake();
			setcookie("logsession", $sessionID, 0, $boardroot, "", false, true);
			Query("INSERT INTO {sessions} (id, user, autoexpire) VALUES ({0}, {1}, {2})", doHash($sessionID.$salt), $user["id"], 0);
			die(header("Location: ."));
		}
		else
			die(header("Location: ".actionLink("login")));
	}
}
else
{
	$_POST['name'] = '';
	$_POST['email'] = '';
	$_POST['sex'] = 2;
	$_POST['autologin'] = 0;
}


$kuriseed = crc32($kurikey.microtime());
srand($kuriseed);
$check = rand();
$kurichallenge = "{$kuriseed}|{$check}|".rand(3,12);

$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
$kurichallenge = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, md5($kurikey.$check, true), $kurichallenge, MCRYPT_MODE_ECB, $iv);
$kurichallenge = base64_encode($kurichallenge);
$kuridata = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, md5($kurikey, true), "{$kuriseed}|{$check}|{$kurichallenge}", MCRYPT_MODE_ECB, $iv);
$kuridata = base64_encode($kuridata);


write(
"
	<form action=\"".actionLink("register")."\" method=\"post\">
		<table class=\"outline margin width100\">
			<tr class=\"header0\">
				<th colspan=\"2\">
					".__("Register")."
				</th>
			</tr>
			<tr>
				<td class=\"cell2 center\" style=\"width:15%; max-width:150px;\">
					<label for=\"un\">".__("User name")."</label>
				</td>
				<td class=\"cell0\">
					<input type=\"text\" id=\"un\" name=\"name\" maxlength=\"20\" style=\"width: 98%;\" value=\"".htmlspecialchars($_POST['name'])."\" class=\"required\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2 center\">
					<label for=\"pw\">".__("Password")."</label>
				</td>
				<td class=\"cell1\">
					<input type=\"password\" id=\"pw\" name=\"pass\" size=\"13\" maxlength=\"32\" class=\"required\" /> / ".__("Repeat:")." <input type=\"password\" id=\"pw2\" name=\"pass2\" size=\"13\" maxlength=\"32\" class=\"required\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2 center\">
					<label for=\"email\">".__("Email address")."</label>
				</td>
				<td class=\"cell0\">
					<input type=\"email\" id=\"email\" name=\"email\" value=\"".htmlspecialchars($_POST['email'])."\" style=\"width: 98%;\" maxlength=\"60\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2 center\">
					".__("Gender")."
				</td>
				<td class=\"cell1\">
					{0}
				</td>
			</tr>
			<tr>
				<td class=\"cell2\"></td>
				<td class=\"cell0\">
					<label>
						<input type=\"checkbox\" name=\"readFaq\" />
						".format(__("I have read the {0}FAQ{1}"), "<a href=\"".actionLink("faq")."\">", "</a>")."
					</label>
				</td>
			</tr>
", MakeOptions("sex",$_POST['sex'],$sexes));
	
write(
"
			<tr>
				<td class=\"cell2 center\">
					".__("How many Goombas are there?")."
				</td>
				<td class=\"cell1\">
					<img src=\"".resourceLink("kurichallenge.php?data=".urlencode($kuridata))."\" alt=\"[reload the page if the image fails to load]\" /><br>
					<input type=\"text\" name=\"kurichallenge\" size=\"10\" maxlength=\"6\" class=\"required\" />
					<input type=\"hidden\" name=\"kuridata\" value=\"".htmlspecialchars($kuridata)."\" />
				</td>
			</tr>
");

write(
"
			<tr class=\"cell2\">
				<td></td>
				<td>
					<input type=\"submit\" name=\"register\" value=\"".__("Register")."\"/>
					<label>
						<input type=\"checkbox\" checked=\"checked\" name=\"autologin\"".($_POST['autologin']?' checked="checked"':'')." />
						".__("Log in afterwards")."
					</label>
				</td>
			</tr>
			<tr>
				<td colspan=\"2\" class=\"cell0 smallFonts\">
					".__("Specifying an email address is not exactly a hard requirement, but it will allow you to reset your password should you forget it. By default, your email is made private.")."
				</td>
			</tr>
		</table>
		<span style=\"display : none;\"><input type=\"checkbox\" name=\"likesCake\" /> I am a robot</span>
	</form>
");


function MakeOptions($fieldName, $checkedIndex, $choicesList)
{
	$checks[$checkedIndex] = " checked=\"checked\"";
	foreach($choicesList as $key=>$val)
		$result .= format("
					<label>
						<input type=\"radio\" name=\"{1}\" value=\"{0}\"{2} />
						{3}
					</label>", $key, $fieldName, $checks[$key], $val);
	return $result;
}
?>
