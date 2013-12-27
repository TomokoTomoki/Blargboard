<?php

$title = 'Report post';

if (!$loguserid) Kill(__('You must be logged in to report posts.'));
CheckPermission('user.reportposts');

$pid = (int)$_GET['id'];
$post = Fetch(Query("SELECT p.*, pt.text FROM {posts} p LEFT JOIN {posts_text} pt ON pt.pid=p.id AND pt.revision=p.currentrevision WHERE p.id={0}", $pid));
if (!$post) Kill(__('Invalid post ID.'));

if ($post['user'] == $loguserid) Kill(__('You may not report your own posts.'));
if ($post['deleted']) Kill(__('This post is deleted.'));

$thread = Fetch(Query("SELECT * FROM {threads} WHERE id={0}", $post['thread']));
if (!$thread) Kill(__('Unknown thread.'));
$fid = $thread['forum'];

if (!HasPermission('forum.viewforum', $fid))
	Kill(__('You may not access this forum.'));

$tags = ParseThreadTags($thread['title']);
$isHidden = !HasPermission('forum.viewforum', $fid, true);

if ($_POST['report'])
{
	if ($_POST['key'] !== $loguser['token'])
		Kill(__('No.'));
	
	Query("INSERT INTO {pmsgs} (userto,userfrom,date,ip,msgread,deleted,drafting)
		VALUES ({0},{1},{2},{3},0,0,0)",
		-1, $loguserid, time(), $_SERVER['REMOTE_ADDR']);
	$pmid = InsertId();
	
	$report = "<strong>Post report</strong>\n\n<strong>Post:</strong> ".actionLinkTag($tags[0], 'post', $pid).
		" (post #{$pid})\n\n<strong>Message:</strong>\n{$_POST['message']}\n\n".actionLinkTag('Mark issue as resolved', 'showprivate', $pmid, 'markread=1');
	
	Query("INSERT INTO {pmsgs_text} (pid,title,text) VALUES ({0},{1},{2})",
		$pmid, "Post report (post #{$pid})", $report);
	
	die(header('Location: '.actionLink('post', $pid)));
}

MakeCrumbs(forumCrumbs($forum) + array(actionLink("thread", $tid, '', $isHidden?'':$tags[0]) => $tags[0], '' => __("Report post")), $links);

$user = Fetch(Query("SELECT * FROM {users} WHERE id={0}", $post['user']));
foreach($user as $key => $value)
	$post['u_'.$key] = $value;

MakePost($post, POST_SAMPLE, array('forcepostnum'=>1, 'metatext'=>__("Target post")));

?>
	<form action="" method="POST">
		<table class="outline margin">
			<tr class="header1"><th colspan="2"><?php echo __('Report post'); ?></th></tr>
			<tr class="cell0">
				<td class="cell2 center" style="width:15%; max-width:150px;"><?php echo __('Message'); ?></td>
				<td><textarea id="text" name="message" rows="10" style="width: 98%;"></textarea></td>
			</tr>
			<tr class="cell2">
				<td>&nbsp;</td>
				<td><input type="submit" name="report" value="<?php echo __('Submit report'); ?>">
		</table>
		<input type="hidden" name="key" value="<?php echo $loguser['token']; ?>">
	</form>