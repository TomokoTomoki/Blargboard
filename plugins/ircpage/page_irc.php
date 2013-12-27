<?php

$title = "IRC";
makeCrumbs(array(actionLink("irc") => __("IRC")), $links);

$bad = array("~", "&", "@", "?", "!", ".", ",", "=", "+", "%", "*");
$handle = str_replace(" ", "", $loguser['name']);
$handle = str_replace($badchars, "_", $handle);
if(!$handle)
{
	$handle = "KuriboGuest";
	$guest = "When you've connected to the IRC network, please use the command <kbd>/nick NICKNAME</kbd>.";
}

$server = 'irc.djbouche.net';
$port = 6667;

if(isset($_GET['chan']))
{
	$chan = htmlspecialchars($_GET['chan']);
	echo "
	<table class=\"outline margin width100\">
		<tr class=\"header1\"><th>IRC</th></tr>
		<tr class=\"cell1\"><td class=\"center\">
			<applet code=\"IRCApplet.class\" codebase=\"".resourceLink("plugins/ircpage/pjirc/")."\"
			archive=\"irc.jar,pixx.jar\" width=\"100%\" height=\"500\">
			<param name=\"CABINETS\" value=\"irc.cab,securedirc.cab,pixx.cab\">

			<param name=\"nick\" value=\"{$handle}\">
			<param name=\"alternatenick\" value=\"{$handle}_??\">
			<param name=\"fullname\" value=\"Kuribo64 IRC User\">
			<param name=\"host\" value=\"{$server}\">
			<param name=\"port\" value=\"{$port}\">
			<param name=\"gui\" value=\"pixx\">
			<param name=\"authorizedcommandlist\" value=\"all-server-s\">

			<param name=\"quitmessage\" value=\"Leaving\">
			<param name=\"autorejoin\" value=\"true\">

			<param name=\"style:bitmapsmileys\" value=\"false\">
			<param name=\"style:backgroundimage\" value=\"false\">
			<param name=\"style:backgroundimage1\" value=\"none+Channel all 2 background.png.gif\">
			<param name=\"style:sourcecolorrule1\" value=\"all all 0=000000 1=ffffff 2=0000ff 3=00b000 4=ff4040 5=c00000 6=c000a0 7=ff8000 8=ffff00 9=70ff70 10=00a0a0 11=80ffff 12=a0a0ff 13=ff60d0 14=a0a0a0 15=d0d0d0\">

			<param name=\"pixx:timestamp\" value=\"true\">
			<param name=\"pixx:highlight\" value=\"true\">
			<param name=\"pixx:highlightnick\" value=\"true\">
			<param name=\"pixx:nickfield\" value=\"false\">
			<param name=\"pixx:styleselector\" value=\"true\">
			<param name=\"pixx:setfontonstyle\" value=\"true\">

			<param name=\"command1\" value=\"/join #{$chan}\">

			</applet>
		</td></tr>
		<tr class=\"cell0\"><td class=\"center\">Powered by PJIRC. If you get security warnings, ignore them and run the applet.</td></tr>
	</table>
";
}
else
{
?>
	<table class="outline margin width100">
		<tr class="header1"><th>IRC</th></tr>
		<tr class="cell1 center">
			<td>
				<br>
				<b>Server:</b><br>
				<?php echo $server.':'.$port; ?><br>
				<br>
				<b>Channels:</b><br>
				&bull; <?php echo actionLinkTag('#kuribo64', 'irc', '', 'chan=kuribo64'); ?>: Kuribo64's IRC channel<br>
				&bull; <?php echo actionLinkTag('#SMG2.5', 'irc', '', 'chan=SMG2.5'); ?>: the SMG2.5 project's IRC channel<br>
				<br>
				Have fun! <img src="<?php echo resourceLink('img/smilies/wink.png'); ?>"><br>
				<br>
			</td>
		</tr>
	</table>
<?php
}
?>