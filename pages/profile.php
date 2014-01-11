<?php
//  AcmlmBoard XD - User profile page
//  Access: all

$id = (int)$_REQUEST['id'];

$rUser = Query("select u.* from {users} u where u.id={0}",$id);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill(__("Unknown user ID."));

$ugroup = $usergroups[$user['primarygroup']];
$usgroups = array();

$res = Query("SELECT groupid FROM {secondarygroups} WHERE userid={0}", $id);
while ($sg = Fetch($res)) $usgroups[] = $usergroups[$sg['groupid']];

if($id == $loguserid)
{
	Query("update {users} set newcomments = 0 where id={0}", $loguserid);
	$loguser['newcomments'] = false;
}

$canDeleteComments = ($id == $loguserid && HasPermission('user.deleteownusercomments')) || HasPermission('admin.adminusercomments');
$canComment = (HasPermission('user.postusercomments') && $user['primarygroup'] != Settings::get('bannedGroup')) || HasPermission('admin.adminusercomments');
$canVote = ($loguserid != $id) && (((time()-$loguser['regdate'])/86400) > 9) && HasPermission('user.rateusers'); // useless

if($loguserid && $_REQUEST['token'] == $loguser['token'])
{
	if(isset($_GET['block']))
	{
		$block = (int)$_GET['block'];
		$rBlock = Query("select * from {blockedlayouts} where user={0} and blockee={1}", $id, $loguserid);
		$isBlocked = NumRows($rBlock);
		if($block && !$isBlocked)
			$rBlock = Query("insert into {blockedlayouts} (user, blockee) values ({0}, {1})", $id, $loguserid);
		elseif(!$block && $isBlocked)
			$rBlock = Query("delete from {blockedlayouts} where user={0} and blockee={1} limit 1", $id, $loguserid);
		die(header("Location: ".actionLink("profile", $id)));
	}
	if($_GET['action'] == "delete")
	{
		$postedby = FetchResult("SELECT cid FROM {usercomments} WHERE uid={0} AND id={1}", $id, (int)$_GET['cid']);
		if ($canDeleteComments || ($postedby == $loguserid && HasPermission('user.deleteownusercomments')))
		{
			Query("delete from {usercomments} where uid={0} and id={1}", $id, (int)$_GET['cid']);
			die(header("Location: ".actionLink("profile", $id)));
		}
	}

	if(isset($_POST['actionpost']) && IsReallyEmpty($_POST['text']) && $canComment)
	{
		$rComment = Query("insert into {usercomments} (uid, cid, date, text) values ({0}, {1}, {2}, {3})", $id, $loguserid, time(), $_POST['text']);
		if($loguserid != $id)
			Query("update {users} set newcomments = 1 where id={0}", $id);
		die(header("Location: ".actionLink("profile", $id)));
	}
}

if($loguserid)
{
	$rBlock = Query("select * from {blockedlayouts} where user={0} and blockee={1}", $id, $loguserid);
	$isBlocked = NumRows($rBlock);
	if($isBlocked)
		$blockLayoutLink = actionLinkTagItem(__("Unblock layout"), "profile", $id, "block=0&token={$loguser['token']}");
	else
		$blockLayoutLink = actionLinkTagItem(__("Block layout"), "profile", $id, "block=1&token={$loguser['token']}");
}

$daysKnown = (time()-$user['regdate'])/86400;
$posts = FetchResult("select count(*) from {posts} where user={0}", $id);
$threads = FetchResult("select count(*) from {threads} where user={0}", $id);
$averagePosts = sprintf("%1.02f", $user['posts'] / $daysKnown);
$averageThreads = sprintf("%1.02f", $threads / $daysKnown);
$deletedposts = FetchResult("SELECT COUNT(*) FROM {posts} p WHERE p.user={0} AND p.deleted!=0 AND p.deletedby!={0}", $id);
$score = 1000 + (10 * $user['postplusones']) - (20 * $deletedposts);

$minipic = getMinipicTag($user);


if($user['rankset'])
{
	$currentRank = GetRank($user["rankset"], $user["posts"]);
	$toNextRank = GetToNextRank($user["rankset"], $user["posts"]);
	if($toNextRank)
		$toNextRank = Plural($toNextRank, "post");
}
if($user['title'])
	$title = preg_replace('@<br.*?>\s*(\S)@i', ' &bull; $1', strip_tags(CleanUpPost($user['title'], "", true), "<b><strong><i><em><span><s><del><img><a><br><br/><small>"));

