<?php

$title = __("Last posts");
MakeCrumbs(array(actionLink("lastposts") => __("Last posts")), '');

$time = $_GET['time'];
if ($time != 'new') $time = (int)$time;
if (!$time) $time = 'new';
$show = $_GET['show'];
if ($show != 'threads' && $show != 'posts' && $show != 'listposts') $show = 'threads';

$from = (int)$_GET['from'];
$fparam = $from ? '&from='.$from : '';

$spans = array(3600=>'1 hour', 86400=>'1 day', 259200=>'3 days', 'new'=>'New posts');
$options = '';
foreach($spans as $span=>$desc)
{
	if ($span == $time)
		$options .= '<li>'.$desc.'</li>';
	else
		$options .= actionLinkTagItem($desc, 'lastposts', '', 'time='.$span.'&show='.$show.$fparam);
}
$options2 = ($show=='threads') ? '<li>List threads</li>' : actionLinkTagItem('List threads', 'lastposts', '', 'time='.$time.'&show=threads'.$fparam);
$options2 .= ($show=='listposts') ? '<li>List posts</li>' : actionLinkTagItem('List posts', 'lastposts', '', 'time='.$time.'&show=listposts'.$fparam);
$options2 .= ($show=='posts') ? '<li>Show posts</li>' : actionLinkTagItem('Show posts', 'lastposts', '', 'time='.$time.'&show=posts'.$fparam);
echo "
	<div class=\"smallFonts margin\">
		".__("Show posts from within:")."
		<ul class=\"pipemenu\">
			{$options}
		</ul>
		&mdash; 
		<ul class=\"pipemenu\">
			{$options2}
		</ul>
	</div>
";

