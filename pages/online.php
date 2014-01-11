<?php
//  AcmlmBoard XD - Realtime visitor statistics page
//  Access: all

$title = __("Online users");
MakeCrumbs(array(actionLink("online") => __("Online users")), $links);

$showIPs = HasPermission('admin.viewips');

$time = (int)$_GET['time'];
if(!$time) $time = 300;

$rUsers = Query("select * from {users} where lastactivity > {0} order by lastactivity desc", (time()-$time));
$rGuests = Query("select * from {guests} where date > {0} and bot = 0 order by date desc", (time()-$time));
$rBots = Query("select * from {guests} where date > {0} and bot = 1 order by date desc", (time()-$time));

$spans = array(60, 300, 900, 3600, 86400);
$spanList = "";
foreach($spans as $span)
{
	$spanList .= actionLinkTagItem(timeunits($span), "online", "", "time=$span");
}
write(
"
	<div class=\"smallFonts margin\">
		".__("Show visitors from this far back:")."
		<ul class=\"pipemenu\">
			{0}
		</ul>
	</div>
", $spanList);


$userList = "";
$i = 1;
if(NumRows($rUsers))
{
	while($user = Fetch($rUsers))
	{
		$cellClass = ($cellClass+1) % 2;
		if($user['lasturl'])
			$lastUrl = "<a href=\"".FilterURL($user['lasturl'])."\">".FilterURL($user['lasturl'])."</a>";
		else
			$lastUrl = __("None");

		$userList .= "
		<tr class=\"cell$cellClass\">
			<td>$i</td>
			<td>".UserLink($user)."</td>
			<td>".($user['lastposttime'] ? cdate("d-m-y G:i:s",$user['lastposttime']) : __("Never"))."</td>
			<td>".cdate("d-m-y G:i:s", $user['lastactivity'])."</td>
			<td>$lastUrl</td>";
		if($showIPs) $userList .= "<td>".formatIP($user['lastip'])."</td>";
		$userList .= "</tr>";

		$i++;
	}
}
else
	$userList = "<tr class=\"cell0\"><td colspan=\"".($showIPs?'6':'5')."\">".__("No users")."</td></tr>";



function listGuests($rGuests, $noMsg)
{
	global $showIPs;
	
	if(!NumRows($rGuests))
		return "<tr class=\"cell0\"><td colspan=\"".($showIPs?'6':'5')."\">$noMsg</td></tr>";
		
	$guestList = '';
	$i = 1;
	while($guest = Fetch($rGuests))
	{
		$cellClass = ($cellClass+1) % 2;
		if($guest['date'])
			$lastUrl = "<a href=\"".FilterURL($guest['lasturl'])."\">".FilterURL($guest['lasturl'])."</a>";
		else
			$lastUrl = __("None");

		$guestList .= format(
"
		<tr class=\"cell{0}\">
			<td>{1}</td>
			".($showIPs?"<td title=\"{2}\" colspan=\"2\">{3}</td>":'<td colspan="2"></td>')."
			<td>{4}</td>
			<td>{5}</td>
			".($showIPs?"<td>{6}</td>":'')."
		</tr>
",	$cellClass, $i, htmlspecialchars($guest['useragent']),
	htmlspecialchars(substr($guest['useragent'], 0, 65)), cdate("d-m-y G:i:s", $guest['date']),
	$lastUrl, formatIP($guest['ip']));
		$i++;
	}
	
	return $guestList;
}

$guestList = listGuests($rGuests, __("No guests"));
$botList = listGuests($rBots, __("No bots"));

write(
"
	<table class=\"outline margin\">
		<tr class=\"header0\">
			<th colspan=\"".($showIPs?'6':'5')."\">
				".__("Online users")."
			</th>
		</tr>
		<tr class=\"header1\">
			<th style=\"width: 30px;\">
				#
			</th>
			<th>
				".__("Name")."
			</th>
			<th style=\"width: 140px;\">
				".__("Last post")."
			</th>
			<th style=\"width: 140px;\">
				".__("Last view")."
			</th>
			<th>
				".__("URL")."
			</th>
".($showIPs ? "
			<th style=\"width: 140px;\">
				".__("IP")."
			</th>
" : "")."
		</tr>
		{0}
		<tr class=\"header0\">
			<th colspan=\"".($showIPs?'6':'5')."\">
				".__("Guests")."
			</th>
		</tr>
		{1}
		<tr class=\"header0\">
			<th colspan=\"".($showIPs?'6':'5')."\">
				".__("Bots")."
			</th>
		</tr>
		{2}
	</table>
", $userList, $guestList, $botList);

function FilterURL($url)
{
	//$url = str_replace('_', ' ', urldecode($url)); // what?
	$url = htmlspecialchars($url);
	$url = preg_replace("@(&amp;)?(key|token)=[0-9a-f]{40,64}@i", '', $url);
	return $url;
}

?>
