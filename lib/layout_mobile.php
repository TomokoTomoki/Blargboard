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