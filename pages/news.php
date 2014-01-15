<?php

$rFora = Query("select * from {forums} where id = {0}",Settings::get("newsForum"));
if(NumRows($rFora))
{
	$forum = Fetch($rFora);
	if(!HasPermission('forum.viewforum', $forum['id']))
		Kill(__("News forum is a restricted forum."));
} else
	Kill(__("Unknown forum ID."));

$fid = $forum['id'];

$key = hash('sha256', "{$loguserid},{$loguser['pss']},{$salt}");

$total = $forum['numthreads'];

if(isset($_GET['from']))
	$from = (int)$_GET['from'];
else
	$from = 0;

$tpp = 5;

//echo '<br>';
$links = array('<a href="'.$boardroot.'rss.php">RSS feed</a>');
if (HasPermission('forum.postthreads', $forum['id']))
	$links[] = actionLinkTag('Post new', 'newthread', $forum['id']);

MakeCrumbs(array(actionLink('news') => 'Latest news'), $links);

/*$lastposts = '';
$lp = Query("
	SELECT 
		t.title,
		f.title,
		COUNT(p.thread) nposts, 
		p2.id, p2.date,
		u.(_userfields)
	FROM 
		{threads} t 
		LEFT JOIN {forums} f ON f.id=t.forum
		LEFT JOIN {posts} p ON p.thread=t.id 
		LEFT JOIN {posts} p2 ON p2.date=MIN(p.date)
		LEFT JOIN {users} u ON u.id=p2.user
	WHERE f.id IN ({1c})
	ORDER BY MAX(p.date)
	GROUP BY p.thread",
	ForumsWithPermission('forum.viewforum'));*/

?>
	<table class="layout-table"><tr>
		<td style="width:50%; vertical-align:top; padding-right:0.5em;">
			<table class="outline margin">
				<tr class="cell1">
					<td>
						<big>Welcome to the board</big><br>
						<br>
						This is some text about the board and all that shit<br>
						<br>
						have fun<br>
						<br>
						<br>
						<br>
						blah blah blah<br>
						<br>
						<br>
						blarg<br>
					</td>
				</tr>
			</table>
		</td>
		<td style="vertical-align:top; padding-left:0.5em;">
			<table class="outline margin">
				<tr class="header1"><th>Last posts</th></tr>
				<?php echo $lastposts; ?>
				<tr class="cell1">
					<td>
						<a href="#" title="link to last post goes here">Some dumb thread</a> (<a href="#">Sample forum</a>)<br>
						<small>4 new posts, last by <a href="#"><strong style="color:#97acef;">blargman</strong></a> 7 minutes ago
					</td>
				</tr>
				<tr class="cell1">
					<td>
						<a href="#">Another derp thread</a> (<a href="#">Sample forum</a>)<br>
						<small>1 new post by <a href="#"><strong style="color:#affabe;">trololo</strong></a> 13 minutes ago
					</td>
				</tr>
				<tr class="cell1">
					<td>
						<a href="#">Intelligent thread!</a> (<a href="#">Sample forum</a>)<br>
						<small>3 new posts, last by <a href="#"><strong style="color:#00aa55;">crazyposter</strong></a> 1 hour ago
					</td>
				</tr>
			</table>
		</td>
	</tr></table>
<?php

$rThreads = Query("	SELECT 
						t.id, t.title, t.closed, t.replies, t.lastpostid,
						p.id pid, p.date,
						pt.text,
						su.(_userfields),
						lu.(_userfields)
					FROM 
						{threads} t
						LEFT JOIN {posts} p ON p.thread=t.id AND p.id=t.firstpostid
						LEFT JOIN {posts_text} pt ON pt.pid=p.id AND pt.revision=p.currentrevision
						LEFT JOIN {users} su ON su.id=t.user
						LEFT JOIN {users} lu ON lu.id=t.lastposter
					WHERE t.forum={0} AND p.deleted=0
					ORDER BY p.date DESC LIMIT {1u}, {2u}", $fid, $from, $tpp);

$numonpage = NumRows($rThreads);

$pagelinks = PageLinks(actionLink('news', '', 'from='), $tpp, $from, $total);
if ($pagelinks)
	Write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

while($thread = Fetch($rThreads))
{
	$starter = getDataPrefix($thread, 'su_');
	$last = getDataPrefix($thread, 'lu_');

	$tags = ParseThreadTags($thread['title']);

	if($thread['sticky'] && $haveStickies == 0) $haveStickies = 1;
		
	if($thread['replies'] == 0) $lastLink = "";
	
	$postdate = formatdate($thread['date']);
	$posttext = CleanUpPost($thread['text'],$starter['name'], false, false);

	if (!$thread['replies'])
		$comments = 'No comments yet';
	else if ($thread['replies'] < 2)
		$comments = actionLinkTag('1 comment', 'thread', 0, 'pid='.$thread['lastpostid'].'#'.$thread['lastpostid']).' (by '.UserLink($last).')';
	else
		$comments = actionLinkTag($thread['replies'].' comments', 'thread', 0, 'pid='.$thread['lastpostid'].'#'.$thread['lastpostid']).' (last by '.UserLink($last).')';

	if ($thread['closed'])
		$newreply = 'Comment posting closed.';
	else if (!$loguserid)
		$newreply = actionLinkTag('Log in', 'login').' to post a comment.';
	else
		$newreply = actionLinkTag("Post a comment", "newreply", $thread['id']);
	
	$modlinks = '<ul class="pipemenu">';
	if (($loguserid == $starter['id'] & HasPermission('user.editownposts')) || HasPermission('mod.editposts', $forum['id']))
		$modlinks .= actionLinkTagItem(__('Edit'), 'editpost', $thread['pid']);
	if (($loguserid == $starter['id'] & HasPermission('user.deleteownposts')) || HasPermission('mod.deleteposts', $forum['id']))
		$modlinks .= actionLinkTagItem(__('Delete'), 'editpost', $thread['pid'], 'delete=1&key='.$key);
	$modlinks .= '</ul>';

	$forumList .= "<table class='outline margin width100'>";
	$forumList .= "
	<tr class=\"header1\" >
		<th style='text-align:left!important;'>
			<span style='float:right;text-align:right;font-weight:normal;'>{$modlinks}</span>
			<span style='font-size:125%;'>{$tags[0]}</span><br>
			<span style='font-weight:normal;font-size:95%;'>Posted on {$postdate} by ".UserLink($starter)."</span>
		</th>
	</tr>";
	$forumList .= "<tr class='cell1'><td style='padding:10px'>{$posttext}</td></tr>";
	$forumList .= "<tr class='cell0'><td>{$comments}. {$newreply}</td></tr>";
	$forumList .="</table><br>";
}

Write($forumList);

if ($pagelinks)
	Write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

?>
