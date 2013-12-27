<?php
//  AcmlmBoard XD support - Post functions


function ParseThreadTags($title)
{
	preg_match_all("/\[(.*?)\]/", $title, $matches);
	foreach($matches[1] as $tag)
	{
		$title = str_replace("[".$tag."]", "", $title);
		$tag = htmlspecialchars(strtolower($tag));

		//Start at a hue that makes "18" red.
		$hash = -105;
		for($i = 0; $i < strlen($tag); $i++)
			$hash += ord($tag[$i]);

		//That multiplier is only there to make "nsfw" and "18" the same color.
		$color = "hsl(".(($hash * 57) % 360).", 70%, 40%)";

		$tags .= "<span class=\"threadTag\" style=\"background-color: ".$color.";\">".$tag."</span>";
	}
	if($tags)
		$tags = " ".$tags;

	$title = str_replace("<", "&lt;", $title);
	$title = str_replace(">", "&gt;", $title);
	return array(trim($title), $tags);
}

function filterPollColors($input)
{
	return preg_replace("@[^#0123456789abcdef]@si", "", $input);
}

function loadBlockLayouts()
{
	global $blocklayouts, $loguserid;

	if(isset($blocklayouts))
		return;

	$rBlocks = Query("select * from {blockedlayouts} where blockee = {0}", $loguserid);
	$blocklayouts = array();

	while($block = Fetch($rBlocks))
		$blocklayouts[$block['user']] = 1;
}

function getSyndrome($activity)
{
	include("syndromes.php");
	$soFar = "";
	foreach($syndromes as $minAct => $syndrome)
		if($activity >= $minAct)
			$soFar = "<em style=\"color: ".$syndrome[1].";\">".$syndrome[0]."</em><br />";
	return $soFar;
}

function applyTags($text, $tags)
{
	if(!stristr($text, "&"))
		return $text;
	$s = $text;
	foreach($tags as $tag => $val)
		$s = str_replace("&".$tag."&", $val, $s);
	if(is_numeric($tags['numposts']))
		$s = preg_replace('@&(\d+)&@sie', 'max($1 - '.$tags['numposts'].', 0)', $s);
	else
		$s = preg_replace("'&(\d+)&'si", "preview", $s);
	return $s;
}


$activityCache = array();
function getActivity($id)
{
	global $activityCache;

	if(!isset($activityCache[$id]))
		$activityCache[$id] = FetchResult("select count(*) from {posts} where user = {0} and date > {1}", $id, (time() - 86400));

	return $activityCache[$id];
}

$layouCache = array();

function makePostText($post)
{
	global $loguser, $loguserid, $theme, $hacks, $isBot, $postText, $sideBarStuff, $sideBarData, $salt, $layoutCache, $blocklayouts;

	LoadBlockLayouts();
	$poster = getDataPrefix($post, "u_");
	$isBlocked = $poster['globalblock'] || $loguser['blocklayouts'] || $post['options'] & 1 || isset($blocklayouts[$poster['id']]);

	$noSmilies = $post['options'] & 2;

	//Do Ampersand Tags
	$tags = array
	(
//This tag breaks because of layout caching.
//		"postnum" => $post['num'],
		"postcount" => $poster['posts'],
		"numdays" => floor((time()-$poster['regdate'])/86400),
		"date" => formatdate($post['date']),
		"rank" => GetRank($poster["rankset"], $poster["posts"]),
	);
	$bucket = "amperTags"; include("./lib/pluginloader.php");

	$postText = $post['text'];
	$postText = ApplyTags($postText, $tags);
	$postText = CleanUpPost($postText, $poster['name'], $noSmilies, false);

	//Post header and footer.
	$magicString = "###POSTTEXTGOESHEREOMG###";
	$separator = "";

	if($isBlocked)
		$postLayout = $magicString;
	else
	{
		if(!isset($layoutCache[$poster["id"]]))
		{
			if (!$poster['postheader'] && $poster['signature'])
				$poster['signature'] = '<small>'.$poster['signature'].'</small>';
			
			$postLayout = $poster['postheader'].$magicString.$poster['signature'];
			$postLayout = ApplyTags($postLayout, $tags);
			$postLayout = CleanUpPost($postLayout, $poster['name'], $noSmilies, false);
			$layoutCache[$poster["id"]] = $postLayout;
		}
		else
			$postLayout = $layoutCache[$poster["id"]];

		if($poster['signature'])
			if(!$poster['signsep'])
				$separator = "<br />_________________________<br />";
			else
				$separator = "<br />";
	}

	$postText = str_replace($magicString, "<!-- LOL -->".$postText.$separator, $postLayout);
	return $postText;
}

