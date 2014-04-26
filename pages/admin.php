<?php
//  AcmlmBoard XD - Administration hub page
//  Access: administrators


CheckPermission('admin.viewadminpanel');

$title = __("Administration");

MakeCrumbs(array(actionLink("admin") => __('Admin')));

$cell2 = 1;
function cell2($content)
{
	global $cell2;
	$cell2 = ($cell2 == 1 ? 0 : 1);
	Write("
		<tr class=\"cell{0}\">
			<td>
				{1}
			</td>
		</tr>
	", $cell2, $content);
}

Write("
	<table class=\"outline margin width50\" style=\"float: right;\">
		<tr class=\"header1\">
			<th colspan=\"2\">
				".__("Information")."
			</th>
		</tr>
");
cell2(Format("

				".__("Last viewcount milestone")."
			</td>
			<td style=\"width: 60%;\">
				{0}
			",	$misc['milestone']));

$bucket = "adminright"; include("./lib/pluginloader.php");

write(
"
	</table>
");

$cell2 = 1;
Write("
	<table class=\"outline margin width25\">
		<tr class=\"header1\">
			<th>
				".__("Admin tools")."
			</th>
		</tr>
");
if ($loguser['root']) 						cell2(actionLinkTag(__("Recalculate statistics"), "recalc"));
if (HasPermission('admin.manageipbans'))	cell2(actionLinkTag(__("Manage IP bans"), "ipbans"));
if (HasPermission('admin.editforums'))		cell2(actionLinkTag(__("Manage forum list"), "editfora"));
if (HasPermission('admin.editsettings'))
{
	cell2(actionLinkTag(__("Manage plugins"), "pluginmanager"));
	cell2(actionLinkTag(__("Edit settings"), "editsettings"));
}
if (HasPermission('admin.editsmilies'))		cell2(actionLinkTag(__("Edit smilies"), "editsmilies"));
if ($loguser['root'])						cell2(actionLinkTag(__("Optimize tables"), "optimize"));
if (HasPermission('admin.viewlog'))			cell2(actionLinkTag(__("View log"), "log"));
if (HasPermission('admin.ipsearch'))		cell2(actionLinkTag(__('Rereg radar'), 'reregs'));

$bucket = "adminleft"; include("./lib/pluginloader.php");

write(
"
	</table>
");
?>
