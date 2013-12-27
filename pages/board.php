<?php

$board = $_GET['id'];
if (!$board) $board = '';
if (!isset($forumBoards[$board])) $board = '';

if($loguserid && isset($_GET['action']) && $_GET['action'] == "markallread")
{
	Query("REPLACE INTO {threadsread} (id,thread,date) SELECT {0}, t.id, {1} FROM {threads} t".($board!='' ? ' LEFT JOIN {forums} f ON f.id=t.forum WHERE f.board={2}' : ''), 
		$loguserid, time(), $board);
		
	die(header("Location: ".actionLink("board")));
}

$links = '';
if($loguserid)
	$links = actionLinkTagItem(__("Mark all forums read"), "board", $board, "action=markallread");

MakeCrumbs(forumCrumbs(array('board' => $board)), $links);

if (!$mobileLayout && $board == '')
{
	$statData = Fetch(Query("SELECT
		(SELECT COUNT(*) FROM {threads}) AS numThreads,
		(SELECT COUNT(*) FROM {posts}) AS numPosts,
		(SELECT COUNT(*) FROM {users}) AS numUsers,
		(select count(*) from {posts} where date > {0}) AS newToday,
		(select count(*) from {posts} where date > {1}) AS newLastHour,
		(select count(*) from {users} where lastposttime > {2}) AS numActive",
		 time() - 86400, time() - 3600, time() - 2592000));

	$stats = Format(__("{0} and {1} total"), Plural($statData["numThreads"], __("thread")), Plural($statData["numPosts"], __("post")));
	$stats .= "<br />".format(__("{0} today, {1} last hour"), Plural($statData["newToday"], __("new post")), $statData["newLastHour"]);

	$percent = $statData["numUsers"] ? ceil((100 / $statData["numUsers"]) * $statData["numActive"]) : 0;
	$lastUser = Query("select u.(_userfields) from {users} u order by u.regdate desc limit 1");
	if(numRows($lastUser))
	{
		$lastUser = getDataPrefix(Fetch($lastUser), "u_");
		$last = format(__("{0}, {1} active ({2}%)"), Plural($statData["numUsers"], __("registered user")), $statData["numActive"], $percent)."<br />".format(__("Newest: {0}"), UserLink($lastUser));
	}
	else
		$last = __("No registered users")."<br />&nbsp;";

	echo
	"
	<table class=\"outline margin width100\" style=\"overflow: auto;\">
		<tr class=\"cell2 center\" style=\"overflow: auto;\">
		<td class=\"smallFonts\">
			<div style=\"float: left; width: 33%;\">&nbsp;<br>&nbsp;</div>
			<div style=\"float: right; width: 33%; text-align: right;\">{$last}</div>
			<div class=\"center\">
				{$stats}
			</div>
		</td>
		</tr>
	</table>
";
}

makeAnncBar();
makeForumListing(0, $board);

?>