$mindate = ($time=='new') ? ($loguserid ? 'IFNULL(tr.date,0)' : '{2}') : '{1}';
$total = FetchResult("SELECT COUNT(".($show=='threads'?'DISTINCT p.thread':'*').") FROM {posts} p ".(($loguserid&&($time=='new'))?'LEFT JOIN {threadsread} tr ON tr.thread=p.thread AND tr.id={0}':'')."
	 WHERE p.date>{$mindate}", $loguserid, time()-$time, time()-900);
if (!$total)
{
	Alert($time=='new' ? 'No unread posts.' : 'No posts have been made during this timespan.', 'Notice');
	return;
}

$perpage = ($show=='posts') ? $loguser['postsperpage'] : $loguser['threadsperpage'];
$pagelinks = PageLinks(actionLink("lastposts", '', "time={$time}&show={$show}&from="), $perpage, $from, $total);

if($pagelinks)
	Write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

if ($show == 'threads')
{
	$mindate = ($time=='new') ? ($loguserid ? 'IFNULL(tr.date,0)' : '{2}') : '{1}';
	$rPosts = Query("SELECT
		t.title as ttit, t.id as tid,
		f.title as ftit, f.id as fid,
		(SELECT COUNT(*) FROM {posts} cp WHERE cp.thread=t.id AND cp.date>{$mindate}) numposts,
		fp.(id,date),
		fu.(_userfields),
		lp.(id,date),
		lu.(_userfields)
		FROM {threads} t
		INNER JOIN {forums} f ON t.forum = f.id AND f.id IN ({5c})
		".(($loguserid&&($time=='new'))?'LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={0}':'')."
		LEFT JOIN {posts} fp ON fp.thread=t.id AND fp.id=(SELECT MIN(fp2.id) FROM {posts} fp2 WHERE fp2.thread=t.id AND fp2.date>{$mindate})
		LEFT JOIN {users} fu ON fu.id=fp.user
		LEFT JOIN {posts} lp ON lp.thread=t.id AND lp.id=(SELECT MAX(lp2.id) FROM {posts} lp2 WHERE lp2.thread=t.id AND lp2.date>{$mindate})
		LEFT JOIN {users} lu ON lu.id=lp.user
		where !ISNULL(fp.id)
		order by lp_date desc limit {3u}, {4u}", $loguserid, time()-$time, time()-900, $from, $perpage, ForumsWithPermission('forum.viewforum'));

	while($post = Fetch($rPosts))
	{
		$thread = array();
		$thread["title"] = $post["ttit"];
		$thread["id"] = $post["tid"];

		$c = ($c+1) % 2;
		$theList .= 
	"
		<tr class=\"cell{$c}\">
			<td class=\"center\" style=\"width:20%;\">
				".actionLinkTag($post['ftit'], "forum", $post['fid'], "", $post['ftit'])."
			</td>
			<td>
				".makeThreadLink($thread)."
			</td>
			<td class=\"center\" style=\"width:50px;\">
				{$post['numposts']}
			</td>
			<td class=\"smallFonts center\" style=\"width:15%;min-width:150px;\">
				".formatdate($post['fp_date'])."<br>
				by ".userLink(getDataPrefix($post, 'fu_'))." ".actionLinkTag('&raquo;', 'post', $post['fp_id'])."
			</td>
			<td class=\"smallFonts center\" style=\"width:15%;min-width:150px;\">
				".formatdate($post['lp_date'])."<br>
				by ".userLink(getDataPrefix($post, 'lu_'))." ".actionLinkTag('&raquo;', 'post', $post['lp_id'])."
			</td>
		</tr>
	";
	}

	write(
	"
	<table class=\"margin outline\">
		<tr class=\"header0\">
			<th colspan=\"5\">".__("Last posts")."</th>
		</tr>
		<tr class=\"header1\">
			<th>".__("Forum")."</th>
			<th>".__("Thread")."</th>
			<th>".__("Posts")."</th>
			<th>".__("First post")."</th>
			<th>".__("Last post")."</th>
		</tr>
		{0}
	</table>
	", $theList);
}
else if ($show == 'listposts')
{
	$mindate = ($time=='new') ? ($loguserid ? 'IFNULL(tr.date,0)' : '{2}') : '{1}';
	$rPosts = Query("select
		p.id, p.date,
		u.(_userfields),
		t.title as ttit, t.id as tid,
		f.title as ftit, f.id as fid
		from {posts} p
		left join {users} u on u.id = p.user
		left join {threads} t on t.id = p.thread
		".(($loguserid&&($time=='new'))?'LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={0}':'')."
		left join {forums} f on t.forum = f.id
		where f.id IN ({5c}) and p.date > {$mindate}
		order by date desc limit {3u}, {4u}", $loguserid, time() - $time, time()-900, $from, $perpage, ForumsWithPermission('forum.viewforum'));

	while($post = Fetch($rPosts))
	{
		$thread = array();
		$thread["title"] = $post["ttit"];
		$thread["id"] = $post["tid"];

		$c = ($c+1) % 2;
		$theList .= format(
	"
		<tr class=\"cell{5}\">
			<td>
				{3}
			</td>
			<td>
				{4}
			</td>
			<td>
				{2}
			</td>
			<td>
				{1}
			</td>
			<td>
				&raquo; ".actionLinkTag($post['id'], "post", $post['id'])."
			</td>
		</tr>
	", $post['id'], formatdate($post['date']), UserLink(getDataPrefix($post, "u_")), 
		actionLinkTag($post["ftit"], "forum", $post["fid"], "", $post["ftit"]), makeThreadLink($thread), $c);
	}

	write(
	"
	<table class=\"margin outline\">
		<tr class=\"header0\">
			<th colspan=\"5\">".__("Last posts")."</th>
		</tr>
		<tr class=\"header1\">
			<th>".__("Forum")."</th>
			<th>".__("Thread")."</th>
			<th>".__("User")."</th>
			<th>".__("Date")."</th>
			<th></th>
		</tr>
		{0}
	</table>
	", $theList);
}
else
{
	$mindate = ($time=='new') ? ($loguserid ? 'IFNULL(tr.date,0)' : '{2}') : '{1}';
	$rPosts = Query("	SELECT
					p.*,
					pt.text, pt.revision, pt.user AS revuser, pt.date AS revdate,
					u.(_userfields), u.(rankset,title,picture,posts,postheader,signature,signsep,lastposttime,lastactivity,regdate,globalblock),
					ru.(_userfields),
					du.(_userfields),
					t.id thread, t.title threadname,
					f.id fid
				FROM
					{posts} p
					LEFT JOIN {posts_text} pt ON pt.pid = p.id AND pt.revision = p.currentrevision
					LEFT JOIN {users} u ON u.id = p.user
					LEFT JOIN {users} ru ON ru.id=pt.user
					LEFT JOIN {users} du ON du.id=p.deletedby
					LEFT JOIN {threads} t ON t.id=p.thread
					".(($loguserid&&($time=='new'))?'LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={0}':'')."
					LEFT JOIN {forums} f ON f.id=t.forum
					LEFT JOIN {categories} c ON c.id=f.catid
				WHERE p.date>{$mindate} AND f.id IN ({5c})
				ORDER BY date DESC LIMIT {3u}, {4u}", $loguserid, time()-$time, time()-900, $from, $perpage, ForumsWithPermission('forum.viewforum'));
	
	while($post = Fetch($rPosts))
		MakePost($post, POST_NORMAL, array('threadlink'=>1, 'tid'=>$post['thread'], 'fid'=>$post['fid'], 'noreplylinks'=>1));
}

if($pagelinks)
	Write("<div class=\"smallFonts pages\">".__("Pages:")." {0}</div>", $pagelinks);

?>
