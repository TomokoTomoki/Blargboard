	<meta http-equiv="Content-Type" content="text/html; CHARSET=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=10" />
	<meta name="description" content="<?php print $metaStuff['description']; ?>" />
	<meta name="keywords" content="<?php print $metaStuff['tags']; ?>" />
	<link rel="shortcut icon" type="image/x-icon" href="<?php print $layout_favicon;?>" />
	<link rel="stylesheet" type="text/css" href="<?php print resourceLink("css/common.css");?>" />
	<link rel="stylesheet" type="text/css" id="theme_css" href="<?php print resourceLink($layout_themefile); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php print resourceLink('css/font-awesome.min.css'); ?>" />

	<script type="text/javascript" src="<?php print resourceLink("js/jquery.js");?>"></script>
	<script type="text/javascript" src="<?php print resourceLink("js/tricks.js");?>"></script>
	<script type="text/javascript" src="<?php print resourceLink("js/jquery.tablednd_0_5.js");?>"></script>
	<script type="text/javascript" src="<?php print resourceLink("js/jquery.scrollTo-1.4.2-min.js");?>"></script>
	<script type="text/javascript">
		boardroot = <?php print json_encode($boardroot); ?>;
	</script>

	<?php
		$bucket = "pageHeader"; include("./lib/pluginloader.php");
	?>

