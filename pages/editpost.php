<?php
//  AcmlmBoard XD - Post editing page
//  Access: users

$title = __("Edit post");

if(!$loguserid)
	Kill(__("You must be logged in to edit your posts."));

$pid = (int)$_REQUEST['id'];

$rPost = Query("
	SELECT
		{posts}.*,
		{posts_text}.text
	FROM {posts}
		LEFT JOIN {posts_text} ON {posts_text}.pid = {posts}.id AND {posts_text}.revision = {posts}.currentrevision
	WHERE id={0}", $pid);

if(NumRows($rPost))
{
	$post = Fetch($rPost);
	$tid = $post['thread'];
}
else
	Kill(__("Unknown post ID."));

$rUser = Query("select * from {users} where id={0}", $post['user']);
if(NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill(__("Unknown user ID."));

$rThread = Query("select * from {threads} where id={0}", $tid);
if(NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill(__("Unknown thread ID."));

$rFora = Query("select * from {forums} where id={0}", $thread['forum']);
if(NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill(__("Unknown forum ID."));
	
if (!HasPermission('forum.viewforum', $forum['id']))
	Kill(__('You may not access this forum.'));

$fid = $forum['id'];
$OnlineUsersFid = $fid;

$isHidden = !HasPermission('forum.viewforum', $forum['id'], true);

$isFirstPost = ($thread['firstpostid'] == $post['id']);
$isLastPost = ($thread['lastpostid'] == $post['id']);

if($thread['closed'] && !HasPermission('mod.closethreads', $fid))
	Kill(__("This thread is closed."));

if((int)$_GET['delete'] == 1)
{
	if ($_GET['key'] != $loguser['token']) Kill(__("No."));
	
	if ($isFirstPost)
		Kill(__("You may not delete a thread's first post."));
	
	if(!HasPermission('mod.deleteposts', $fid))
	{
		if ($post['user'] != $loguserid || !HasPermission('user.deleteownposts'))
			Kill(__("You are not allowed to delete this post."));
		
		$_GET['reason'] = '';
	}
	$rPosts = Query("update {posts} set deleted=1,deletedby={0},reason={1} where id={2} limit 1", $loguserid, $_GET['reason'], $pid);

	die(header("Location: ".actionLink("post", $pid)));
}
else if((int)$_GET['delete'] == 2)
{
	if ($_GET['key'] != $loguser['token']) Kill(__("No."));
	
	if(!HasPermission('mod.deleteposts', $fid))
		Kill(__("You're not allowed to undelete posts."));
	$rPosts = Query("update {posts} set deleted=0 where id={0} limit 1", $pid);

	die(header("Location: ".actionLink("post", $pid)));
}

if ($post['deleted'])
	Kill(__("This post has been deleted."));

if(($post['user'] != $loguserid || !HasPermission('user.editownposts')) && !HasPermission('mod.editposts', $fid))
	Kill(__("You are not allowed to edit this post."));

$tags = ParseThreadTags($thread['title']);
MakeCrumbs(forumCrumbs($forum) + array(actionLink("thread", $tid, '', $isHidden?'':$tags[0]) => $tags[0], '' => __("Edit post")), $links);

write("
	<script type=\"text/javascript\">
			window.addEventListener(\"load\",  hookUpControls, false);
	</script>
");

if(isset($_POST['actionpreview']))
{
	$previewPost['text'] = $_POST["text"];
	$previewPost['num'] = $post['num'];
	$previewPost['id'] = "_";
	$previewPost['options'] = 0;
	if($_POST['nopl']) $previewPost['options'] |= 1;
	if($_POST['nosm']) $previewPost['options'] |= 2;
	$previewPost['mood'] = (int)$_POST['mood'];
	foreach($user as $key => $value)
		$previewPost["u_".$key] = $value;
	MakePost($previewPost, POST_SAMPLE, array('forcepostnum'=>1, 'metatext'=>__("Preview")));
}
else if(isset($_POST['actionpost']))
{
	if ($_POST['key'] != $loguser['token']) Kill(__("No."));

	$rejected = false;

	if(!$_POST['text'])
	{
		Alert(__("Enter a message and try again."), __("Your post is empty."));
		$rejected = true;
	}

	if(!$rejected)
	{
		$bucket = "checkPost"; include("./lib/pluginloader.php");
	}

	if(!$rejected)
	{
		$options = 0;
		if($_POST['nopl']) $options |= 1;
		if($_POST['nosm']) $options |= 2;

		$now = time();
		$rev = fetchResult("select max(revision) from {posts_text} where pid={0}", $pid);
		$rev++;
		$rPostsText = Query("insert into {posts_text} (pid,text,revision,user,date) values ({0}, {1}, {2}, {3}, {4})",
							$pid, $_POST["text"], $rev, $loguserid, $now);

		$rPosts = Query("update {posts} set options={0}, mood={1}, currentrevision = currentrevision + 1 where id={2} limit 1",
						$options, (int)$_POST['mood'], $pid);

		// mark the thread as new if we edited the last post
		if($isLastPost)
			Query("DELETE FROM {threadsread} WHERE thread={0} AND id!={1}", $thread['id'], $loguserid);

		Report("Post edited by [b]".$loguser['name']."[/] in [b]".$thread['title']."[/] (".$forum['title'].") -> [g]#HERE#?pid=".$pid, $isHidden);
		$bucket = 'editpost'; include("lib/pluginloader.php");

		die(header("Location: ".actionLink("post", $pid)));
	}
}

if(isset($_POST['actionpreview']) || isset($_POST['actionpost']))
{
	$prefill = $_POST['text'];
	if($_POST['nopl']) $nopl = "checked=\"checked\"";
	if($_POST['nosm']) $nosm = "checked=\"checked\"";
}
else
{
	$prefill = $post['text'];
	if($post['options'] & 1) $nopl = "checked=\"checked\"";
	if($post['options'] & 2) $nosm = "checked=\"checked\"";
	$_POST['mood'] = $post['mood'];
}

if($_POST['mood'])
	$moodSelects[(int)$_POST['mood']] = "selected=\"selected\" ";
$moodOptions = Format("<option {0}value=\"0\">".__("[Default avatar]")."</option>\n", $moodSelects[0]);
$rMoods = Query("select mid, name from {moodavatars} where uid={0} order by mid asc", $post['user']);
while($mood = Fetch($rMoods))
	$moodOptions .= Format("<option {0}value=\"{1}\">{2}</option>\n", $moodSelects[$mood['mid']], $mood['mid'], htmlspecialchars($mood['name']));

Write(
"
				<form name=\"postform\" action=\"".actionLink("editpost")."\" method=\"post\">
					<table class=\"outline margin width100\">
						<tr class=\"header1\">
							<th colspan=\"2\">
								".__("Edit Post")."
							</th>
						</tr>
						<tr class=\"cell0\">
							<td class=\"center\" style=\"width:15%; max-width:150px;\">
								".__("Post")."
							</td>
							<td>
								<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">{0}</textarea>
							</td>
						</tr>
						<tr class=\"cell2\">
							<td></td>
							<td>
								<input type=\"submit\" name=\"actionpost\" value=\"".__("Edit")."\" />
								<input type=\"submit\" name=\"actionpreview\" value=\"".__("Preview")."\" />
								<select size=\"1\" name=\"mood\">
									{1}
								</select>
								<label>
									<input type=\"checkbox\" name=\"nopl\" {3} />&nbsp;".__("Disable post layout", 1)."
								</label>
								<label>
									<input type=\"checkbox\" name=\"nosm\" {4} />&nbsp;".__("Disable smilies", 1)."
								</label>
								<input type=\"hidden\" name=\"id\" value=\"{2}\" />
								<input type=\"hidden\" name=\"key\" value=\"{6}\" />
							</td>
						</tr>
					</table>
				</form>
",	htmlspecialchars($prefill), $moodOptions, $pid, $nopl, $nosm, $nobr, $loguser['token']);


Write(
"
	<script type=\"text/javascript\">
		document.postform.text.focus();
	</script>
");

doThreadPreview($tid);

