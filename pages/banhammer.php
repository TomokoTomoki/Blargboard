<?php

$title = 'Banhammer';

CheckPermission('admin.banusers');

$id = (int)$_GET['id'];
$user = Fetch(Query("SELECT u.(_userfields) FROM {users} u WHERE u.id={0}", $id));
if (!$user)
	Kill('Invalid user ID.');

if ($usergroups[$user['u_id']]['rank'] >= $loguserGroup['rank'])
	Kill('You may not ban a user whose level is equal to or above yours.');

if ($_POST['ban'])
{
	if ($_POST['token'] !== $loguser['token']) Kill('No.');
	
	$time = $_POST['time'] * $_POST['timemult'];
	if ($time > 0)
	{
		if ($time > 604800)
			$time = 604800;
		
		Query("update {users} set tempbanpl = {0}, tempbantime = {1}, primarygroup = {4}, title = {3} where id = {2}", 
			$user['u_primarygroup'], time()+$time, $id, $_POST['reason'], Settings::get('bannedGroup'));
		
		Report($loguser['name'].' banned '.$user['u_name'].' for '.($time > 86400 ? (ceil($time/86400).' days') : (ceil($time/3600).' hours')).
			($_POST['reason'] ? ': '.$_POST['reason']:'.'), true);
	}

	die(header('Location: '.actionLink('profile', $id)));
}

MakeCrumbs(array(actionLink("profile", $id) => htmlspecialchars($user['u_displayname']?$user['u_displayname']:$user['u_name']), 
	actionLink('banhammer', $id) => __('Banhammer')), '');

$userlink = userLink(getDataPrefix($user, 'u_'));

?>
	<form action="" method="POST">
		<table class="outline margin">
			<tr class="header1">
				<th colspan="2">Banhammer</th>
			</tr>
			<tr class="cell0">
				<td style="width:12%;" class="center">
					Target
				</td>
				<td>
					<?php echo $userlink; ?>
				</td>
			</tr>
			<tr class="cell2">
				<td class="center">
					Duration
				</td>
				<td>
					<input type="text" name="time" size="4" maxlength="2">
					<select name="timemult">
						<option value="3600">hours</option>
						<option value="86400">days</option>
					</select>
					<br><small>Maximum ban duration is 7 days. Longer bans should be discussed with the staff.</small>
				</td>
			</tr>
			<tr class="cell0">
				<td class="center">
					Reason
				</td>
				<td>
					<input type="text" name="reason" style="width:98%;" maxlength="200">
				</td>
			</tr>
			<tr class="cell2">
				<td>&nbsp;</td>
				<td>
					<input type="submit" name="ban" value="Ban user">
				</td>
			</tr>
		</table>
		<input type="hidden" name="token" value="<?php echo $loguser['token']; ?>">
	</form>