if($user['homepageurl'])
{
	$nofollow = "";
	if(Settings::get("nofollow"))
		$nofollow = "rel=\"nofollow\"";
			
	if($user['homepagename'])
		$homepage = "<a $nofollow target=\"_blank\" href=\"".htmlspecialchars($user['homepageurl'])."\">".htmlspecialchars($user['homepagename'])."</a> - ".htmlspecialchars($user['homepageurl']);
	else
		$homepage = "<a $nofollow target=\"_blank\" href=\"".htmlspecialchars($user['homepageurl'])."\">".htmlspecialchars($user['url'])."</a>";
	$homepage = securityPostFilter($homepage);
}

$emailField = __("Private");
if($user['email'] == "")
	$emailField = __("None given");
elseif($user['showemail'])
	$emailField = "<span id=\"emailField\">".__("Public")." <button style=\"font-size: 0.7em;\" onclick=\"$(this.parentNode).load('{$boardroot}ajaxcallbacks.php?a=em&amp;id=".$id."');\">".__("Show")."</button></span>";

if($user['tempbantime'])
{
	write(
"
	<table class=\"outline margin\"><tr class=\"cell0\"><td class=\"smallFonts\">
		".__("This user has been temporarily banned until {0} (GMT). That's {1} left.")."
	</td></tr></table>
",	gmdate("M jS Y, G:i:s",$user['tempbantime']), TimeUnits($user['tempbantime'] - time())
	);
}


$profileParts = array();

$foo = array();
$foo[__("Name")] = $minipic . htmlspecialchars($user['displayname'] ? $user['displayname'] : $user['name']) . ($user['displayname'] ? " (".htmlspecialchars($user['name']).")" : "");
if($title)
	$foo[__("Title")] = $title;
	
$glist = '<strong style="color: '.htmlspecialchars($ugroup['color_unspec']).';">'.htmlspecialchars($ugroup['name']).'</strong>';
foreach ($usgroups as $sgroup)
{
	if ($sgroup['display'] > -1)
		$glist .= ', '.htmlspecialchars($sgroup['name']);
}
$foo[__("Groups")] = $glist;

if($currentRank)
	$foo[__("Rank")] = $currentRank;
if($toNextRank)
	$foo[__("To next rank")] = $toNextRank;
//$foo[__("Karma")] = $karma.$karmaLinks;
$foo[__("Total posts")] = format("{0} ({1} per day)", $posts, $averagePosts);
$foo[__("Total threads")] = format("{0} ({1} per day)", $threads, $averageThreads);
$foo[__("Registered on")] = format("{0} ({1} ago)", formatdate($user['regdate']), TimeUnits($daysKnown*86400));

