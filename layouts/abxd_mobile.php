<!doctype html>
<html lang="en">
<head>
	<title><?php print $layout_title?></title>
	<?php include("header.php"); ?>
	<meta name="viewport" content="user-scalable=yes, initial-scale=1.0, width=device-width" />
	<script type="text/javascript" src="<?php echo resourceLink('js/mobile.js'); ?>"></script>
	<?php if ($oldAndroid) { ?>
	<style type="text/css"> 
	#mobile-sidebar { height: auto!important; max-height: none!important; } 
	#realbody { max-height: none!important; max-width: none!important; overflow: scroll!important; } 
	</style>
	<?php } ?>
</head>
<?php
	
	include('links.php');
	
?>
<body style="width:100%; font-size:80%;">
<div id="realbody">
	
	<div id="mobile-sidebar-container" style="display:none;">
	<div id="mobile-sidebar-deactivate"></div>
	<div id="mobile-sidebar">
		<table class="outline opaque">
			<tr class="header1"><th>Kuribo64</th></tr>
			<tr><td class="cell1 center"><?php echo $layout_time; ?></td></tr>
			<?php
				$c = 2;
				$stuff = Settings::get('PoRAText');
				if ($stuff) { echo '<tr><td class="cell'.$c.' center">'.$stuff.'</td></tr>'; $c = 1; }
				
				if (!empty($notifications))
				{
					echo '<tr class="header1"><th>Notifications</th></tr>';
					
					foreach ($notifications as $notif)
					{
						$msg = $notif['text'].'<br><small>'.relativedate($notif['date']).'</small>';
						$msg = str_replace('<span class="nobr">', '<span>', $msg); // hack
						
						echo '<tr><td class="cell'.$c.'"><div>'.$msg.'</div></td></tr>';
						$c = ($c==1) ? 2 : 1;
					}
				}
			?>
			<tr class="header1"><th style="height:6px;"></th></tr>
			<?php
				$cur = 'Kuribo64';
				foreach ($sidelinks as $cat=>$links)
				{
					if ($cat != $cur)
					{
						$cur = $cat;
						echo '
			<tr class="header1"><th>'.$cat.'</th></tr>';
					}
					
					foreach ($links as $url=>$text)
					{
						echo '
			<tr><td class="cell'.$c.' link"><a href="'.$url.'">'.$text.'</a></td></tr>';
						$c = ($c==1) ? 2 : 1;
					}
				}
				
				if ($loguserid)
				{
					echo '
			<tr class="header1"><th>'.userLink($loguser).'</th></tr>';
					
					while ($link = $layout_userpanel->shift())
					{
						echo '
			<tr><td class="cell'.$c.' link">'.$link->build(false).'</td></tr>';
						$c = ($c==1) ? 2 : 1;
					}
					
					echo '
			<tr><td class="cell'.$c.' link"><a href="#" onclick="$(\'#logout\').submit(); return false;">'.__('Log out').'</a></td></tr>';
				}
				else
				{
					echo '
			<tr class="header1"><th style="height:6px;"></th></tr>
			<tr><td class="cell'.$c.' link">'.actionLinkTag(__('Register'), 'register').'</td></tr>';
					$c = ($c==1) ? 2 : 1;
					echo '
			<tr><td class="cell'.$c.' link">'.actionLinkTag(__('Log in'), 'login').'</td></tr>';
				}
				
				if ($layout_actionlinks)
				{
					echo '
			<tr class="header1"><th style="height:6px;"></th></tr>';
			
					$links = $layout_actionlinks;
					// gross hack
					$links = str_replace('<li>', '<tr><td class="cell1 link">', $links);
					$links = str_replace('</li>', '</td></tr>', $links);
					echo $links;
				}
			?>
		</table>
	</div>
	</div>

	<div id="main" style="padding:0px;">
	
	<table class="outline" id="mobile-crumbs">
	<tr class="header0"><th>
		<span style="float:right;">
			<button onclick="openSidebar();"<?php echo empty($notifications) ? '>...' : ' class="notifs">'.count($notifications); ?></button>
		</span>
		<?php echo $layout_crumbs; ?>
	</th></tr>
	</table>
		
	<form action="<?php print actionLink('login'); ?>" method="post" id="logout">
		<input type="hidden" name="action" value="logout" />
	</form>

	<?php echo $layout_contents; ?>

	</div>
	<div class="footer" style='clear:both;'>
	<?php print $layout_footer;?>
	</div>
</div>
</body>
</html>