define('POST_NORMAL', 0);			// standard post box
define('POST_PM', 1);				// PM post box
define('POST_DELETED_SNOOP', 2);	// post box with close/undelete (for mods 'view deleted post' feature)
define('POST_SAMPLE', 3);			// sample post box (profile sample post, newreply post preview, etc)

$sideBarStuff = "";
$sideBarData = 0;

if ($mobileLayout) require('post_mobile.php');
else
{

// $post: post data (typically returned by SQL queries or forms)
// $type: one of the POST_XXX constants
// $params: an array of extra parameters, depending on the post box type. Possible parameters:
//		* tid: the ID of the thread the post is in (POST_NORMAL and POST_DELETED_SNOOP only)
//		* fid: the ID of the forum the thread containing the post is in (POST_NORMAL and POST_DELETED_SNOOP only)
// 		* threadlink: if set, a link to the thread is added next to 'Posted on blahblah' (POST_NORMAL and POST_DELETED_SNOOP only)
//		* noreplylinks: if set, no links to newreply.php (Quote/ID) are placed in the metabar (POST_NORMAL only)
//		* forcepostnum: if set, forces sidebar to show "Posts: X/X" (POST_SAMPLE only)
//		* metatext: if non-empty, this text is displayed in the metabar instead of 'Sample post' (POST_SAMPLE only)
function makePost($post, $type, $params=array())
{
	global $loguser, $loguserid, $usergroups, $theme, $hacks, $isBot, $blocklayouts, $postText, $sideBarStuff, $sideBarData, $salt, $dataDir, $dataUrl;

	$sideBarStuff = "";
	$poster = getDataPrefix($post, "u_");
	LoadBlockLayouts();
	$isBlocked = $poster['globalblock'] || $loguser['blocklayouts'] || $post['options'] & 1 || isset($blocklayouts[$poster['id']]);

	if(isset($_GET['pid']))
		$highlight = (int)$_GET['pid'];

	if($post['deleted'] && $type == POST_NORMAL)
	{
		$meta = format(__("Posted on {0}"), formatdate($post['date']));
		$meta .= __(', deleted');
		if ($post['deletedby'])
		{
			$db_link = UserLink(getDataPrefix($post, "du_"));
			$meta .= __(' by ').$db_link;

			if ($post['reason'])
				$meta .= ': '.htmlspecialchars($post['reason']);
		}

		$links = new PipeMenu();

		if (HasPermission('mod.deleteposts', $params['fid']))
		{
			$links->add(new PipeMenuLinkEntry(__("Undelete"), "editpost", $post['id'], "delete=2&key=".$loguser['token']));
			$links->add(new PipeMenuHtmlEntry("<a href=\"#\" onclick=\"replacePost(".$post['id'].",true); return false;\">".__("View")."</a>"));
		}

		$links->add(new PipeMenuTextEntry('#'.$post['id']));
		
		if (HasPermission('admin.viewips'))
			$links->add(new PipeMenuTextEntry($post['ip']));
		
		write(
"
		<table class=\"post margin deletedpost\" id=\"post{0}\">
			<tr>
				<td class=\"side userlink\" id=\"{0}\">
					{1}
				</td>
				<td class=\"smallFonts meta right\">
					<div style=\"float:left\">
						{2}
					</div>
					{3}
				</td>
			</tr>
		</table>
",	$post['id'], userLink($poster), $meta, $links->build()
);
		return;
	}

	$links = new PipeMenu();

	if ($type == POST_SAMPLE)
		$meta = $params['metatext'] ? $params['metatext'] : __("Sample post");
	else
	{
		$forum = $params['fid'];
		$thread = $params['tid'];
		
		$notclosed = (!$post['closed'] || HasPermission('mod.closethreads', $forum));

		if (!$isBot)
		{
			if ($type == POST_DELETED_SNOOP)
			{
				$links->add(new PipeMenuTextEntry(__("Post deleted")));
				
				if ($notclosed && HasPermission('mod.deleteposts', $forum))
					$links->add(new PipeMenuLinkEntry(__("Undelete"), "editpost", $post['id'], "delete=2&key=".$loguser['token']));
				
				$links->add(new PipeMenuHtmlEntry("<a href=\"#\" onclick=\"replacePost(".$post['id'].",false); return false;\">".__("Close")."</a>"));
				
				$links->add(new PipeMenuTextEntry('#'.$post['id']));
				if (HasPermission('admin.viewips'))
					$links->add(new PipeMenuTextEntry($post['ip']));
			}
			else if ($type == POST_NORMAL)
			{
				$links->add(new PipeMenuLinkEntry(__("Link"), "post", $post['id']));

				if ($notclosed)
				{
					if ($loguserid && HasPermission('forum.postreplies', $forum) && !$params['noreplylinks'])
						$links->add(new PipeMenuLinkEntry(__("Quote"), "newreply", $thread, "quote=".$post['id']));

					$editrights = 0;
					if (($poster['id'] == $loguserid && HasPermission('user.editownposts')) || HasPermission('mod.editposts', $forum))
					{
						$links->add(new PipeMenuLinkEntry(__("Edit"), "editpost", $post['id']));
						$editrights++;
					}
					
					if (($poster['id'] == $loguserid && HasPermission('user.deleteownposts')) || HasPermission('mod.deleteposts', $forum))
					{
						if ($post['id'] != $post['firstpostid'])
						{
							$link = actionLink('editpost', $post['id'], 'delete=1&key='.$loguser['token']);
							$onclick = HasPermission('mod.deleteposts', $forum) ? 
								" onclick=\"deletePost(this);return false;\"" : ' onclick="if(!confirm(\'Really delete this post?\'))return false;"';
							$links->add(new PipeMenuHtmlEntry("<a href=\"{$link}\"{$onclick}>".__('Delete')."</a>"));
						}
						$editrights++;
					}
					
					if ($editrights < 2 && HasPermission('user.reportposts'))
						$links->add(new PipeMenuLinkEntry(__('Report'), 'reportpost', $post['id']));
				}
					
				$links->add(new PipeMenuTextEntry('#'.$post['id']));
				if (HasPermission('admin.viewips'))
					$links->add(new PipeMenuTextEntry($post['ip']));
				
				$bucket = "topbar"; include("./lib/pluginloader.php");
			}
		}

		if ($type == POST_PM)
			$message = __("Sent on {0}");
		else
			$message = __("Posted on {0}");

		$meta = format($message, formatdate($post['date']));

		//Threadlinks for listpost.php
		if ($params['threadlink'])
		{
			$thread = array();
			$thread['id'] = $post['thread'];
			$thread['title'] = $post['threadname'];
			$thread['forum'] = $post['fid'];

			$meta .= " ".__("in")." ".makeThreadLink($thread);
		}

		//Revisions
		if($post['revision'])
		{
			if ($post['revuser'])
			{
				$ru_link = UserLink(getDataPrefix($post, "ru_"));
				$revdetail = " ".format(__("by {0} on {1}"), $ru_link, formatdate($post['revdate']));
			}
			else
				$revdetail = '';

			if (HasPermission('mod.editposts', $forum))
				$meta .= " (<a href=\"javascript:void(0);\" onclick=\"showRevisions(".$post['id'].")\">".format(__("rev. {0}"), $post['revision'])."</a>".$revdetail.")";
			else
				$meta .= " (".format(__("rev. {0}"), $post['revision']).$revdetail.")";
		}
		//</revisions>
	}


	// POST SIDEBAR
	
	// quit abusing custom syndromes you unoriginal fuckers
	$poster['title'] = preg_replace('@Affected by \'?.*?Syndrome\'?@si', '', $poster['title']);

	$sideBarStuff .= GetRank($poster["rankset"], $poster["posts"]);
	if($sideBarStuff)
		$sideBarStuff .= "<br />";
	if($poster['title'])
		$sideBarStuff .= strip_tags(CleanUpPost($poster['title'], "", true), "<b><strong><i><em><span><s><del><img><a><br/><br><small>")."<br />";
	else
		$sideBarStuff .= htmlspecialchars($usergroups[$poster['primarygroup']]['title']).'<br />';

	$sideBarStuff .= GetSyndrome(getActivity($poster["id"]));

	if($post['mood'] > 0)
	{
		if(file_exists("${dataDir}avatars/".$poster['id']."_".$post['mood']))
			$sideBarStuff .= "<img src=\"${dataUrl}avatars/".$poster['id']."_".$post['mood']."\" alt=\"\" />";
	}
	else
	{
		if($poster["picture"] == "#INTERNAL#")
			$sideBarStuff .= "<img src=\"${dataUrl}avatars/".$poster['id']."\" alt=\"\" />";
		else if($poster["picture"])
			$sideBarStuff .= "<img src=\"".htmlspecialchars($poster["picture"])."\" alt=\"\" />";
	}

	$lastpost = ($poster['lastposttime'] ? timeunits(time() - $poster['lastposttime']) : "none");
	$lastview = timeunits(time() - $poster['lastactivity']);

	if(!$params['forcepostnum'] && ($type == POST_PM || $type == POST_SAMPLE || !$post['num']))
		$sideBarStuff .= "<br />\n".__("Posts:")." ".$poster['posts'];
	else
		$sideBarStuff .= "<br />\n".__("Posts:")." ".$post['num']."/".$poster['posts'];

	$sideBarStuff .= "<br />\n".__("Since:")." ".cdate($loguser['dateformat'], $poster['regdate'])."<br />";

	$bucket = "sidebar"; include("./lib/pluginloader.php");

	if(Settings::get("showExtraSidebar"))
	{
		$sideBarStuff .= "<br />\n".__("Last post:")." ".$lastpost;
		$sideBarStuff .= "<br />\n".__("Last view:")." ".$lastview;

		if($poster['lastactivity'] > time() - 300)
			$sideBarStuff .= "<br />\n".__("User is <strong>online</strong>");
	}

	// OTHER STUFF

	if(!$isBlocked)
	{
		$pTable = "table".$poster['id'];
		$row1 = "row".$poster['id']."_1";
		$row2 = "row".$poster['id']."_2";
		$topBar1 = "topbar".$poster['id']."_1";
		$topBar2 = "topbar".$poster['id']."_2";
		$sideBar = "sidebar".$poster['id'];
		$mainBar = "mainbar".$poster['id'];
	}

	$highlightClass = "";
	if($post['id'] == $highlight)
		$highlightClass = "highlightedPost";

	$postText = makePostText($post);
	
	$paddingkiller = '';
	if ($post['u_postheader'] && !$isBlocked) 
		$paddingkiller=' style="padding:0px!important;"';

	//PRINT THE POST!

	echo "
		<table class=\"post margin $highlightClass $pTable\" id=\"post${post['id']}\">
			<tr class=\"$row1\">
				<td class=\"side userlink $topBar1\">
					".UserLink($poster)."
				</td>
				<td class=\"meta right $topBar2\">
					<div style=\"float: left;\" id=\"meta_${post['id']}\">
						$meta
					</div>
					<div style=\"float: left; text-align:left; display: none;\" id=\"dyna_${post['id']}\">
						Hi.
					</div>
					" . $links->build() . "
				</td>
			</tr>
			<tr class=\"".$row2."\">
				<td class=\"side $sideBar\">
					<div class=\"smallFonts\">
						$sideBarStuff
					</div>
				</td>
				<td class=\"post $mainBar\" id=\"post_${post['id']}\"{$paddingkiller}>
					$postText
				</td>
			</tr>
		</table>";
}

} // end non-mobile post functions

?>