$lastPost = Fetch(Query("
	SELECT
		p.id as pid, p.date as date,
		{threads}.title AS ttit, {threads}.id AS tid,
		{forums}.title AS ftit, {forums}.id AS fid
	FROM {posts} p
		LEFT JOIN {users} u on u.id = p.user
		LEFT JOIN {threads} on {threads}.id = p.thread
		LEFT JOIN {forums} on {threads}.forum = {forums}.id
	WHERE p.user={0}
	ORDER BY p.date DESC
	LIMIT 0, 1", $user["id"]));

if($lastPost)
{
	$thread = array();
	$thread['title'] = $lastPost['ttit'];
	$thread['id'] = $lastPost['tid'];
	$thread['forum'] = $lastPost['fid'];

	if(!HasPermission('forum.viewforum', $lastPost['fid']))
		$place = __("a restricted forum");
	else
	{
		$ispublic = HasPermission('forum.viewforum', $lastPost['fid'], true);
		$pid = $lastPost["pid"];
		$place = makeThreadLink($thread)." (".actionLinkTag($lastPost["ftit"], "forum", $lastPost["fid"], "", $ispublic?$lastPost["ftit"]:'').")";
		$place .= " &raquo; ".actionLinkTag($pid, "post", $pid);
	}
	$foo[__("Last post")] = format("{0} ({1} ago)", formatdate($lastPost["date"]), TimeUnits(time() - $lastPost["date"])) .
								"<br>".__("in")." ".$place;
}
else
	$foo[__("Last post")] = __("Never");

$foo[__("Last view")] = format("{0} ({1} ago)", formatdate($user['lastactivity']), TimeUnits(time() - $user['lastactivity']));
$foo[__("Score")] = $score;
$foo[__("Browser")] = $user['lastknownbrowser'];
if(HasPermission('admin.viewips'))
	$foo[__("Last known IP")] = formatIP($user['lastip']);
$profileParts[__("General information")] = $foo;

$foo = array();
$foo[__("Email address")] = $emailField;
if($homepage)
	$foo[__("Homepage")] = $homepage;
$profileParts[__("Contact information")] = $foo;

$foo = array();
$infofile = "themes/".$user['theme']."/themeinfo.txt";

if(file_exists($infofile))
{
	$themeinfo = file_get_contents($infofile);
	$themeinfo = explode("\n", $themeinfo, 2);
	
	$themename = trim($themeinfo[0]);
	$themeauthor = trim($themeinfo[1]);
}
else
{
	$themename = $user['theme'];
	$themeauthor = "";
}
$foo[__("Theme")] = $themename;
$foo[__("Items per page")] = Plural($user['postsperpage'], __("post")) . ", " . Plural($user['threadsperpage'], __("thread"));
$profileParts[__("Presentation")] = $foo;

$foo = array();
if($user['realname'])
	$foo[__("Real name")] = htmlspecialchars($user['realname']);
if($user['location'])
	$foo[__("Location")] = htmlspecialchars($user['location']);
if($user['birthday'])
	$foo[__("Birthday")] = formatBirthday($user['birthday']);
//if($user['bio'])
//	$foo[__("Bio")] = CleanUpPost($user['bio']);

if(count($foo))
	$profileParts[__("Personal information")] = $foo;

if ($user['bio'])
	$profileParts[__('Bio')] = CleanUpPost($user['bio']);

$badgersR = Query("select * from {badges} where owner={0} order by color", $id);
if(NumRows($badgersR))
{
	$badgers = "";
	$colors = array("bronze", "silver", "gold", "platinum");
	while($badger = Fetch($badgersR))
		$badgers .= Format("<span class=\"badge {0}\">{1}</span> ", $colors[$badger['color']], $badger['name']);
	$profileParts['General information']['Badges'] = $badgers;
}

$prepend = "";
$bucket = "profileTable"; include("./lib/pluginloader.php");

if (!$mobileLayout) echo "
	<table class=\"layout-table\">
		<tr>
			<td style=\"width: 60%; border: 0px none; vertical-align: top; padding-right: 1em;\">
";
echo $prepend;

$cc = 0;
foreach($profileParts as $partName => $fields)
{
	$issingle = !is_array($fields);
	
	write("
				<table class=\"outline margin\">
					<tr class=\"header1\">
						<th{1}>{0}</th>
					</tr>
", $partName, $issingle?'':' colspan="2"');
	if (!$issingle)
	{
		foreach($fields as $label => $value)
		{
			$cc = ($cc + 1) % 2;
			write("
							<tr>
								<td class=\"cell2 center\" style=\"width:150px;\">{0}</td>
								<td class=\"cell{2}\">{1}</td>
							</tr>
	", str_replace(" ", "&nbsp;", $label), $value, $cc);
		}
	}
	else
	{
		$cc = ($cc + 1) % 2;
		echo "
							<tr>
								<td class=\"cell{$cc}\">{$fields}</td>
							</tr>
	";
	}
	
	write("
				</table>
");
}

$bucket = "profileLeft"; include("./lib/pluginloader.php");
if (!$mobileLayout) echo "
			</td>
";


$cpp = 15;
$total = FetchResult("SELECT
						count(*)
					FROM {usercomments}
					WHERE uid={0}", $id);

$from = (int)$_GET["from"];
if(!isset($_GET["from"]))
	$from = 0;
$realFrom = $total-$from-$cpp;
$realLen = $cpp;
if($realFrom < 0)
{
	$realLen += $realFrom;
	$realFrom = 0;
}
$rComments = Query("SELECT
		u.(_userfields),
		uc.id, uc.cid, uc.text, uc.date
		FROM {usercomments} uc
		LEFT JOIN {users} u ON u.id = uc.cid
		WHERE uc.uid={0}
		ORDER BY uc.date ASC LIMIT {1u},{2u}", $id, $realFrom, $realLen);

$pagelinks = PageLinksInverted(actionLink("profile", $id, "from="), $cpp, $from, $total);

$commentList = "";
$commentField = "";
if(NumRows($rComments))
{
	while($comment = Fetch($rComments))
	{
		$deleteLink = '';
		if($canDeleteComments || ($comment['cid'] == $loguserid && HasPermission('user.deleteownusercomments')))
			$deleteLink = "<small style=\"float: right; margin: 0px 4px;\">".
				actionLinkTag("&#x2718;", "profile", $id, "action=delete&cid=".$comment['id']."&token={$loguser['token']}")."</small>";
		
		$cellClass = ($cellClass+1) % 2;
		$thisComment = format(
"
						<tr>
							<td class=\"cell2 width25\" style=\"vertical-align:top;\">
								{0}<br>
								<small>{4}</small>
							</td>
							<td class=\"cell{1}\" style=\"vertical-align:top;\">
								{3}{2}
							</td>
						</tr>
",	UserLink(getDataPrefix($comment, "u_")), $cellClass, CleanUpPost($comment['text']), $deleteLink, relativedate($comment['date']));
		$commentList = $commentList.$thisComment;
		if(!isset($lastCID))
			$lastCID = $comment['cid'];
	}

	$pagelinks = "<td colspan=\"2\" class=\"cell1\">$pagelinks</td>";
	if($total > $cpp)
		$commentList = "$pagelinks$commentList$pagelinks";
}
else
{
	$commentsWasEmpty = true;
	$commentList = $thisComment = format(
"
						<tr>
							<td class=\"cell0\" colspan=\"2\">
								".__("No comments.")."

							</td>
						</tr>
");
}

if($canComment)
{
	$commentField = "
					<tr>
						<td colspan=\"2\" class=\"cell2\">
								<div>
									<form name=\"commentform\" method=\"post\" action=\"".actionLink("profile")."\">
										<input type=\"hidden\" name=\"id\" value=\"$id\" />
										<input type=\"text\" name=\"text\" style=\"width: 80%;\" maxlength=\"255\" />
										<input type=\"submit\" name=\"actionpost\" value=\"".__("Post")."\" />
										<input type=\"hidden\" name=\"token\" value=\"{$loguser['token']}\" />
									</form>
								</div>
							</td>
						</tr>";
}

print "
			".($mobileLayout?'':"<td style=\"vertical-align: top; border: 0px none;\">")."
				<table class=\"outline margin\">
					<tr class=\"header1\">
						<th colspan=\"2\">
							".format(__("Comments about {0}"), UserLink($user))."
						</th>
					</tr>
					$commentList
					$commentField
				</table>";

$bucket = "profileRight"; include("./lib/pluginloader.php");

if (!$mobileLayout) print "
			</td>
		</tr>
	</table>";
	

if (!$mobileLayout)
{
	$previewPost['text'] = Settings::get("profilePreviewText");

	$previewPost['num'] = "_";
	$previewPost['id'] = "_";

	foreach($user as $key => $value)
		$previewPost["u_".$key] = $value;

	MakePost($previewPost, POST_SAMPLE);
}

if (HasPermission('admin.banusers') && $loguserid != $id)
{
	if ($user['primarygroup'] != Settings::get('bannedGroup'))
		$links .= actionLinkTagItem('Ban user', 'banhammer', $id);
	//else
	//	$links .= actionLinkTagItem('Unban user', 'banhammer', $id, 'unban=1');
	// TODO should mods be able to unban people?
}

if(HasPermission('user.editprofile') && $loguserid == $id)
	$links .= actionLinkTagItem(__("Edit my profile"), "editprofile");
else if(HasPermission('admin.editusers'))
	$links .= actionLinkTagItem(__("Edit user"), "editprofile", $id);

if(HasPermission('admin.editusers'))
	$links .= actionLinkTagItem(__('Edit permissions'), 'editperms', '', 'uid='.$id);

if(HasPermission('admin.viewpms'))
	$links .= actionLinkTagItem(__("Show PMs"), "private", "", "user=".$id);

if(HasPermission('user.sendpms'))
	$links .= actionLinkTagItem(__("Send PM"), "sendprivate", "", "uid=".$id);

$links .= actionLinkTagItem(__("Show posts"), "listposts", $id, "", $user["name"]);
$links .= actionLinkTagItem(__("Show threads"), "listthreads", $id, "", $user["name"]);

$links .= $blockLayoutLink;

$uname = $user["name"];
if($user["displayname"])
	$uname = $user["displayname"];
MakeCrumbs(array(actionLink("profile", $id) => htmlspecialchars($uname)), $links);

$title = format(__("Profile for {0}"), htmlspecialchars($uname));

function IsReallyEmpty($subject)
{
	$trimmed = trim(preg_replace("/&.*;/", "", $subject));
	return strlen($trimmed) != 0;
}


?>
