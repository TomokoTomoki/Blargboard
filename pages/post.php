<?php

if (isset($_GET['id']))
{
	$pid = (int)$_GET['id'];
	$rPost = Query("select date,thread from {posts} where id={0}", $pid);
}
else if (isset($_GET['tid']) && isset($_GET['time']))
{
	$rPost = Query("select date,thread from {posts} where thread={0} AND date>{1} ORDER BY DATE LIMIT 1",
		$_GET['tid'], $_GET['time']);
}
else
	Kill('blarg');

if(NumRows($rPost))
	$post = Fetch($rPost);
else
	Kill(__("Unknown post ID."));

$tid = $post['thread'];

$rThread = Query("select id,title,forum from {threads} where id={0}", $tid);

if(NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill(__("Unknown thread ID."));
	
$tags = ParseThreadTags($thread['title']);

$ppp = $loguser['postsperpage'];
if(!$ppp) $ppp = 20;
$from = (floor(FetchResult("SELECT COUNT(*) FROM {posts} WHERE thread={1} AND date<={2} AND id!={0}", $pid, $tid, $post['date']) / $ppp)) * $ppp;
$url = actionLink("thread", $thread['id'], $from?"from=$from":"", HasPermission('forum.viewforum', $thread['forum'], true)?$tags[0]:'')."#post".$pid;

header("HTTP/1.1 301 Moved Permanently");
header("Status: 301 Moved Permanently");
header("Location: ".$url);
die;

