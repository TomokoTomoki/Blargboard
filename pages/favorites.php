<?php
// favorites page
// forum.php copypasta

if (!$loguserid)
	Kill(__("You must be logged in to use this feature."));

if ($_GET['action'] == "markasread")
{
	Query("	REPLACE INTO 
				{threadsread} (id,thread,date) 
			SELECT 
				{0}, t.id, {1} 
			FROM 
				{threads} t
				INNER JOIN {favorites} fav ON fav.user={0} AND fav.thread=t.id",
		$loguserid, time());

	die(header("Location: ".actionLink("board")));
}
else if ($_GET['action'] == 'add' || $_GET['action'] == 'remove')
{
	if ($_GET['token'] !== $loguser['token'])
		Kill(__('No.'));
	
	$tid = (int)$_GET['id'];
	$thread = Query("SELECT t.forum FROM {threads} t WHERE t.id={0}", $tid);
	if (!NumRows($thread))
		Kill(__("Invalid thread ID."));
	
	$thread = Fetch($thread);
	if (!HasPermission('forum.viewforum', $thread['forum']))
		Kill(__("Nice try, hacker kid, but no."));
	
	if ($_GET['action'] == 'add')
		Query("INSERT IGNORE INTO {favorites} (user,thread) VALUES ({0},{1})", $loguserid, $tid);
	else
		Query("DELETE FROM {favorites} WHERE user={0} AND thread={1}", $loguserid, $tid);
	
	die(header('Location: '.$_SERVER['HTTP_REFERER']));
}

$title = 'Favorites';

$links = actionLinkTagItem(__("Mark threads read"), 'favorites', 0, 'action=markasread');

MakeCrumbs(array(actionLink('favorites') => 'Favorites'), $links);

$total = $forum['numthreads'];
$tpp = $loguser['threadsperpage'];
if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

if(!$tpp) $tpp = 50;

$rThreads = Query("	SELECT
						t.*,
						tr.date readdate,
						su.(_userfields),
						lu.(_userfields),
						f.(id,title)
					FROM
						{threads} t
						INNER JOIN {favorites} fav ON fav.user={0} AND fav.thread=t.id
						LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={0}
						LEFT JOIN {users} su ON su.id=t.user
						LEFT JOIN {users} lu ON lu.id=t.lastposter
						LEFT JOIN {forums} f ON f.id=t.forum
					WHERE f.id IN ({3c})
					ORDER BY sticky DESC, lastpostdate DESC LIMIT {1u}, {2u}", 
					$loguserid, $from, $tpp, ForumsWithPermission('forum.viewforum'));

$numonpage = NumRows($rThreads);

$pagelinks = PageLinks(actionLink("forum", $fid, "from="), $tpp, $from, $total);

if($pagelinks)
	echo "<div class=\"smallFonts pages\">".__("Pages:")." ".$pagelinks."</div>";

$ppp = $loguser['postsperpage'];
if(!$ppp) $ppp = 20;

if(NumRows($rThreads))
{
	$forumList = "";
	$haveStickies = 0;
	$cellClass = 0;

	while($thread = Fetch($rThreads))
	{
		$forumList .= listThread($thread, $cellClass, true, true);
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
			<th style=\"width: 28px;\">&nbsp;</th>
			<th style=\"width: 16px;\">&nbsp;</th>
			<th style=\"width: 60%;\">".__("Title")."</th>
			<th>".__("Forum")."</th>
			<th>".__("Started by")."</th>
			<th>".__("Replies")."</th>
			<th>".__("Views")."</th>
			<th style=\"min-width:150px\">".__("Last post")."</th>
		</tr>
		{0}
	</table>
",	$forumList);
} else
	Alert(__("You do not have any favorite threads."), __("Notice"));

if($pagelinks)
	Write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

if (!$mobileLayout) printRefreshCode();

?>
