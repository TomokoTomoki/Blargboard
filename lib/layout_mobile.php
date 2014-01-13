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

?>