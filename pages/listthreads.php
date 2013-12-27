<?php
$uid = (int)$_GET['id'];

$rUser = Query("select * from {users} where id={0}", $uid);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill(__("Unknown user ID."));

$title = __("Thread list");

$uname = $user["name"];
if($user["displayname"])
	$uname = $user["displayname"];

MakeCrumbs(array(actionLink("profile", $uid, "", $user["name"]) => htmlspecialchars($uname), '' => __("List of threads")), $links);

$total = FetchResult("SELECT
						count(*)
					FROM
						{threads} t
					WHERE t.user={0} AND t.forum IN ({1c})", $uid, ForumsWithPermission('forum.viewforum'));

$tpp = $loguser['threadsperpage'];
if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

if(!$tpp) $tpp = 50;

$rThreads = Query("	SELECT
						t.*,
						f.(title, id),
						".($loguserid ? "tr.date readdate," : '')."
						su.(_userfields),
						lu.(_userfields)
					FROM
						{threads} t
						".($loguserid ? "LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={4}" : '')."
						LEFT JOIN {users} su ON su.id=t.user
						LEFT JOIN {users} lu ON lu.id=t.lastposter
						LEFT JOIN {forums} f ON f.id=t.forum
					WHERE t.user={0} AND f.id IN ({5c})
					ORDER BY lastpostdate DESC LIMIT {2u}, {3u}", $uid, null, $from, $tpp, $loguserid, ForumsWithPermission('forum.viewforum'));

$numonpage = NumRows($rThreads);

$pagelinks = PageLinks(actionLink("listthreads", $uid, "from=", $user['name']), $tpp, $from, $total);

if($pagelinks)
	echo "<div class=\"smallFonts pages\">".__("Pages:")." ".$pagelinks."</div>";

$ppp = $loguser['postsperpage'];
if(!$ppp) $ppp = 20;

if(NumRows($rThreads))
{
	$forumList = "";
	$haveStickies = 1;
	$cellClass = 0;

	while($thread = Fetch($rThreads))
	{
		$forumList .= listThread($thread, $cellClass, false, true);
		$cellClass = ($cellClass + 1) % 2;
	}

	Write($mobileLayout ?
"
	<table class=\"outline margin width100\">
		<tr class=\"header1\">
			<th>".__("Threads")."</th>
		</tr>
		{0}
	</table>
" :
"
	<table class=\"outline margin width100\">
		<tr class=\"header1\">
			<th style=\"width: 20px;\">&nbsp;</th>
			<th style=\"width: 16px;\">&nbsp;</th>
			<th style=\"width: 35%;\">".__("Title")."</th>
			<th style=\"width: 25%;\">".__("Forum")."</th>
			<th>".__("Started by")."</th>
			<th>".__("Replies")."</th>
			<th>".__("Views")."</th>
			<th style=\"min-width:150px\">".__("Last post")."</th>
		</tr>
		{0}
	</table>
",	$forumList);
}
else
	Alert(__("No threads found."), __("Error"));

if($pagelinks)
	Write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

