<?php
//  AcmlmBoard XD - Private message inbox/outbox viewer
//  Access: users

$title = "Private messages";

if(!$loguserid)
	Kill(__("You must be logged in to view your private messages."));

$user = $loguserid;
if(isset($_GET['user']) && HasPermission('admin.viewpms'))
{
	$user = (int)$_GET['user'];
	$snoop = "&snooping=1";
	$userGet = "&user=".$user;
}

if(isset($_POST['action']))
{
	if ($_POST['token'] !== $loguser['token']) Kill('No.');
	
	if($_POST['action'] == "multidel" && $_POST['delete'] && $snoop != 1)
	{
		$deleted = 0;
		foreach($_POST['delete'] as $pid => $on)
		{
			$rPM = Query("select * from {pmsgs} where id = {0} and (userto = {1} or userfrom = {1})", $pid, $loguserid);
			if(NumRows($rPM))
			{
				$pm = Fetch($rPM);
				$val = $pm['userto'] == $loguserid ? 2 : 1;
				$newVal = ($pm['deleted'] | $val);
				/*if($newVal == 3)
				{
					Query("delete from {pmsgs} where id = {0}", $pid);
					Query("delete from {pmsgs_text} where pid = {0}", $pid);
				}
				else*/
					Query("update {pmsgs} set deleted = {0} where id = {1}", $newVal, $pid);
				$deleted++;
			}
		}
		Alert(format(__("{0} deleted."), Plural($deleted, __("private message"))));
	}
}

if(isset($_GET['del']))
{
	if ($_GET['token'] !== $loguser['token']) Kill('No.');
	
	$pid = (int)$_GET['del'];
	$rPM = Query("select * from {pmsgs} where id = {0} and (userto = {1} or userfrom = {1})", $pid, $loguserid);
	if(NumRows($rPM))
	{
		$pm = Fetch($rPM);
		$val = $pm['userto'] == $loguserid ? 2 : 1;
		$newVal = ($pm['deleted'] | $val);
		/*if($newVal == 3)
		{
			Query("delete from {pmsgs} where id = {0}", $pid);
			Query("delete from {pmsgs_text} where pid = {0}", $pid);
		}
		else*/
			Query("update {pmsgs} set deleted = {0} where id = {1}", $newVal, $pid);
		Alert(__("Private message deleted."));
	}
}

$whereFrom = "userfrom = {0}";
$drafting = 0;
$deleted = 2;
$staffpms = '';
if(isset($_GET['show']))
{
	$show = "&show=".(int)$_GET['show'];
	if($_GET['show'] == 1)
		$deleted = 1;
	else if($_GET['show'] == 2)
		$drafting = 1;
	$onclause = 'userto';
}
else
{
	$whereFrom = "userto = {0}";
	if (HasPermission('admin.viewstaffpms') && $user==$loguserid) $staffpms = ' OR userto={4}';
	$onclause = 'userfrom';
}
$whereFrom .= " and drafting = ".$drafting;

$total = FetchResult("select count(*) from {pmsgs} where ({$whereFrom}{$staffpms}) and deleted != {1}", $user, $deleted, null, null, -1);

$ppp = $loguser['postsperpage'];

if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;


$links = '';

$links .= actionLinkTagItem(__("Show received"), "private", "", str_replace("&", "", $userGet));
$links .= actionLinkTagItem(__("Show sent"), "private", "", "show=1".$userGet);
$links .= actionLinkTagItem(__("Show drafts"), "private", "", "show=2".$userGet);
$links .= actionLinkTagItem(__("Send new PM"), "sendprivate");

MakeCrumbs(array(actionLink("private") => __("Private messages")), $links);

$rPM = Query("select {pmsgs}.*,{pmsgs_text}.*,u.(_userfields) 
	from {pmsgs} left join {pmsgs_text} on pid = {pmsgs}.id LEFT JOIN {users} u ON u.id={$onclause}
	where (".$whereFrom.$staffpms.") and deleted != {1} order by date desc limit {2u}, {3u}", $user, $deleted, $from, $ppp, -1);
$numonpage = NumRows($rPM);

$pagelinks = PageLinks(actionLink("private", "", "$show$userGet&from="), $ppp, $from, $total);

if($pagelinks)
	write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

if(NumRows($rPM))
{
	while($pm = Fetch($rPM))
	{
		$user = getDataPrefix($pm, 'u_');

		$cellClass = ($cellClass+1) % 2;
		if(!$pm['msgread'])
			$img = "<div class=\"statusIcon new\"></div>";
		else
			$img = "";

		if ($_GET['show'] && $pm['userto'] == -1)
			$sender = 'Staff';
		else
			$sender = UserLink($user);

		$check = $snoop ? "" : "<input type=\"checkbox\" name=\"delete[{2}]\" />";

		$delLink = $snoop == "" ? "<sup>&nbsp;".actionLinkTag("&#x2718;", "private", "", "del=".$pm['id'].$show.'&token='.$loguser['token'])."</sup>" : "";

		$pms .= format(
"
		<tr class=\"cell{0}\">
			<td>
				".$check."
			</td>
			<td class=\"center\">
				{1}
			</td>
			<td>
				".actionLinkTag(htmlspecialchars($pm['title']), "showprivate", $pm['id'], $snoop)."{7}
			</td>
			<td class=\"center\">
				{5}
			</td>
			<td class=\"center\">
				{6}
			</td>
		</tr>
",	$cellClass, $img, $pm['id'], $snoop, htmlspecialchars($pm['title']), $sender, formatdate($pm['date']), $delLink);
	}
}
else
	$pms = format(
"
		<tr class=\"cell1\">
			<td colspan=\"6\">
				".__("There are no messages to display.")."
			</td>
		</tr>
");

write(
"
	<form method=\"post\" action=\"".actionLink("private")."\">
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th style=\"width: 22px;\">
				<input type=\"checkbox\" id=\"ca\" onchange=\"checkAll();\" />
			</th>
			<th style=\"width: 32px;\">&nbsp;</th>
			<th>".__("Title")."</th>
			<th>{0}</th>
			<th style=\"min-width:120px\">".__("Date")."</th>
		</tr>
		{1}
		<tr class=\"header1\">
			<th style=\"text-align: left!important;\" colspan=\"6\">
				<input type=\"hidden\" name=\"action\" value=\"multidel\" />
				<input type=\"hidden\" name=\"token\" value=\"{$loguser['token']}\" />
				<a href=\"javascript:void();\" onclick=\"document.forms[1].submit();\">".__("Delete checked")."</a>
			</th>
		</tr>
	</table>
	</font>
", (isset($_GET['show']) ? __("To") : __("From")), $pms);

if($pagelinks)
	write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

?>
