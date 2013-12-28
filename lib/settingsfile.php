<?php

	$settings = array(
		"boardname" => array (
			"type" => "text",
			"default" => "AcmlmBoard XD",
			"name" => "Board name"
		),
		"metaDescription" => array (
			"type" => "text",
			"default" => "AcmlmBoard XD",
			"name" => "Meta description"
		),
		"metaTags" => array (
			"type" => "text",
			"default" => "AcmlmBoard XD abxd",
			"name" => "Meta tags"
		),
		"dateformat" => array (
			"type" => "text",
			"default" => "m-d-y, h:i a",
			"name" => "Date format"
		),
		"customTitleThreshold" => array (
			"type" => "integer",
			"default" => "100",
			"name" => "Custom Title Threshold"
		),
		"oldThreadThreshold" => array (
			"type" => "integer",
			"default" => "3",
			"name" => "Old Thread Threshold months"
		),
		"viewcountInterval" => array (
			"type" => "integer",
			"default" => "10000",
			"name" => "Viewcount Report Interval"
		),
		"ajax" => array (
			"type" => "boolean",
			"default" => "1",
			"name" => "Enable AJAX"
		),
		"guestLayouts" => array (
			"type" => "boolean",
			"default" => "0",
			"name" => "Show post layouts to guests"
		),
		"registrationWord" => array (
			"type" => "text",
			"default" => "",
			"name" => "Word needed for registration",
			"help" => "If set, the registration page will send the user to the FAQ page to look for the word",
		),
		"breadcrumbsMainName" => array (
			"type" => "text",
			"default" => "Main",
			"name" => "Text in breadcrumbs 'main' link",
		),
		"menuMainName" => array (
			"type" => "text",
			"default" => "Main",
			"name" => "Text in menu 'main' link",
		),
		"mailResetSender" => array (
			"type" => "text",
			"default" => "",
			"name" => "Password Reset e-mail Sender",
			"help" => "Email address used to send the pasword reset e-mails. If left blank, the password reset feature is disabled.",
		),
		"defaultTheme" => array (
			"type" => "theme",
			"default" => "gold",
			"name" => "Default Board Theme",
		),
		"showGender" => array (
			"type" => "boolean",
			"default" => "1",
			"name" => "Color usernames based on gender"
		),
		"defaultLanguage" => array (
			"type" => "language",
			"default" => "en_US",
			"name" => "Board language",
		),
		"floodProtectionInterval" => array (
			"type" => "integer",
			"default" => "10",
			"name" => "Minimum time between user posts"
		),
		"nofollow" => array (
			"type" => "boolean",
			"default" => "0",
			"name" => "Add rel=nofollow to all user-posted links"
		),
		"tagsDirection" => array (
			"type" => "options",
			"options" => array('Left' => 'Left', 'Right' => 'Right'),
			"default" => 'Right',
			"name" => "Direction of thread tags",
		),
		"alwaysMinipic" => array (
			"type" => "boolean",
			"default" => "0",
			"name" => "Show Minipics everywhere",
		),
		"showExtraSidebar" => array (
			"type" => "boolean",
			"default" => "1",
			"name" => "Show extra info in post sidebar",
		),
		"PoRAText" => array (
			"type" => "texthtml",
			"default" => "Welcome to your new ABXD Board!",
			"name" => "'What's up today?' text",
		),

		"profilePreviewText" => array (
			"type" => "textbbcode",
			"default" => "This is a sample post. You [b]probably[/b] [i]already[/i] [u]know[/u] what this is for.

[quote=Goomba][quote=Mario]Woohoo! [url=http://www.mariowiki.com/Super_Mushroom]That's what I needed![/url][/quote]Oh, nooo! *stomp*[/quote]

Well, what more could you [url=http://en.wikipedia.org]want to know[/url]? Perhaps how to do the classic infinite loop?
[source=c]while(true){
    printf(\"Hello World!
\");
}[/source]",
			"name" => "Post preview text"
		),
		
		'newsForum' => array(
			'type' => 'forum',
			'default' => '0',
			'name' => 'Latest News forum',
			'category' => 'Forum settings',
		),
		'anncForum' => array(
			'type' => 'forum',
			'default' => '0',
			'name' => 'Announcements forum',
			'category' => 'Forum settings',
		),
		"trashForum" => array (
			"type" => "forum",
			"default" => "1",
			"name" => "Trash forum",
			'category' => 'Forum settings',
		),
		"secretTrashForum" => array (
			"type" => "forum",
			"default" => "1",
			"name" => "Deleted threads forum",
			'category' => 'Forum settings',
		),
		
		'defaultGroup' => array (
			'type' => 'group',
			'default' => 0,
			'name' => 'Group for new users',
			'category' => 'Group settings',
			'rootonly' => 1,
		),
		'rootGroup' => array (
			'type' => 'group',
			'default' => 4,
			'name' => 'Group for root users',
			'category' => 'Group settings',
			'rootonly' => 1,
		),
		'bannedGroup' => array (
			'type' => 'group',
			'default' => -1,
			'name' => 'Group for banned users',
			'category' => 'Group settings',
			'rootonly' => 1,
		),
	);
?>
