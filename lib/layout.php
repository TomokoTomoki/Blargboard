<?php

// ----------------------------------------------------------------------------
// --- General layout functions
// ----------------------------------------------------------------------------

function gfxnumber($num)
{
	return $num;
	// 0123456789/NA-
	
	$sign = '';
	if ($num < 0)
	{
		$sign = '<span class="gfxnumber" style="background-position:-104px 0px;"></span>';
		$num = -$num;
	}
	
	$out = '';
	while ($num > 0)
	{
		$out = '<span class="gfxnumber" style="background-position:-'.(8*($num%10)).'px 0px;"></span>'.$out;
		$num = floor($num / 10);
	}
	
	return '<span style="white-space:nowrap;">'.$sign.$out.'</span>';
}

function makeNotifMenu($notif)
{
	$menu = '';
	foreach ($notif as $n)
	{
		$menu .= '<li>'.$n['text'].'<br><small>'.relativedate($n['date']).'</small></li>';
	}
	
	return $menu;
}


function mfl_forumBlock($fora, $catid, $selID, $indent)
{
	$ret = '';
	
	foreach ($fora[$catid] as $forum)
	{
		$ret .=
'				<option value="'.$forum['id'].'"'.($forum['id'] == $selID ? ' selected="selected"':'').'>'
	.str_repeat('&nbsp; &nbsp; ', $indent).htmlspecialchars($forum['title'])
	.'</option>
';
		if (!empty($fora[-$forum['id']]))
			$ret .= mfl_forumBlock($fora, -$forum['id'], $selID, $indent+1);
	}
	
	return $ret;
}

