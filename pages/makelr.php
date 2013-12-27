<?php

if ($loguserid != 1) die('no');

$fora = Query("SELECT *, 0 AS l, 0 AS r, IF(catid>0,0,-catid) AS parent FROM {forums} ORDER BY parent,id");
while ($f = Fetch($fora))
{
	$forums[$f['id']] = $f;
	$forums[$f['id']]['done'] = false;
}

maketree(0, 1);

function maketree($level, $curval)
{
	global $forums;
	
	foreach ($forums as $id=>$f)
	{
		if ($f['done']) continue;
		
		$parent = $f['parent'];
		if ($parent == $level)
		{
			$forums[$id]['l'] = $curval++;
			
			foreach ($forums as $cf)
			{
				if ($cf['parent'] == $id)
				{
					$curval = maketree($id, $curval);
					break;
				}
			}
			
			$forums[$id]['r'] = $curval++;
			$forums[$id]['done'] = true;
		}
	}
	
	return $curval;
}


?><table border=1><?php
foreach ($forums as $id=>$f)
{
	echo '<tr><td>FORUM #'.$id.':<td>'.$f['title'].'<td>parent='.$f['parent'].'<td>l='.$f['l'].'<td>r='.$f['r'];
	Query("UPDATE {forums} SET l={0}, r={1} WHERE id={2}", $f['l'], $f['r'], $id);
	echo '<td>OK';
}

?></table>