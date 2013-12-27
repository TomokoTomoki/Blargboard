<?php

require 'lib/permstrings.php';

$usergroups = array();
$grouplist = array();
$res = Query("SELECT * FROM {usergroups} ORDER BY id");
while ($g = Fetch($res))
{
	$usergroups[$g['id']] = $g;
	$grouplist[$g['id']] = $g['title'];
}

function LoadPermset($res)
{
	$perms = array();
	$permord = array();
	
	while ($perm = Fetch($res))
	{
		if ($perm['value'] == 0) continue;
		
		$k = $perm['perm'];
		if ($perm['arg']) $k .= '_'.$perm['arg'];
		
		if ($perm['ord'] > $permord[$k] || $perms[$k] != -1)
			$perms[$k] = $perm['value'];
		
		$permord[$k] = $perm['ord'];
	}
	
	return $perms;
}

function LoadGroups()
{
	global $usergroups, $loguserid, $loguser, $loguserGroup, $loguserPermset;
	global $guestPerms, $guestGroup, $guestPermset;
	
	$guestGroup = $usergroups[Settings::get('defaultGroup')];
	$res = Query("SELECT *, 1 ord FROM {permissions} WHERE applyto=0 AND id={0} AND perm IN ({1c})", $guestGroup['id'], $guestPerms);
	$guestPermset = LoadPermset($res);
	
	if (!$loguserid)
	{
		$loguserGroup = $guestGroup;
		$loguserPermset = $guestPermset;
		
		$loguser['banned'] = false;
		$loguser['root'] = false;
		return;
	}
	
	$secgroups = array();
	$loguserGroup = $usergroups[$loguser['primarygroup']];
	
	$res = Query("SELECT groupid FROM {secondarygroups} WHERE userid={0}", $loguserid);
	while ($sg = Fetch($res)) $secgroups[] = $sg['groupid'];
	
	$res = Query("	SELECT *, 1 ord FROM {permissions} WHERE applyto=0 AND id={0}
					UNION SELECT *, 2 ord FROM {permissions} WHERE applyto=0 AND id IN ({1c})
					UNION SELECT *, 3 ord FROM {permissions} WHERE applyto=1 AND id={2}
					ORDER BY ord", 
		$loguserGroup['id'], $secgroups, $loguserid);
	$loguserPermset = LoadPermset($res);
	
	$maxrank = FetchResult("SELECT MAX(rank) FROM {usergroups}");
	
	$loguser['banned'] = ($loguserGroup['id'] == Settings::get('bannedGroup'));
	$loguser['root'] = ($loguserGroup['id'] == Settings::get('rootGroup'));
}

function HasPermission($perm, $arg=0, $guest=false)
{
	global $guestPermset, $loguserPermset;
	
	$permset = $guest ? $guestPermset : $loguserPermset;

	// check general permission first
	if ($permset[$perm] == -1)
		return false;
		
	$needspecific = !$permset[$perm];
	if ($needspecific && $arg == 0)
		return false;
	
	// then arg-specific permission
	// if it's set to revoke it revokes the general permission
	if ($arg)
	{
		$perm .= '_'.$arg;
		if ($needspecific)
		{
			if ($permset[$perm] != 1)
				return false;
		}
		else
		{
			if ($permset[$perm] == -1)
				return false;
		}
	}
	
	return true;
}

function CheckPermission($perm, $arg=0, $guest=false)
{
	global $loguserid, $loguser;
	
	if (!HasPermission($perm, $arg, $guest))
	{
		if (!$loguserid)
			Kill(__('You must be logged in to perform this action.'));
		else if ($loguser['banned'])
			Kill(__('You may not perform this action because you are banned.'));
		else
			Kill(__('You may not perform this action.'));
	}
}

function ForumsWithPermission($perm, $guest=false)
{
	global $guestPermset, $loguserPermset;
	static $fpermcache = array();
	
	if ($guest)
	{
		$permset = $guestPermset;
		$cperm = 'guest_'.$perm;
	}
	else
	{
		$permset = $loguserPermset;
		$cperm = $perm;
	}
	
	if (isset($fpermcache[$cperm]))
		return $fpermcache[$cperm];
	
	$ret = array();
	
	// if the general permission is set to deny, no need to check for specific permissions
	if ($permset[$perm] == -1)
	{
		$fpermcache[$cperm] = $ret;
		return $ret;
	}
	
	$forumlist = Query("SELECT id FROM {forums}");
	
	// if the general permission is set to grant, we need to check for forums for which it'd be revoked
	// otherwise we need to check for forums for which it'd be granted
	if ($permset[$perm] == 1)
	{
		while ($forum = Fetch($forumlist))
		{
			if ($permset[$perm.'_'.$forum['id']] != -1)
				$ret[] = $forum['id'];
		}
	}
	else
	{
		while ($forum = Fetch($forumlist))
		{
			if ($permset[$perm.'_'.$forum['id']] == 1)
				$ret[] = $forum['id'];
		}
	}

	$fpermcache[$cperm] = $ret;
	return $ret;
}


LoadGroups();
$loguser['powerlevel'] = -1; // safety

?>