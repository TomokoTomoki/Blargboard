<?php

function makeCrumbs($path, $links='')
{
	global $layout_crumbs, $layout_actionlinks;

	if(count($path) != 0)
	{
		$pathPrefix = array(actionLink(0) => Settings::get("breadcrumbsMainName"));

		$bucket = "breadcrumbs"; include("lib/pluginloader.php");

		$path = $pathPrefix + $path;
	}
	
	$urls = array_keys($path);

	if (count($path) > 1)
	{
		$prevurl = $urls[count($urls)-2];
		$url = $urls[count($urls)-1];
		$prevurl = str_replace("&","&amp;",$prevurl);
		$prevurl = addslashes($prevurl);
		$link = str_replace("&","&amp;",$url);
		$layout_crumbs = '<button onclick="window.location=\''.$prevurl.'\';">&lt;</button> <a href="'.$link.'">'.$path[$url].'</a>';
	}
	else
		$layout_crumbs = '';
	
	$layout_actionlinks = $links;
}

function makeForumListing($parent, $board='')
{
	global $loguserid, $loguser;
		
	$viewableforums = ForumsWithPermission('forum.viewforum');
	$viewhidden = HasPermission('user.viewhiddenforums');

	$lastCatID = -1;
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
						WHERE f.id IN ({3c}) AND ".($parent==0 ? 'f.catid>0 AND c.board={2}' : 'f.catid={1}').(!$viewhidden ? " AND f.hidden=0" : '')."
						ORDER BY c.corder, c.id, f.forder, f.id", 
						$loguserid, -$parent, $board, $viewableforums);
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

	$theList = "";
	$firstCat = true;
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
				
				if ($redir[1] == 'board')
				{
					$tboard = $redir[2];
					$f = Fetch(Query("SELECT MIN(l) minl, MAX(r) maxr FROM {forums} WHERE board={0}", $tboard));
					
					$sforums = Query("	SELECT f.id, f.lastpostid, f.lastpostuser, f.lastpostdate,
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
			$lastCatID = $forum['catid'];
			$theList .= format(
"
		".($firstCat ? '':'</tbody></table>')."
	<table class=\"outline margin\">
		<tbody>
			<tr class=\"header1\">
				<th>{0}</th>
			</tr>
		</tbody>
		<tbody>
", ($parent==0)?$forum['cname']:'Subforums');
			$firstCat = false;
		}

		$newstuff = 0;
		$NewIcon = "";
		$subforaList = '';

		$newstuff = $forum['ignored'] ? 0 : $forum['numnew'];
		$ignoreClass = $forum['ignored'] ? " class=\"ignored\"" : "";

		if ($newstuff > 0)
			$NewIcon = '<div class="statusIcon new"></div>';
			
		if (isset($subfora[$forum['id']]))
		{
			foreach ($subfora[$forum['id']] as $subforum)
			{
				$link = actionLinkTag($subforum['title'], 'forum', $subforum['id']);
				
				if ($subforum['ignored'])
					$link = '<span class="ignored">'.$link.'</span>';
				else if ($subforum['numnew'] > 0)
					$link = '<div class="statusIcon new"></div> '.$link;
					
				$subforaList .= $link.', ';
			}
		}
			
		if($subforaList)
			$subforaList = "<br />".__("Subforums:")." ".substr($subforaList,0,-2);

		if($forum['lastpostdate'])
		{
			$user = getDataPrefix($forum, "lu_");

			$lastLink = '<br>'.actionLinkTag("Last post", "post", $forum['lastpostid'])." by ".userLink($user)." on ".formatdate($forum['lastpostdate']);
		}
		else
			$lastLink = '<br>No posts';

		// onclick=\"window.location='".actionLink('forum', $forum['id'], '', $ispublic?$forum['title']:'')."';\"
		$theList .=
"
		<tr class=\"cell1\">
			<td>
				".$NewIcon.' '.$forumlink."<br>
				<small>
					{$forum['description']}
					$lastLink
					$subforaList
				</small>
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
		$NewIcon = "<div class=\"statusIcon $NewIcon\"></div>";

	if($thread['sticky'] == 0 && $haveStickies == 1 && $dostickies)
	{
		$haveStickies = 2;
		$forumList .= "<tr class=\"header1\"><th style=\"height: 6px;\"></th></tr>";
	}
	if($thread['sticky'] && $haveStickies == 0) $haveStickies = 1;

	$poll = ($thread['poll'] ? "<img src=\"img/poll.png\" alt=\"Poll\"/> " : "");


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

	$lastLink = "";
	if($thread['lastpostid'])
		$lastLink = '<br>'.actionLinkTag('Last post', "post", $thread['lastpostid'])." by ".UserLink($last)." on ".formatdate($thread['lastpostdate']);

	$forumcell = "";
	if($showforum)
	{
		$forumcell = " in ".actionLinkTag(htmlspecialchars($thread["f_title"]), "forum", $thread["f_id"]);
	}
	$forumList .= "
	<tr class=\"cell$cellClass\">
		<td style=\"border-left: 0px none;\">
			$NewIcon
			$poll
			$threadlink $pl<br>
			<small>By ".UserLink($starter).$forumcell." -- ".Plural($thread['replies'], 'reply')."
			$lastLink</small>
		</td>
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
			
			$status = '';
			if ((!$loguserid && $annc['anncdate'] > (time()-900)) ||
				($loguserid && $annc['anncdate'] > $annc['readdate']))
				$status = "<div class=\"statusIcon new\"></div> ";
			
			$poll = ($annc['poll'] ? "<img src=\"".resourceLink('img/poll.png')."\" alt=\"Poll\"/> " : '');
			
			$user = getDataPrefix($annc, 'u_');
			
			echo "
		<table class=\"outline margin width100\">
			<tr class=\"header1\">
				<th>
					Announcement
				</th>
			</tr>
			<tr class=\"cell1\">
				<td>
					{$status}{$poll}".makeThreadLink($annc)."<br><small>Posted by ".userLink($user)." on ".formatdate($annc['anncdate'])."</small>
				</td>
			</tr>
		</table>
";
		}
	}
}

?>