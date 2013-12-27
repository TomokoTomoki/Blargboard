<?php

$title = __("Ranks");
MakeCrumbs(array(actionLink("ranks") => __("Ranks")), $links);

loadRanksets();
if(count($ranksetData) == 0)
	Kill(__("No ranksets have been defined."));

if(!isset($_GET["id"]))
{
	$rankset = $loguser['rankset'];
	if(!$rankset || !isset($ranksetData[$rankset]))
	{
		$rankset = array_keys($ranksetData);
		$rankset = $rankset[0];
	}
	
	die(header("Location: ".actionLink("ranks", $rankset)));
}

$rankset = $_GET['id'];
if(!isset($ranksetData[$rankset]))
	Kill(__("Rankset not found."));

if(count($ranksetNames) > 1)
{
	$ranksets = new PipeMenu();
	foreach($ranksetNames as $name => $title)
		if($name == $rankset)
			$ranksets->add(new PipeMenuTextEntry($title));
		else
			$ranksets->add(new PipeMenuLinkEntry($title, "ranks", $name));


	echo "
		<table class=\"outline margin width100\">
			<tr class=\"header0\">
				<th colspan=\"2\">
					".__("Ranksets")."
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					".$ranksets->build()."
				</td>
		</table>";
}

$users = array();
$rUsers = Query("select u.(_userfields), u.(posts,lastposttime) from {users} u order by id asc");
while($user = Fetch($rUsers))
	$users[$user['u_id']] = getDataPrefix($user, "u_");

$ranks = $ranksetData[$rankset];

$ranklist = "";
for($i = 0; $i < count($ranks); $i++)
{
	$rank = $ranks[$i];
	$nextRank = $ranks[$i+1];
	if($nextRank['num'] == 0)
		$nextRank['num'] = $ranks[$i]['num'] + 1;
	$members = array(); $inactive = 0; $total = 0;
	foreach($users as $user)
	{
		if($user['posts'] >= $rank['num'] && $user['posts'] < $nextRank['num'])
		{
			$total++;
			if ($user['lastposttime'] > time() - 2592000)
				$members[] = UserLink($user);
			else
				$inactive++;
		}
	}
	if ($inactive)
		$members[] = $inactive.' inactive';
	
	$showRank = HasPermission('admin.viewallranks') || $loguser['posts'] >= $rank['num'] || count($members) > 0;
	if($showRank)
		$rankText = getRankHtml($rankset, $rank);
	else
		$rankText = '???';

	if(count($members) == 0)
		$members = '&nbsp;';
	else
		$members = join(', ', $members);

	$cellClass = ($cellClass+1) % 2;

	$ranklist .= format(
"
	<tr class=\"cell{0}\">
		<td class=\"cell2\">{1}</td>
		<td class=\"center\">{2}</td>
		<td class=\"center\">{4}</td>
		<td>{3}</td>
	</tr>
", $cellClass, $rankText, $rank['num'], $members, $total);
}
write(
"
<table class=\"width100 margin outline\">
	<tr class=\"header1\">
		<th>
			".__("Rank")."
		</th>
		<th>
			".__("Posts", 1)."
		</th>
		<th colspan=\"2\">
			".__("Users")."
		</th>
	</tr>
	{0}
</table>
",	$ranklist);

?>
