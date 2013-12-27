<?php

require 'lib/hpsecurity.php';

$title = 'Edit homepage';

CheckPermission('admin.editsettings');
	
$err = '';

$token = hash('sha256', "{$loguserid},{$loguser['pss']},{$salt},esgf8798df7h98dfg7h4gfs57h4gfh");
if (isset($_POST['saveaction']))
{
	if ($_POST['token'] !== $token)
		Kill('No.');
	
	$res = @file_put_contents('homepage.txt', $_POST['text']);
	if ($res !== FALSE)
		die(header('Location: '.actionLink('edithomepage')));
	else
		$err = 'Save failed.';
}

MakeCrumbs(array(actionLink('admin') => 'Admin', actionLink('edithomepage') => 'Edit home page'), '');

if ($err)
	Alert($err, 'Error');

if (isset($_POST['previewaction']))
{
	$text = $_POST['text'];
	echo '
	<table class="outline margin">
		<tr class="header1"><th>Home page preview</th></tr>
	</table>
	'.securityFilter($_POST['text']);
}
else
	$text = @file_get_contents('homepage.txt');

?>
	<script type="text/javascript">
		function tabfixor(t,e)
		{
			var code = e.keyCode||e.which;
			if (code == 9)
			{
				e.preventDefault();
				t.focus();
				if (document.selection)
					document.selection.createRange().text += '\t';
				else
				{
					var oldpos = t.selectionEnd;
					t.value = t.value.substring(0, t.selectionEnd) + '\t' + t.value.substring(t.selectionEnd, t.value.length);
					t.selectionStart = t.selectionEnd = oldpos + 1;
				}
			}
		}
	</script>
	<form action="" method="POST">
		<table class="outline margin">
			<tr class="header1"><th>Home page editor</th></tr>
			<tr class="cell1"><td><input type="submit" name="saveaction" value="Save" /> <input type="submit" name="previewaction" value="Preview" /></td></tr>
			<tr class="header1"><th style="height:6px;"></td></tr>
			<tr class="cell0">
				<td>
					Format is HTML, with newlines converted to &lt;br&gt; tags automatically. Javascript and iframes will be filtered out, except for Youtube embeds.<br>
					<textarea name="text" style="width:99.5%; height:40em;" onkeydown="tabfixor(this,event);"><?php echo htmlspecialchars($text); ?></textarea>
				</td>
			</tr>
			<tr class="header1"><th style="height:6px;"></td></tr>
			<tr class="cell1"><td><input type="submit" name="saveaction" value="Save" /> <input type="submit" name="previewaction" value="Preview" /></td></tr>
		</table>
		<input type="hidden" name="token" value="<?php echo $token; ?>" />
	</form>