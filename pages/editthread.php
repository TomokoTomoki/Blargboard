<?php
//  AcmlmBoard XD - Thread editing page
//  Access: moderators

$title = __("Edit thread");

if (isset($_REQUEST['action']) && $loguser['token'] != $_REQUEST['key'])
	Kill(__("No."));

if(!$loguserid) //Not logged in?
	Kill(__("You must be logged in to edit threads."));

if(isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if(!isset($_GET['id']))
	Kill(__("Thread ID unspecified."));

$tid = (int)$_GET['id'];

$rThread = Query("select * from {threads} where id={0}", $tid);
if(NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill(__("Unknown thread ID."));

$canmod = 
	HasPermission('mod.closethreads', $thread['forum']) ||
	HasPermission('mod.stickthreads', $thread['forum']) ||
	HasPermission('mod.trashthreads', $thread['forum']) ||
	HasPermission('mod.deletethreads', $thread['forum']) ||
	HasPermission('mod.movethreads', $thread['forum']) ||
	HasPermission('mod.renamethreads', $thread['forum']);
	
$isclosed = $thread['closed'] && !HasPermission('mod.closethreads', $thread['forum']);
$canrename = ($thread['user'] == $loguserid && HasPermission('user.renameownthreads') && !$isclosed) || HasPermission('mod.renamethreads', $thread['forum']);

if(($thread['user'] != $loguserid || !HasPermission('user.renameownthreads')) && !$canmod)
	Kill(__("You are not allowed to edit this thread."));

$rFora = Query("select * from {forums} where id={0}", $thread['forum']);
if(NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill(__("Unknown forum ID."));

if (!HasPermission('forum.viewforum', $forum['id']))
	Kill(__('You may not access this forum.'));

$OnlineUsersFid = $thread['forum'];
$isHidden = !HasPermission('forum.viewforum', $forum['id'], true);

$tags = ParseThreadTags($thread['title']);
MakeCrumbs(forumCrumbs($forum) + array(actionLink("thread", $tid, '', $isHidden?'':$tags[0]) => $tags[0], '' => __("Edit thread")), $links);

if($_GET['action']=="close" && HasPermission('mod.closethreads', $thread['forum']))
{
	$rThread = Query("update {threads} set closed=1 where id={0}", $tid);
	Report("[b]".$loguser['name']."[/] closed thread [b]".$thread['title']."[/] -> [g]#HERE#?tid=".$tid, $isHidden);

	die(header("Location: ".actionLink("thread", $tid)));
}
elseif($_GET['action']=="open" && HasPermission('mod.closethreads', $thread['forum']))
{
	$rThread = Query("update {threads} set closed=0 where id={0}", $tid);
	Report("[b]".$loguser['name']."[/] opened thread [b]".$thread['title']."[/] -> [g]#HERE#?tid=".$tid, $isHidden);

	die(header("Location: ".actionLink("thread", $tid)));
}
elseif($_GET['action']=="stick" && HasPermission('mod.stickthreads', $thread['forum']))
{
	$rThread = Query("update {threads} set sticky=1 where id={0}", $tid);
	Report("[b]".$loguser['name']."[/] stickied thread [b]".$thread['title']."[/] -> [g]#HERE#?tid=".$tid, $isHidden);

	die(header("Location: ".actionLink("thread", $tid)));
}
elseif($_GET['action']=="unstick" && HasPermission('mod.stickthreads', $thread['forum']))
{
	$rThread = Query("update {threads} set sticky=0 where id={0}", $tid);
	Report("[b]".$loguser['name']."[/] unstuck thread [b]".$thread['title']."[/] -> [g]#HERE#?tid=".$tid, $isHidden);

	die(header("Location: ".actionLink("thread", $tid)));
}
elseif(($_GET['action'] == "trash" && HasPermission('mod.trashthreads', $thread['forum']))
	|| ($_GET['action'] == 'delete' && HasPermission('mod.deletethreads', $thread['forum'])))
{
	if ($_GET['action'] == 'delete')
	{
		$trashid = Settings::get('secretTrashForum');
		$verb = 'deleted';
	}
	else
	{
		$trashid = Settings::get('trashForum');
		$verb = 'thrashed';
	}
	
	if($trashid > 0)
	{
		$rThread = Query("update {threads} set forum={0}, closed=1 where id={1} limit 1", $trashid, $tid);

		//Tweak forum counters
		$rForum = Query("update {forums} set numthreads=numthreads-1, numposts=numposts-{0} where id={1}", ($thread['replies']+1), $thread['forum']);
		$rForum = Query("update {forums} set numthreads=numthreads+1, numposts=numposts+{0} where id={1}", ($thread['replies']+1), $trashid);

		// Tweak forum counters #2
		Query("	UPDATE {forums} LEFT JOIN {threads}
				ON {forums}.id={threads}.forum AND {threads}.lastpostdate=(SELECT MAX(nt.lastpostdate) FROM {threads} nt WHERE nt.forum={forums}.id)
				SET {forums}.lastpostdate=IFNULL({threads}.lastpostdate,0), {forums}.lastpostuser=IFNULL({threads}.lastposter,0), {forums}.lastpostid=IFNULL({threads}.lastpostid,0)
				WHERE {forums}.id={0} OR {forums}.id={1}", $thread['forum'], $trashid);

		Report("[b]".$loguser['name']."[/] {$verb} thread [b]".$thread['title']."[/] -> [g]#HERE#?tid=".$tid, $isHidden);

		die(header("Location: ".actionLink("forum", $thread['forum'])));
	}
	else
		Kill(__("Could not identify trash forum."));
}
elseif($_POST['action'] == __("Edit"))
{

	if($thread['forum'] != $_POST['moveTo'] && HasPermission('mod.movethreads', $thread['forum']))
	{
		$moveto = (int)$_POST['moveTo'];
		$dest = Fetch(Query("select * from {forums} where id={0}", $moveto));
		if(!$dest)
			Kill(__("Unknown forum ID."));

		//Tweak forum counters
		$rForum = Query("update {forums} set numthreads=numthreads-1, numposts=numposts-{0} where id={1}", ($thread['replies']+1), $thread['forum']);
		$rForum = Query("update {forums} set numthreads=numthreads+1, numposts=numposts+{0} where id={1}", ($thread['replies']+1), $moveto);

		$rThread = Query("update {threads} set forum={0} where id={1}", (int)$_POST['moveTo'], $tid);

		// Tweak forum counters #2
		Query("	UPDATE {forums} LEFT JOIN {threads}
				ON {forums}.id={threads}.forum AND {threads}.lastpostdate=(SELECT MAX(nt.lastpostdate) FROM {threads} nt WHERE nt.forum={forums}.id)
				SET {forums}.lastpostdate=IFNULL({threads}.lastpostdate,0), {forums}.lastpostuser=IFNULL({threads}.lastposter,0), {forums}.lastpostid=IFNULL({threads}.lastpostid,0)
				WHERE {forums}.id={0} OR {forums}.id={1}", $thread['forum'], $moveto);

		Report("[b]".$loguser['name']."[/] moved thread [b]".$thread['title']."[/] -> [g]#HERE#?tid=".$tid, $isHidden);
	}

	$isClosed = HasPermission('mod.closethreads', $thread['forum']) ? (isset($_POST['isClosed']) ? 1 : 0) : $thread['closed'];
	$isSticky = HasPermission('mod.stickthreads', $thread['forum']) ? (isset($_POST['isSticky']) ? 1 : 0) : $thread['sticky'];

	$trimmedTitle = $canrename ? trim(str_replace('&nbsp;', ' ', $_POST['title'])) : 'lolnotempty';
	if($trimmedTitle != "")
	{
		if ($canrename)
		{
			if($_POST['iconid'])
			{
				$_POST['iconid'] = (int)$_POST['iconid'];
				if($_POST['iconid'] < 255)
					$iconurl = "img/icons/icon".$_POST['iconid'].".png";
			}
		}
		else
			$iconurl = $thread['icon'];

		$rThreads = Query("update {threads} set title={0}, icon={1}, closed={2}, sticky={3} where id={4} limit 1", 
			$canrename ? $_POST['title'] : $thread['title'], $iconurl, $isClosed, $isSticky, $tid);

		Report("[b]".$loguser['name']."[/] edited thread [b]".$thread['title']."[/] -> [g]#HERE#?tid=".$tid, $isHidden);

		die(header("Location: ".actionLink("thread", $tid)));
	}
	else
		Alert(__("Your thread title is empty. Enter a title and try again."));
}

if ($canrename)
{
	if(!$_POST['title']) $_POST['title'] = $thread['title'];

	$match = array();
	if (preg_match("@^img/icons/icon(\d+)\..{3,}\$@si", $thread['icon'], $match))
		$_POST['iconid'] = $match[1];
	elseif($thread['icon'] == "") //Has no icon
		$_POST['iconid'] = 0;
	else //Has custom icon
	{
		$_POST['iconid'] = 255;
		$_POST['iconurl'] = $thread['icon'];
	}

	if(!isset($_POST['iconid'])) $_POST['iconid'] = 0;

	$icons = "";
	$i = 1;
	while(is_file("img/icons/icon".$i.".png"))
	{
		$check = "";
		if($_POST['iconid'] == $i) $check = "checked=\"checked\" ";
		$icons .= "	<label>
						<input type=\"radio\" $checked name=\"iconid\" value=\"$i\" />
						<img src=\"".resourceLink("img/icons/icon$i.png")."\" alt=\"Icon $i\" onclick=\"javascript:void()\" />
					</label>";
		$i++;
	}
	$check[0] = "";
	$check[1] = "";
	if($_POST['iconid'] == 0) $check[0] = "checked=\"checked\" ";
	if($_POST['iconid'] == 255)
	{
		$check[1] = "checked=\"checked\" ";
		$iconurl = htmlspecialchars($_POST['iconurl']);
	}
}

echo "
	<script src=\"".resourceLink("js/threadtagging.js")."\"></script>
	<form action=\"".actionLink("editthread")."\" method=\"post\">
		<table class=\"outline margin\" style=\"width: 100%;\">
			<tr class=\"header1\">
				<th colspan=\"2\">
					".__("Edit thread")."
				</th>
			</tr>
			".($canrename ? "<tr class=\"cell0\">
				<td>
					<label for=\"tit\">".__("Title")."</label>
				</td>
				<td id=\"threadTitleContainer\">
					<input type=\"text\" id=\"tit\" name=\"title\" style=\"width: 98%;\" maxlength=\"60\" value=\"".htmlspecialchars($_POST['title'])."\" />
				</td>
			</tr>
			<tr class=\"cell1\">
				<td>
					".__("Icon")."
				</td>
				<td class=\"threadIcons\">
					<label>
						<input type=\"radio\" {$check[0]} id=\"noicon\" name=\"iconid\" value=\"0\">
						".__("None")."
					</label>
					$icons
					<br/>
					<label>
						<input type=\"radio\" {$check[1]} name=\"iconid\" value=\"255\" />
						<span>".__("Custom")."</span>
					</label>
					<input type=\"text\" name=\"iconurl\" style=\"width: 50%;\" maxlength=\"100\" value=\"".htmlspecialchars($iconurl)."\" />
				</td>
			</tr>
			" : '').(HasPermission('mod.stickthreads', $thread['forum']) || HasPermission('mod.closethreads', $thread['forum']) ? "
			<tr class=\"cell0\">
				<td>
					".__("Extras")."
				</td>
				<td>
					".(HasPermission('mod.closethreads', $thread['forum']) ? "
					<label>
						<input type=\"checkbox\" name=\"isClosed\" ".($thread['closed'] ? " checked=\"checked\"" : "")." />
						".__("Closed")."
					</label>
					" : '').(HasPermission('mod.stickthreads', $thread['forum']) ? "
					<label>
						<input type=\"checkbox\" name=\"isSticky\" ".($thread['sticky'] ? " checked=\"checked\"" : "")." />
						".__("Sticky")."
					</label>
					" : '')."
				</td>
			</tr>
			" : '').(HasPermission('mod.movethreads', $thread['forum']) ? "
			<tr class=\"cell1\">
				<td>
					".__("Move")."
				</td>
				<td>
					".makeForumList('moveTo', $thread['forum'])."
				</td>
			</tr>
			" : '')."
			<tr class=\"cell2\">
				<td></td>
				<td>
					<input type=\"submit\" name=\"action\" value=\"".__("Edit")."\"></input>
					<input type=\"hidden\" name=\"id\" value=\"$tid\" />
					<input type=\"hidden\" name=\"key\" value=\"".$loguser['token']."\" />
				</td>
			</tr>
		</table>
	</form>";

?>
