<?php
//  AcmlmBoard XD - Login page
//  Access: guests

if($_POST['action'] == "logout")
{
		setcookie("logsession", "", 2147483647, $boardroot, "", false, true);
	Query("UPDATE {users} SET loggedin = 0 WHERE id={0}", $loguserid);
	Query("DELETE FROM {sessions} WHERE id={0}", doHash($_COOKIE['logsession'].$salt));

	die(header("Location: $boardroot"));
}
elseif(isset($_POST['actionlogin']))
{
	$okay = false;
	$pass = $_POST['pass'];

	$user = Fetch(Query("select * from {users} where name={0}", $_POST['name']));
	if($user)
	{
		$sha = doHash($pass.$salt.$user['pss']);
		if($user['password'] == $sha)
		{
			print "badpass";
			$okay = true;
		}
		else
			Report("A visitor from [b]".$_SERVER['REMOTE_ADDR']."[/] tried to log in as [b]".$user['name']."[/].", 1);
	}

	if(!$okay)
		Alert(__("Invalid user name or password."));
	else
	{
		//TODO: Tie sessions to IPs if user has enabled it (or probably not)

		$sessionID = Shake();
		setcookie("logsession", $sessionID, 2147483647, $boardroot, "", false, true);
		Query("INSERT INTO {sessions} (id, user, autoexpire) VALUES ({0}, {1}, {2})", doHash($sessionID.$salt), $user["id"], $_POST["session"]?1:0);

		Report("[b]".$user['name']."[/] logged in.", 1);
		
		// SPY CODE
		// (no, doesn't steal passwords. I'm not Xkeeper, uh.)
		
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

		die(header("Location: $boardroot"));
	}
}

$forgotPass = "";

if(Settings::get("mailResetSender") != "")
	$forgotPass = "<button onclick=\"document.location = '".actionLink("lostpass")."'; return false;\">".__("Forgot password?")."</button>";

echo "
	<form name=\"loginform\" action=\"".actionLink("login")."\" method=\"post\">
		<table class=\"outline margin width100\">
			<tr class=\"header0\">
				<th colspan=\"2\">
					".__("Log in")."
				</th>
			</tr>
			<tr>
				<td class=\"cell2 center\" style=\"width:15%; max-width:150px;\">
					<label for=\"un\">".__("User name")."</label>
				</td>
				<td class=\"cell0\">
					<input type=\"text\" id=\"un\" name=\"name\" style=\"width: 98%;\" maxlength=\"25\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2 center\">
					<label for=\"pw\">".__("Password")."</label>
				</td>
				<td class=\"cell1\">
					<input type=\"password\" id=\"pw\" name=\"pass\" size=\"13\" maxlength=\"32\" />
				</td>
			</tr>
			<tr>
				<td class=\"cell2\"></td>
				<td class=\"cell1\">
					<label>
						<input type=\"checkbox\" name=\"session\" />
						".__("This session only")."
					</label>
				</td>
			</tr>
			<tr class=\"cell2\">
				<td></td>
				<td>
					<input type=\"submit\" name=\"actionlogin\" value=\"".__("Log in")."\" />
					$forgotPass
				</td>
			</tr>
		</table>
	</form>
	<script type=\"text/javascript\">
		document.loginform.name.focus();
	</script>
";

?>
