<?php
$userMenu = new PipeMenu();

if($loguserid)
{
	if (HasPermission('user.editprofile'))
	{
		$userMenu->add(new PipeMenuLinkEntry(__("Edit profile"), "editprofile"));
		if (HasPermission('user.editavatars'))
			$userMenu->add(new PipeMenuLinkEntry(__("Mood avatars"), "editavatars"));
	}
	
	$userMenu->add(new PipeMenuLinkEntry(__("Private messages"), "private"));
	$userMenu->add(new PipeMenuLinkEntry(__('Favorites'), 'favorites'));

	$bucket = "bottomMenu"; include("./lib/pluginloader.php");
}

$layout_userpanel = $userMenu;
?>
