<!doctype html>
<html lang="en">

<head>
	<title><?php echo $layout_title?></title>
	<?php include("header.php"); ?>
</head>
<?php
	
	include('links.php');
	
?>
<body style="width:100%; font-size: <?php echo $loguser['fontsize']; ?>%;">

	<table id="main" class="layout-table">
	<tr>
	<td id="main-header" colspan="2">
		<table id="header" class="outline">
			<tr>
				<td class="cell0 left">
					<table class="layout-table">
					<tr>
					<td>
						<a href="./">
							<img id="theme_banner" src="<?php echo htmlspecialchars($layout_logopic); ?>" alt="[banner]" title="<?php echo htmlspecialchars(Settings::get('boardName')); ?>" />
						</a>
					</td>
					<td>
						<table class="outline" id="headerInfo">
							<tr class="header1"><th>What's up today?</th></tr>
							<tr>
								<td class="cell1 center">
									<?php 
										echo $layout_time.' &mdash; '.$layout_views;
										if ($layout_birthdays) echo '<br><br>'.$layout_birthdays;
										
										$stuff = Settings::get('PoRAText');
										if ($stuff) echo '<br><br>'.$stuff;
									?>
								</td>
							</tr>
						</table>
					</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr class="header1">
				<th id="navBar">
					<div style="display:inline-block; float:right;">
						<?php if ($loguserid) { ?>
						<?php if (HasPermission('admin.viewadminpanel')) { ?>
						<span class="navButton">
							<?php echo actionLinkTag(__('Admin'), 'admin'); ?>
						</span>
						<?php } ?>
						<div id="userMenuContainer" class="dropdownContainer">
							<div id="userMenuButton" class="navButton">
								<?php echo userLink($loguser); ?>
								<i class="icon-caret-down"></i>
							</div>
							<?php $layout_userpanel->setClass('dropdownMenu'); echo $layout_userpanel->build(); ?>
						</div>
						<div id="notifMenuContainer" class="dropdownContainer">
							<div id="notifMenuButton" class="navButton <?php echo empty($notifications) ? 'noNotif' : 'hasNotifs'; ?>">
								<?php echo __('Notifications'); ?>
								<span id="notifCount"><?php echo count($notifications); ?></span>
								<?php if (!empty($notifications)) echo ' <i class="icon-caret-down"></i>'; ?>
							</div>
							<?php if (!empty($notifications)) { ?>
							<ul class="dropdownMenu">
								<?php echo makeNotifMenu($notifications); ?>
							</ul>
							<?php } ?>
						</div>
						<span class="navButton">
							<a href="#" onclick="$('#logout').submit(); return false;"><?php echo __('Log out'); ?></a>
						</span>
						<?php } else { ?>
						<span class="navButton">
							<?php echo actionLinkTag(__('Register'), 'register'); ?>
						</span>
						<span class="navButton">
							<?php echo actionLinkTag(__('Log in'), 'login'); ?>
						</span>
						<?php } ?>
					</div>
					<div id="navMenuContainer">
						<?php
							foreach ($headerlinks as $url=>$link)
								echo "<span class=\"navButton\"><a href=\"{$url}\">{$link}</a></span>";
						?>
					</div>
				</th>
			</tr>
			<tr class="cell0">
				<td class="smallFonts center">
					<?php echo $layout_onlineusers; ?>
				</td>
			</tr>
			<tr class="header1"><th id="header-sep"></th></tr>
		</table>
	
		<form action="<?php echo actionLink('login'); ?>" method="post" id="logout">
			<input type="hidden" name="action" value="logout" />
		</form>
	</td>
	</tr>
	
	<tr>
	<td id="main-sidebar">
		<table id="sidebar" class="outline">
			<tr>
				<td class="cell1">
					<table class="outline margin">
						<?php
							$c = 1;
							foreach ($sidelinks as $cat=>$links)
							{
								echo '
						<tr class="header1"><th>'.$cat.'</th></tr>';
								foreach ($links as $url=>$text)
								{
									echo '
						<tr class="cell'.$c.'"><td><a href="'.htmlspecialchars($url).'">'.$text.'</a></td></tr>';
									$c = ($c==1) ? 2:1;
								}
							}
						?>
					</table>
				</td>
			</tr>
		</table>
	</td>
	
	<td id="main-page">
		<table id="page-container" class="layout-table">
		<tr><td class="crumb-container">
		<?php echo $layout_crumbs; ?>
		</td></tr>
		<tr><td class="contents-container">
		<?php echo $layout_contents; ?>
		</td></tr>
		<tr><td class="crumb-container">
		<?php echo $layout_crumbs; ?>
		</td></tr>
		</table>
	</td>
	</tr>
	
	<tr>
	<td id="main-footer" colspan="2">

		<table id="footer" class="outline">
		<tr>
		<td class="cell2">
			<?php echo $layout_footer;?>
		</td>
		</tr>
		</table>
	
	</td>
	</tr>
	</table>
</body>
</html>
