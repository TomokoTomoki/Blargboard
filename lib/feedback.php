<?php
//  AcmlmBoard XD support - System feedback

//	Not really much different to kill()
function Alert($s, $t="")
{
	if($t=="")
		$t = __("Notice");

	RenderTemplate('messagebox', 
		array(	'msgtitle' => $t,
				'message' => $s));
}

function Kill($s, $t="")
{
	if($t=="")
		$t = __("Error");
	Alert($s, $t);
	throw new KillException();
}

function dieAjax($what)
{
	global $ajaxPage;

	echo $what;
	$ajaxPage = true;
	throw new KillException();
}

?>