function makeForumList($fieldname, $selectedID)
{
	global $loguserid, $loguser, $forumBoards;

	$viewableforums = ForumsWithPermission('forum.viewforum');
	$viewhidden = HasPermission('user.viewhiddenforums');
	
	$rCats = Query("SELECT id, name, board FROM {categories} ORDER BY board, corder, id");
	$cats = array();
	while ($cat = Fetch($rCats))
		$cats[$cat['id']] = $cat;

	$rFora = Query("	SELECT
							f.id, f.title, f.catid
						FROM
							{forums} f
						WHERE f.id IN ({0c})".(!$viewhidden ? " AND f.hidden=0" : '')." AND f.redirect=''
						ORDER BY f.forder, f.id", $viewableforums);
						
	$fora = array();
	while($forum = Fetch($rFora))
		$fora[$forum['catid']][] = $forum;

	$theList = '';
	foreach ($cats as $cid=>$cat)
	{
		if (empty($fora[$cid]))
			continue;
			
		$cname = $cat['name'];
		if ($cat['board']) $cname = $forumBoards[$cat['board']].' - '.$cname;
			
		$theList .= 
'			<optgroup label="'.htmlspecialchars($cname).'">
'.mfl_forumBlock($fora, $cid, $selectedID, 0).
'			</optgroup>
';
	}

	return "<select id=\"$fieldname\" name=\"$fieldname\">$theList</select>";
}

function forumCrumbs($forum)
{
	global $forumBoards;
	$ret = array(actionLink('board') => __('Forums'));
	
	if ($forum['board'] != '')
		$ret[actionLink('board', $forum['board'])] = $forumBoards[$forum['board']];
	
	if (!$forum['id']) return $ret;
	
	$parents = Query("SELECT id,title FROM {forums} WHERE l<{0} AND r>{1} ORDER BY l", $forum['l'], $forum['r']);
	while ($p = Fetch($parents))
	{
		$public = HasPermission('forum.viewforum', $p['id'], true);
		$ret[actionLink('forum', $p['id'], '', $public?$p['title']:'')] = $p['title'];
	}
	
	$public = HasPermission('forum.viewforum', $forum['id'], true);
	$ret[actionLink('forum', $forum['id'], '', $public?$forum['title']:'')] = $forum['title'];
	return $ret;
}

function doThreadPreview($tid)
{
	$rPosts = Query("
		select
			{posts}.id, {posts}.date, {posts}.num, {posts}.deleted, {posts}.options, {posts}.mood, {posts}.ip,
			{posts_text}.text, {posts_text}.text, {posts_text}.revision,
			u.(_userfields)
		from {posts}
		left join {posts_text} on {posts_text}.pid = {posts}.id and {posts_text}.revision = {posts}.currentrevision
		left join {users} u on u.id = {posts}.user
		where thread={0} and deleted=0
		order by date desc limit 0, 20", $tid);

	if(NumRows($rPosts))
	{
		$posts = "";
		while($post = Fetch($rPosts))
		{
			$cellClass = ($cellClass+1) % 2;

			$poster = getDataPrefix($post, "u_");

			$nosm = $post['options'] & 2;
			$nobr = $post['options'] & 4;

			$posts .= Format(
	"
			<tr>
				<td class=\"cell2\" style=\"width: 15%; vertical-align: top;\">
					{1}
				</td>
				<td class=\"cell{0}\">
					<button style=\"float: right;\" onclick=\"insertQuote({2});\">".__("Quote")."</button>
					<button style=\"float: right;\" onclick=\"insertChanLink({2});\">".__("Link")."</button>
					{3}
				</td>
			</tr>
	",	$cellClass, UserLink($poster), $post['id'], CleanUpPost($post['text'], $poster['name'], $nosm));
		}
		Write(
	"
		<table class=\"outline margin\">
			<tr class=\"header0\">
				<th colspan=\"2\">".__("Thread review")."</th>
			</tr>
			{0}
		</table>
	",	$posts);
	}
}


// ----------------------------------------------------------------------------
// --- Layout-specific functions
// ----------------------------------------------------------------------------

if ($mobileLayout) require('layout_mobile.php');
else
{

function makeCrumbs($path, $links='')
{
	global $layout_crumbs;

	if(count($path) != 0)
	{
		$pathPrefix = array(actionLink(0) => Settings::get("breadcrumbsMainName"));

		$bucket = "breadcrumbs"; include("lib/pluginloader.php");

		$path = $pathPrefix + $path;
	}

	$first = true;

	$crumbs = "";
	foreach($path as $link=>$text)
	{
		$link = str_replace("&","&amp;",$link);
		$crumbs .= "<li><a href=\"".$link."\">".$text."</a></li>";
	}

	if($links)
		$links = "
	<div class=\"actionlinks\" style=\"float: right;\">
		<ul class=\"pipemenu smallFonts\">
			{$links}
		</ul>
	</div>";

	$layout_crumbs = "
<table class=\"outline breadcrumbs\">
<tr class=\"header1\">
<th>
	$links
	<ul class=\"crumbLinks\">$crumbs</ul>
</th>
</tr>
</table>";
}


// parent=0: index listing
function makeForumListing($parent, $board='')
{
	global $loguserid, $loguser, $usergroups;
		
	$viewableforums = ForumsWithPermission('forum.viewforum');
	$viewhidden = HasPermission('user.viewhiddenforums');

	$lastCatID = -1;
	$firstCat = true;
	$rFora = Query("	SELECT f.*,
							c.name cname,
							".($loguserid ? "(NOT ISNULL(i.fid))" : "0")." ignored,
							(SELECT COUNT(*) FROM {threads} t".($loguserid ? " LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={0}" : "")."
								WHERE t.forum=f.id AND t.lastpostdate>".($loguserid ? "IFNULL(tr.date,0)" : time()-900).") numnew,
							lu.(_userfields)
						FROM {forums} f
							LEFT JOIN {categories} c ON c.id=f.catid
							".($loguserid ? "LEFT JOIN {ignoredforums} i ON i.fid=f.id AND i.uid={0}" : "")."
							LEFT JOIN {users} lu ON lu.id=f.lastpostuser
						WHERE f.id IN ({1c}) AND ".($parent==0 ? 'c.board={2} AND f.catid>0' : 'f.catid={3}').(!$viewhidden ? " AND f.hidden=0" : '')."
						ORDER BY c.corder, c.id, f.forder, f.id", 
						$loguserid, $viewableforums, $board, -$parent);
	if (!NumRows($rFora))
		return;
						
	$rSubfora = Query("	SELECT f.*,
							".($loguserid ? "(NOT ISNULL(i.fid))" : "0")." ignored,
							(SELECT COUNT(*) FROM {threads} t".($loguserid ? " LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={0}" : "")."
								WHERE t.forum=f.id AND t.lastpostdate>".($loguserid ? "IFNULL(tr.date,0)" : time()-900).") numnew
						FROM {forums} f
							".($loguserid ? "LEFT JOIN {ignoredforums} i ON i.fid=f.id AND i.uid={0}" : "")."
						WHERE f.id IN ({2c}) AND ".($parent==0 ? 'f.catid<0' : 'f.catid!={1}').(!$viewhidden ? " AND f.hidden=0" : '')."
						ORDER BY f.forder, f.id", 
						$loguserid, -$parent, $viewableforums);
	$subfora = array();
	while ($sf = Fetch($rSubfora))
		$subfora[-$sf['catid']][] = $sf;

	
	$rMods = Query("	SELECT 
							p.(arg, applyto, id),
							u.(_userfields)
						FROM
							{permissions} p
							LEFT JOIN {users} u ON p.applyto=1 AND p.id=u.id
						WHERE SUBSTR(p.perm,1,4)={0} AND p.arg!=0 AND p.value=1
						GROUP BY p.applyto, p.id, p.arg
						ORDER BY p.applyto, p.id",
						'mod.');
	$mods = array();
	while($mod = Fetch($rMods))
		$mods[$mod['p_arg']][] = $mod['p_applyto'] ? getDataPrefix($mod, "u_") : array('groupid' => $mod['p_id']);

	$theList = "";
	while($forum = Fetch($rFora))
	{
		$skipThisOne = false;
		$bucket = "forumListMangler"; include("./lib/pluginloader.php");
		if($skipThisOne)
			continue;
			
		if ($forum['redirect'])
		{
			$redir = $forum['redirect'];
			if ($redir[0] == ':')
			{
				$redir = explode(':', $redir);
				$forumlink = actionLinkTag($forum['title'], $redir[1], $redir[2], $redir[3], $redir[4]);
				$forum['numthreads'] = '-';
				$forum['numposts'] = '-';
				
				if ($redir[1] == 'board')
				{
					$tboard = $redir[2];
					$f = Fetch(Query("SELECT MIN(l) minl, MAX(r) maxr FROM {forums} WHERE board={0}", $tboard));
					
					$forum['numthreads'] = 0;
					$forum['numposts'] = 0;
					$sforums = Query("	SELECT f.id, f.numthreads, f.numposts, f.lastpostid, f.lastpostuser, f.lastpostdate,
											".($loguserid ? "(NOT ISNULL(i.fid))" : "0")." ignored,
											(SELECT COUNT(*) FROM {threads} t".($loguserid ? " LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={0}" : "")."
												WHERE t.forum=f.id AND t.lastpostdate>".($loguserid ? "IFNULL(tr.date,0)" : time()-900).") numnew,
											lu.(_userfields)
										FROM {forums} f
											".($loguserid ? "LEFT JOIN {ignoredforums} i ON i.fid=f.id AND i.uid={0}" : "")."
											LEFT JOIN {users} lu ON lu.id=f.lastpostuser
										WHERE f.l>={1} AND f.r<={2}", 
										$loguserid, $f['minl'], $f['maxr']);
					while ($sforum = Fetch($sforums))
					{
						$forum['numthreads'] += $sforum['numthreads'];
						$forum['numposts'] += $sforum['numposts'];
						
						if (!HasPermission('forum.viewforum', $sforum['id']))
							continue;
						
						if (!$sforum['ignored'])
							$forum['numnew'] += $sforum['numnew'];
						
						if ($sforum['lastpostdate'] > $forum['lastpostdate'])
						{
							$forum['lastpostdate'] = $sforum['lastpostdate'];
							$forum['lastpostid'] = $sforum['lastpostid'];
							$forum['lastpostuser'] = $sforum['lastpostuser'];
							foreach ($sforum as $key=>$val)
							{
								if (substr($key,0,3) != 'lu_') continue;
								$forum[$key] = $val;
							}
						}
					}
				}
			}
			else
				$forumlink = '<a href="'.htmlspecialchars($redir).'">'.$forum['title'].'</a>';
		}
		else
			$forumlink = actionLinkTag($forum['title'], "forum",  $forum['id'], '', 
				HasPermission('forum.viewforum', $forum['id'], true) ? $forum['title'] : '');

		if($firstCat || $forum['catid'] != $lastCatID)
		{
			$theList .= format(
"
		".($firstCat ? '':'</tbody></table>')."
	<table class=\"outline margin\">
		<tbody>
			<tr class=\"header1\">
				<th style=\"width:32px;\"></th>
				<th>{0}</th>
				<th style=\"width:75px;\">".__("Threads")."</th>
				<th style=\"width:50px;\">".__("Posts")."</th>
				<th style=\"min-width:150px; width:15%;\">".__("Last post")."</th>
			</tr>
		</tbody>
		<tbody>
", ($parent==0)?$forum['cname']:'Subforums', $forum['catid']);
	
			$lastCatID = $forum['catid'];
			$firstCat = false;
		}

		$newstuff = 0;
		$NewIcon = '';
		$localMods = '';
		$subforaList = '';

		$newstuff = $forum['ignored'] ? 0 : $forum['numnew'];
		$ignoreClass = $forum['ignored'] ? " class=\"ignored\"" : "";

		if ($newstuff > 0)
			$NewIcon = "<div class=\"statusIcon new\"></div><br>".gfxnumber($newstuff);

		if (isset($mods[$forum['id']]))
		{
			foreach($mods[$forum['id']] as $user)
			{
				if ($user['groupid'])
					$localMods .= htmlspecialchars($usergroups[$user['groupid']]['name']).', ';
				else
					$localMods .= UserLink($user).', ';
			}
		}

		if($localMods)
			$localMods = "<br /><small>".__("Moderated by:")." ".substr($localMods,0,-2)."</small>";
			
		if (isset($subfora[$forum['id']]))
		{
			foreach ($subfora[$forum['id']] as $subforum)
			{
				$link = actionLinkTag($subforum['title'], 'forum', $subforum['id'], '', 
					HasPermission('forum.viewforum', $subforum['id'], true) ? $subforum['title'] : '');
				
				if ($subforum['ignored'])
					$link = '<span class="ignored">'.$link.'</span>';
				else if ($subforum['numnew'] > 0)
					$link = '<div class="statusIcon new"></div> '.$link;
					
				$subforaList .= $link.', ';
			}
		}
			
		if($subforaList)
			$subforaList = "<br /><small>".__("Subforums:")." ".substr($subforaList,0,-2)."</small>";

		if($forum['lastpostdate'])
		{
			$user = getDataPrefix($forum, "lu_");

			$lastLink = "";
			if($forum['lastpostid'])
				$lastLink = actionLinkTag("&raquo;", "post", $forum['lastpostid']);
			$lastLink = format("{0}<br />".__("by")." {1} {2}", formatdate($forum['lastpostdate']), UserLink($user), $lastLink);
		}
		else
			$lastLink = "----";


		$theList .=
"
		<tr class=\"cell1\">
			<td class=\"cell2 newMarker\">
				$NewIcon
			</td>
			<td>
				<h4 $ignoreClass>".
					$forumlink . "
				</h4>
				<span $ignoreClass>
					{$forum['description']}
					$localMods
					$subforaList
				</span>
			</td>
			<td class=\"center cell2\">
				{$forum['numthreads']}
			</td>
			<td class=\"center cell2\">
				{$forum['numposts']}
			</td>
			<td class=\"smallFonts center\">
				$lastLink
			</td>
		</tr>";
	}

	write(
"
	{0}
	</tbody>
</table>
",	$theList);
}

function listThread($thread, $cellClass, $dostickies = true, $showforum = false)
{
	global $haveStickies, $loguserid, $loguser, $misc;

	$forumList = "";

	$starter = getDataPrefix($thread, "su_");
	$last = getDataPrefix($thread, "lu_");
	
	$ispublic = HasPermission('forum.viewforum', $thread['forum'], true);
	$tags = ParseThreadTags($thread['title']);
	$urlname = $ispublic ? $tags[0] : '';

	$threadlink = actionLinkTag($tags[0], 'thread', $thread['id'], '', $urlname);
	$threadlink = (Settings::get("tagsDirection") === 'Left') ? $tags[1].' '.$threadlink : $threadlink.' '.$tags[1];


	$NewIcon = "";
	$newstuff = 0;
	if($thread['closed'])
		$NewIcon = "off";
	if($thread['replies'] >= $misc['hotcount'])
		$NewIcon .= "hot";
	if((!$loguserid && $thread['lastpostdate'] > time() - 900) ||
		($loguserid && $thread['lastpostdate'] > $thread['readdate']) &&
		!$isIgnored)
	{
		$NewIcon .= "new";
		$newstuff++;
	}
	else if(!$thread['closed'] && !$thread['sticky'] && Settings::get("oldThreadThreshold") > 0 && $thread['lastpostdate'] < time() - (2592000 * Settings::get("oldThreadThreshold")))
		$NewIcon = "old";

	if($NewIcon)
		$NewIcon = '<div class="statusIcon '.$NewIcon.'"></div>';
		//$NewIcon = "<img src=\"".resourceLink("img/status/".$NewIcon.".png")."\" alt=\"\"/>";

	if($thread['icon'])
	{
		//This is a hack, but given how icons are stored in the DB, I can do nothing about it without breaking DB compatibility.
		if(startsWith($thread['icon'], "img/"))
			$thread['icon'] = resourceLink($thread['icon']);
		$ThreadIcon = "<img src=\"".htmlspecialchars($thread['icon'])."\" alt=\"\" class=\"smiley\"/>";
	}
	else
		$ThreadIcon = "";


	if($thread['sticky'] == 0 && $haveStickies == 1 && $dostickies)
	{
		$haveStickies = 2;
		$forumList .= "<tr class=\"header1\"><th colspan=\"".($showforum?'8':'7')."\" style=\"height: 8px;\"></th></tr>";
	}
	if($thread['sticky'] && $haveStickies == 0) $haveStickies = 1;

	$poll = ($thread['poll'] ? "<img src=\"".resourceLink("img/poll.png")."\" alt=\"Poll\"/> " : "");


	$n = 4;
	$total = $thread['replies'];

	$ppp = $loguser['postsperpage'];
	if(!$ppp) $ppp = 20;

	$numpages = floor($total / $ppp);
	$pl = "";
	if($numpages <= $n * 2)
	{
		for($i = 1; $i <= $numpages; $i++)
			$pl .= " ".actionLinkTag($i+1, "thread", $thread['id'], "from=".($i * $ppp), $urlname);
	}
	else
	{
		for($i = 1; $i < $n; $i++)
		$pl .= " ".actionLinkTag($i+1, "thread", $thread['id'], "from=".($i * $ppp), $urlname);
		$pl .= " &hellip; ";
		for($i = $numpages - $n + 1; $i <= $numpages; $i++)
			$pl .= " ".actionLinkTag($i+1, "thread", $thread['id'], "from=".($i * $ppp), $urlname);
	}
	if($pl)
		$pl = " <span class=\"smallFonts\">[".
			actionLinkTag(1, "thread", $thread['id'], '', $urlname). $pl . "]</span>";
	
	$extra = ''; 
	if ($thread['forum'] == 3) // hax
	{
		$m = array();
		if (preg_match('@\(#?(\d+)\)@', $thread['title'], $m))
			$extra = ' <small>('.actionLinkTag('view profile', 'profile', $m[1]).')</small>';
	}

	$lastLink = "";
	if($thread['lastpostid'])
		$lastLink = " ".actionLinkTag("&raquo;", "post", $thread['lastpostid']);


	$forumcell = "";
	if($showforum)
	{
		$forumcell = "<td class=\"center\">".actionLinkTag(htmlspecialchars($thread["f_title"]), "forum", $thread["f_id"], "", $ispublic?$thread["f_title"]:'')."</td>";
	}
	$forumList .= "
	<tr class=\"cell$cellClass\">
		<td class=\"cell2 threadIcon\"> $NewIcon</td>
		<td class=\"threadIcon\" style=\"border-right: 0px none;\">
			 $ThreadIcon
		</td>
		<td style=\"border-left: 0px none;\">
			$poll
			$threadlink
			$pl
			$extra
		</td>
		$forumcell
		<td class=\"center\">
			".UserLink($starter)."
		</td>
		<td class=\"center\">
			{$thread['replies']}
		</td>
		<td class=\"center\">
			{$thread['views']}
		</td>
		<td class=\"smallFonts center\">
			".formatdate($thread['lastpostdate'])."<br />
			".__("by")." ".UserLink($last)." {$lastLink}</td>
	</tr>";

	return $forumList;
}

function makeAnncBar()
{
	global $loguserid;
	
	$anncforum = Settings::get('anncForum');
	if ($anncforum > 0)
	{
		$annc = Query("	SELECT 
							t.id, t.title, t.icon, t.poll, t.forum,
							t.date anncdate,
							".($loguserid ? "tr.date readdate," : '')."
							u.(_userfields)
						FROM 
							{threads} t 
							".($loguserid ? "LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={1}" : '')."
							LEFT JOIN {users} u ON u.id=t.user
						WHERE forum={0}
						ORDER BY anncdate DESC LIMIT 1", $anncforum, $loguserid);
								
		if ($annc && NumRows($annc))
		{
			$annc = Fetch($annc);
			
			$status = '&nbsp;';
			if ((!$loguserid && $annc['anncdate'] > (time()-900)) ||
				($loguserid && $annc['anncdate'] > $annc['readdate']))
				$status = "<div class=\"statusIcon new\"></div>";
			
			$poll = ($annc['poll'] ? "<img src=\"".resourceLink('img/poll.png')."\" alt=\"Poll\"/> " : '');
			
			$user = getDataPrefix($annc, 'u_');
			
			echo "
		<table class=\"outline margin width100\">
			<tr class=\"header1\">
				<th colspan=\"2\">
					Announcement
				</th>
			</tr>
			<tr class=\"cell1\">
				<td class=\"cell2 newMarker\">
					{$status}
				</td>
				<td>
					{$poll}".makeThreadLink($annc)." &mdash; Posted by ".userLink($user)." on ".formatdate($annc['anncdate'])."
				</td>
			</tr>
		</table>
";
		}
	}
}

} // end non-mobile layout code

?>
