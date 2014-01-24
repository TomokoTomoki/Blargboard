<?php

require('lib/common.php');
if ($loguserid != 1) die('no');

echo "fixing database $dbname...<br>";
$cols = Query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA={0}", $dbname);
while ($col = Fetch($cols))
{
	if ($col['DATA_TYPE'] != 'varchar' && $col['DATA_TYPE'] != 'text' && $col['DATA_TYPE'] != 'mediumtext') 
		continue;
	
	echo "fixing column {$col['TABLE_NAME']}.{$col['COLUMN_NAME']}...";
	
	$notnull = ($col['IS_NULLABLE'] == 'NO') ? 'NOT NULL' : '';
	$default = ($col['COLUMN_DEFAULT'] === NULL) ? '' : 'DEFAULT \''.addslashes($col['COLUMN_DEFAULT']).'\'';
	
	Query("ALTER TABLE {$col['TABLE_NAME']} CHANGE {$col['COLUMN_NAME']} {$col['COLUMN_NAME']} {$col['COLUMN_TYPE']} CHARACTER SET utf8 COLLATE utf8_bin $notnull $default");
	echo " ok<br>";
}

function fixcoldata($table, $field, $key)
{
	echo "fixing data in {$table}.{$field}...";
	
	$data = Query("SELECT $field,$key FROM {".$table."}");
	while ($row = Fetch($data))
	{
		// data was UTF8 stored as latin1
		// then latin1->UTF8
		// resulting in double UTF8 encoding
		// to fix, convert back to latin1
		
		$blarg = $row[$field];
		$blarg = utf8_decode($blarg);
		
		Query("UPDATE {".$table."} SET $field={0} WHERE $key={1}", $blarg, $row[$key]);
	}
	
	echo " ok<br>";
}

fixcoldata('forums', 'description', 'id');
fixcoldata('pmsgs_text', 'title', 'pid');
fixcoldata('pmsgs_text', 'text', 'pid');
fixcoldata('poll', 'question', 'id');
fixcoldata('posts_text', 'text', 'pid');
fixcoldata('reports', 'text', 'time');
fixcoldata('settings', 'value', 'name');
fixcoldata('threads', 'title', 'id');
fixcoldata('usercomments', 'text', 'id');
fixcoldata('users', 'displayname', 'id');
fixcoldata('users', 'title', 'id');
fixcoldata('users', 'postheader', 'id');
fixcoldata('users', 'signature', 'id');
fixcoldata('users', 'bio', 'id');

?>
done. delete me now. blarg.