<?php

require('lib/common.php');
if (!$loguser['root']) die('no');


$needfix = Query("SELECT id,picture,minipic FROM {users} WHERE picture='#INTERNAL#' OR minipic='#INTERNAL#'");
while ($user = Fetch($needfix))
{
	if ($user['picture'] == '#INTERNAL#')
	{
		$filename = 'avatars/'.$user['id'];
		Query("UPDATE {users} SET picture={0} WHERE id={1}", '$root/'.$filename, $user['id']);
		
		file_put_contents($dataDir.$filename.'.internal', hash_hmac_file('sha256', $dataDir.$filename, $user['id'].$salt));
	}
	
	if ($user['minipic'] == '#INTERNAL#')
	{
		$filename = 'minipics/'.$user['id'];
		Query("UPDATE {users} SET minipic={0} WHERE id={1}", '$root/'.$filename, $user['id']);
		
		file_put_contents($dataDir.$filename.'.internal', hash_hmac_file('sha256', $dataDir.$filename, $user['id'].$salt));
	}
}

?>fixed