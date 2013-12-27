<?php

// this post box is totally not Acmlmboard but it fits much better in small resolutions

function makePost($post, $type, $params=array())
{
	global $loguser, $loguserid, $theme, $hacks, $isBot, $blocklayouts, $postText, $sideBarStuff, $sideBarData, $salt, $dataDir, $dataUrl;

	$sideBarStuff = "";
	$poster = getDataPrefix($post, "u_");

	if(isset($_GET['pid']))
		$highlight = (int)$_GET['pid'];

	if($post['deleted'] && $type == POST_NORMAL)
	{
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
		<table class=\"outline margin\" id=\"post{0}\">
			<tr class=\"cell0\">
				<td class=\"right\">
					<div style=\"float:left\">
						{1} - <small>deleted</small>
					</div>
					<small>{2}</small>
				</td>
			</tr>
		</table>
",	$post['id'], userLink($poster), $links->build()
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

		$meta = formatdate($post['date']);

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
			if (HasPermission('mod.editposts', $forum))
				$meta .= " (<a href=\"javascript:void(0);\" onclick=\"showRevisions(".$post['id'].")\">".format(__("rev. {0}"), $post['revision'])."</a>)";
			else
				$meta .= " (".format(__("rev. {0}"), $post['revision']).")";
		}
		//</revisions>
	}

	// OTHER STUFF

	if($type == POST_NORMAL)
		$anchor = "<a name=\"".$post['id']."\"></a>";

	$highlightClass = "";
	if($post['id'] == $highlight)
		$highlightClass = "highlightedPost";

	$postText = makePostText($post);

	//PRINT THE POST!
	
	$links = $links->build();

	echo "
		{$anchor}
		<table class=\"outline margin $highlightClass\" id=\"post${post['id']}\">
			<tr class=\"cell0\">
				<td>
					".UserLink($poster)." -
					<small><span id=\"meta_${post['id']}\">
						$meta
					</span>
					<span style=\"text-align:left; display: none;\" id=\"dyna_${post['id']}\">
						Hi.
					</span></small>
				</td>
			</tr>
			<tr class=\"cell1\">
				<td id=\"post_${post['id']}\">
					$postText
				</td>
			</tr>
			".($links ? "
			<tr class=\"cell0\">
				<td class=\"right\">
					<small>{$links}</small>
				</td>
			</tr>" : '')."
		</table>";
}

?>