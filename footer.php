<?php
	$mobileswitch = '';
	if ($mobileLayout) $mobileswitch .= 'Mobile view [BETA] - ';
	if ($_COOKIE['forcelayout']) $mobileswitch .= '<a href="?forcelayout=0" rel="nofollow">Auto view</a>';
	else if ($mobileLayout) $mobileswitch .= '<a href="?forcelayout=-1" rel="nofollow">Force normal view</a>';
	else $mobileswitch .= '<a href="?forcelayout=1" rel="nofollow">Force mobile view [BETA]</a>';
	
	if ($mobileLayout)
	{
		echo $mobileswitch;
		return;
	}
?>
	<table class="layout-table" style="line-height: 1.4em;">
	<tr>
	<td style="text-align: left;">
		<img src="<?php echo resourceLink('img/poweredbyblarg.png'); ?>" style="float: left; margin-right: 3px;">
		Blargboard 1.1 &middot; by StapleButter<br />
		Based off <a href="http://abxd.dirbaio.net/">ABXD</a> by Dirbaio &amp; co.<br />
		<?php echo '<!-- Page rendered in '.sprintf('%.03f',microtime(true)-$starttime).' seconds (with '.$queries.' SQL queries and '.sprintf('%.03f',memory_get_usage() / 1024).'K of RAM) -->'; ?>
	</td>
	<td style="text-align: right;">
		<?php echo $mobileswitch; ?>
	</td>
	</table>