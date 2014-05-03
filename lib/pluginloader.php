<?php
//Plugin loader -- By Nikolaj
global $pluginbuckets, $plugins, $plugin;

$oldplugin = $plugin;
if(!isset($self))
	$self = NULL;
$oldself = $self;

if (isset($pluginbuckets[$bucket]))
{
	$boardcwd = getcwd();
	
	foreach ($pluginbuckets[$bucket] as $plugin)
	{
		if (isset($plugins[$plugin]))
		{
			$self = $plugins[$plugin];
			chdir('plugins/'.$self['dir']);
			include($bucket.".php");
			unset($self);
			chdir($boardcwd);
		}
	}
}

$self = $oldself;
$plugin = $oldplugin;
?>
