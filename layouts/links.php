<?php

$headerlinks = array
(
	$boardroot => Settings::get('breadcrumbsMainName'), 
	actionLink('news') => 'News',
	actionLink('board') => 'Forums',
);

$sidelinks = array
(
	Settings::get('breadcrumbsMainName') => array
	(
		$boardroot => 'Home page',
		actionLink('news') => 'Latest news',
		actionLink('board') => 'Forums',
		actionLink('faq') => 'FAQ',
		actionLink('memberlist') => 'Member list',
		actionLink('ranks') => 'Ranks',
		actionLink('online') => 'Online users',
		actionLink('lastposts') => 'Last posts',
		actionLink('search') => 'Search',
	),
);

$bucket = "links"; include("./lib/pluginloader.php");

?>