<?php

//Category/forum editor -- By Nikolaj
//Secured and improved by Dirbaio
// Adapted to Blargboard by StapleButter.

$title = __("Edit forums");

CheckPermission('admin.editforums');

MakeCrumbs(array(actionLink("admin") => __("Admin"), actionLink("editfora") => __("Edit forum list")));

/**
	Okay. Much like the category editor, now the action is specified by $_POST["action"].

	Possible actions are:
	- updateforum: Updates the settings of a forum in the DB.
	- addforum: Adds a new forum to the DB.
	- deleteforum: Deletes a forum from the DB. Also, depending on $_GET["threads"]: (NOT YET)
		- "delete": DELETES all threads and posts in the DB.
		- "trash": TRASHES all the threads (move to trash and close)
		- "move": MOVES the threads to forum ID $_POST["threadsmove"]
		- "leave": LEAVES all the threads untouched in the DB (like the old forum editor. Not recommended. Will cause "invisible posts" that will still count towards user's postcounts)

	- forumtable: Returns the forum table for the left panel.
	- editforum: Returns the HTML code for the forum settings in right panel.
		- editforumnew: Returns the forum edit box to create a new forum. This way the huge HTML won't be duplicated in the code.
		- editforum: Returns the forum edit box to edit a forum.
		
		
	PERMISSION EDITING PRESETS
	
	* Full: full access
	* Standard: view, post threads, reply to threads
	* Reply-only: view, reply to threads (ie announcement forum)
	* Read-only: view
	* No access: (none)
	* Custom

**/

$noFooter = true;

function recursionCheck($fid, $cid)
{
	if ($cid >= 0) return;
	
	$check = array();
	for (;;)
	{
		$check[] = -$cid;
		if ($check[0] == $fid)
			dieAjax('Endless recursion detected; choose another parent for this forum.');
		
		$cid = FetchResult("SELECT catid FROM {forums} WHERE id={0}", $cid);
		if ($cid >= 0) break;
	}
}

if (isset($_REQUEST['action']) && isset($_POST['key']))
{
	//Check for the key
	if ($loguser['token'] != $_POST['key'])
		Kill(__("No."));
			
	switch($_REQUEST['action'])
	{
		case 'updateforum':

			//Get new forum data
			$id = (int)$_POST['id'];
			$title = $_POST['title'];
			if($title == "") dieAjax(__("Title can't be empty."));
			$description = $_POST['description'];
			$category = ($_POST['ptype'] == 0) ? (int)$_POST['category'] : -(int)$_POST['pforum'];
			$forder = (int)$_POST['forder'];
			
			// TODO PERMS

			//Send it to the DB
			Query("UPDATE {forums} SET title = {0}, description = {1}, catid = {2}, forder = {3}, minpower = {4}, minpowerthread = {5}, minpowerreply = {6}, accesscontrol={8} WHERE id = {7}", $title, $description, $category, $forder, $minpower, $minpowerthread, $minpowerreply, $id, $accessctrl);
			
			dieAjax('Ok');
			break;
			
		case 'updatecategory':

			//Get new cat data
			$id = (int)$_POST['id'];
			$name = $_POST['name'];
			if($name == "") dieAjax(__("Name can't be empty."));
			$corder = (int)$_POST['corder'];
			
			$board = $_POST['board'];
			if (!isset($forumBoards[$board])) $board = '';

			//Send it to the DB
			Query("UPDATE {categories} SET name = {0}, corder = {1}, board={3} WHERE id = {2}", $name, $corder, $id, $board);
			
			dieAjax('Ok');
			break;

		case 'addforum':

			//Get new forum data
			$title = $_POST['title'];
			if($title == "") dieAjax(__("Title can't be empty."));
			$description = $_POST['description'];
			$category = ($_POST['ptype'] == 0) ? (int)$_POST['category'] : -(int)$_POST['pforum'];
			$forder = (int)$_POST['forder'];
			
			// TODO PERMS

			//Figure out the new forum ID.
			//I think it'd be better to use InsertId, but...
			$newID = FetchResult("SELECT id+1 FROM {forums} WHERE (SELECT COUNT(*) FROM {forums} f2 WHERE f2.id={forums}.id+1)=0 ORDER BY id ASC LIMIT 1");
			if($newID < 1) $newID = 1;

			//Add the actual forum
			Query("INSERT INTO {forums} (`id`, `title`, `description`, `catid`, `forder`, `minpower`, `minpowerthread`, `minpowerreply`, `accesscontrol`) VALUES ({0}, {1}, {2}, {3}, {4}, {5}, {6}, {7}, {8})", $newID, $title, $description, $category, $forder, $minpower, $minpowerthread, $minpowerreply, $accessctrl);

			dieAjax('Ok');
			break;

		case 'addcategory':

			//Get new cat data
			$name = $_POST['name'];
			if($name == "") dieAjax(__("Name can't be empty."));
			$corder = (int)$_POST['corder'];
			
			$board = (int)$_POST['board'];
			if (!isset($forumBoards[$board])) $board = '';

			Query("INSERT INTO {categories} (`name`, `corder`, `board`) VALUES ({0}, {1}, {2})", $name, $corder, $board);

			dieAjax('Ok');
			break;
			
		case 'deleteforum':
			//TODO: Move and delete threads mode.

			//Get Forum ID
			$id = (int)$_POST['id'];

			//Check that forum exists
			$rForum = Query("SELECT * FROM {forums} WHERE id={0}", $id);
			if (!NumRows($rForum))
				dieAjax("No such forum.");

			//Check that forum has threads.
			$forum = Fetch($rForum);
			if($forum['numthreads'] > 0)
				dieAjax(__("Forum has threads. Move those first."));

			//Delete
			Query("DELETE FROM `{forums}` WHERE `id` = {0}", $id);
			dieAjax('Ok');
			break;
			
		case 'deletecategory':
			//Get Cat ID
			$id = (int)$_POST['id'];

			//Check that forum exists
			$rCat = Query("SELECT * FROM {categories} WHERE id={0}", $id);
			if (!NumRows($rCat))
				dieAjax(__("No such category."));
				
			if (FetchResult("SELECT COUNT(*) FROM {forums} WHERE catid={0}", $cid) > 0)
				dieAjax(__('Cannot delete a category that contains forums.'));

			//Delete
			Query("DELETE FROM `{categories}` WHERE `id` = {0}", $id);
			dieAjax('Ok');
			break;
	}
}

if (isset($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'forumtable':
			WriteForumTableContents();
			dieAjax('');
			break;

		case 'editforumnew':
		case 'editforum':

			//Get forum ID
			if($_REQUEST['action'] == 'editforumnew')
				$fid = -1;
			else
				$fid = (int)$_GET['fid'];

			WriteForumEditContents($fid);
			dieAjax('');
			break;

		case 'editcategorynew':
		case 'editcategory':

			//Get cat ID
			if($_REQUEST['action'] == 'editcategorynew')
				$cid = -1;
			else
				$cid = (int)$_GET['cid'];

			WriteCategoryEditContents($cid);
			dieAjax('');
			break;
	}
}



//Main code.

echo '
<script src="'.resourceLink('js/editfora.js').'" type="text/javascript"></script>
<div id="editcontent" style="float: right; width: 49.7%;">
	&nbsp;
</div>
<div id="flist">';

WriteForumTableContents();

echo '
</div>';




//Helper functions

// $fid == -1 means that a new forum should be made :)
function WriteForumEditContents($fid)
{
	global $loguser;

	//Get all categories.
	$rCats = Query("SELECT * FROM {categories} ORDER BY board, corder, id");

	$cats = array();
	while ($cat = Fetch($rCats))
		$cats[$cat['id']] = $cat;
		
	$rFora = Query("SELECT * FROM {forums} ORDER BY forder, id");

	$fora = array();
	while ($forum = Fetch($rFora))
		$fora[$forum['id']] = $forum;

	if(count($cats) == 0)
		$cats[0] = __("No categories");

	if($fid != -1)
	{
		$rForum = Query("SELECT * FROM {forums} WHERE id={0}", $fid);
		if (!NumRows($rForum))
		{
			Kill(__("Forum not found."));
		}
		$forum = Fetch($rForum);

		$title = htmlspecialchars($forum['title']);
		$description = htmlspecialchars($forum['description']);
		$catselect = MakeCatSelect('cat', $cats, $fora, $forum['catid'], $forum['id']);
		$accessctrl = $forum['accesscontrol'];
		$minpower = PowerSelect('minpower', $forum['minpower']);
		$minpowerthread = PowerSelect("minpowerthread", $forum['minpowerthread']);
		$minpowerreply = PowerSelect('minpowerreply', $forum['minpowerreply']);
		$forder = $forum['forder'];
		$func = "changeForumInfo";
		$button = __("Save");
		$boxtitle = __("Edit Forum");
		$delbutton = "
			<button onclick='showDeleteForum(); return false;'>
				".__("Delete")."
			</button>";

		$localmods = "fuck you";
		
		
		$privusers = "fuck you too";
	}
	else
	{
		$title = __("New Forum");
		$description = __("Description goes here. <strong>HTML allowed.</strong>");
		$catselect = MakeCatSelect('cat', $cats, $fora, 1, -1);
		$accessctrl = 1;
		$minpower = PowerSelect('minpower', 0);
		$minpowerthread = PowerSelect("minpowerthread", 0);
		$minpowerreply = PowerSelect('minpowerreply', 0);
		$forder = 0;
		$func = "addForum";
		$button = __("Add");
		$boxtitle = __("New Forum");
		$delbutton = "";
		$localmods = "(Create the forum before managing mods)";
		$privusers = '<small>(create the forum before adding users here)</small>';
	}

	echo "
	<form method=\"post\" id=\"forumform\" action=\"".actionLink("editfora")."\">
	<input type=\"hidden\" name=\"key\" value=\"".$loguser['token']."\">
	<input type=\"hidden\" name=\"id\" value=\"$fid\">
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th colspan=\"2\">
				$boxtitle
			</th>
		</tr>
		<tr class=\"cell1\">
			<td style=\"width: 25%;\">
				".__("Title")."
			</td>
			<td>
				<input type=\"text\" style=\"width: 98%;\" name=\"title\" value=\"$title\" />
			</td>
		</tr>
		<tr class=\"cell0\">

			<td>
				".__("Description")."
			</td>
			<td>
				<input type=\"text\" style=\"width: 98%;\" name=\"description\" value=\"$description\" />
			</td>
		</tr>
		<tr class=\"cell1\">
			<td>
				".__("Parent")."
			</td>
			<td>
				$catselect
			</td>
		</tr>
		<tr class=\"cell0\">
			<td>
				".__("Listing order")."
			</td>
			<td>
				<input type=\"text\" size=\"2\" name=\"forder\" value=\"$forder\" />
				<img src=\"".resourceLink("img/icons/icon5.png")."\" title=\"".__("Everything is sorted by listing order first, then by ID. If everything has its listing order set to 0, they will therefore be sorted by ID only.")."\" alt=\"[?]\" />
			</td>
		</tr>
		<tr class=\"cell1\">
			<td>
				".__("Access control")."
			</td>
			<td>
				<label><input type=\"radio\" name=\"acc\" value=\"1\"".($accessctrl==1 ? ' checked="checked"':'')."> ".__("Powerlevel: ")."</label>
				$minpower<br>
				<label><input type=\"radio\" name=\"acc\" value=\"2\"".($accessctrl==2 ? ' checked="checked"':'')."> ".__("Private: ")."</label>
				<br>
				$privusers
				<br />
				<br />
				".__("To post threads: ")."$minpowerthread
				<br />
				".__("To reply: ")."$minpowerreply
			</td>
		</tr>
		<tr class=\"cell0\">
			<td>
				".__("Local moderators")."
			</td>
			<td>
				$localmods
			</td>
		</tr>

		<tr class=\"cell2\">
			<td>
				&nbsp;
			</td>
			<td>
				<button onclick=\"$func(); return false;\">
					$button
				</button>
				$delbutton
			</td>
		</tr>
	</table></form>

	<form method=\"post\" id=\"deleteform\" action=\"".actionLink("editfora")."\">
	<input type=\"hidden\" name=\"key\" value=\"".$loguser['token']."\">
	<input type=\"hidden\" name=\"id\" value=\"$fid\">
	<div id=\"deleteforum\" style=\"display:none\">
		<table class=\"outline margin\">
			<tr class=\"header1\">

				<th>
					".__("Delete forum")."
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					".__("Instead of deleting a forum, you might want to consider archiving it: Change its name or description to say so, and raise the minimum powerlevel to reply and create threads so it's effectively closed.")."<br /><br />
					".__("If you still want to delete it, click below:")."<br />
					<button onclick=\"deleteForum('delete'); return false;\">
						".__("Delete forum")."
					</button>
				</td>
			</tr>
		</table>
	</div>
	</form>";

//	, $title, $description, $catselect, $minpower, $minpowerthread, $minpowerreply, $fid, $forder, $loguser['token'], $func, $button, $boxtitle, $delbutton);
}
// $fid == -1 means that a new forum should be made :)
function WriteCategoryEditContents($cid)
{
	global $loguser, $forumBoards;
	
	$boardlist = '';

	if($cid != -1)
	{
		$rCategory = Query("SELECT * FROM {categories} WHERE id={0}", $cid);
		if (!NumRows($rCategory))
		{
			Kill("Category not found.");
		}
		$cat = Fetch($rCategory);
		
		$candelete = FetchResult("SELECT COUNT(*) FROM {forums} WHERE catid={0}", $cid) == 0;

		$name = htmlspecialchars($cat['name']);
		$corder = $cat['corder'];
		
		if (count($forumBoards) > 1)
		{
			foreach ($forumBoards as $bid=>$bname)
			{
				$boardlist .= '<label><input type="radio" name="board" value="'.htmlspecialchars($bid).'"'.($cat['board']==$bid ? ' checked="checked"':'').'> '.htmlspecialchars($bname).'</label>';
			}
		}

		$boxtitle = __("Editing category ").$name;
			
		$fields = array
		(
			'name' => '<input type="text" name="name" value="'.$name.'" size=64>',
			'order' => '<input type="text" name="corder" value="'.$corder.'" size=2>',
			'board' => $boardlist,
			
			'btnSave' => '<button onclick="changeCategoryInfo(); return false;">Save</button>',
			'btnDelete' => '<button '.($candelete ? 'onclick="deleteCategory(); return false;"' : 'disabled="disabled"').'>Delete</button>',
		);
		$delMessage = $candelete ? '' : 'Before deleting a category, remove all forums from it.';
	}
	else
	{		
		if (count($forumBoards) > 1)
		{
			foreach ($forumBoards as $bid=>$bname)
			{
				$boardlist .= '<label><input type="radio" name="board" value="'.htmlspecialchars($bid).'"'.($bid=='' ? ' checked="checked"':'').'> '.htmlspecialchars($bname).'</label>';
			}
		}
		
		$boxtitle = __("New category");
		
		$fields = array
		(
			'name' => '<input type="text" name="name" value="" size=64>',
			'order' => '<input type="text" name="corder" value="0" size=2>',
			'board' => $boardlist,
			
			'btnSave' => '<button onclick="addCategory(); return false;">Save</button>',
			'btnDelete' => '',
		);
		$delMessage = '';
	}

	echo "
	<form method=\"post\" id=\"forumform\" action=\"".actionLink("editfora")."\">
	<input type=\"hidden\" name=\"key\" value=\"".$loguser["token"]."\">
	<input type=\"hidden\" name=\"id\" value=\"$cid\">";
	
	RenderTemplate('form_editcategory', array('formtitle' => $boxtitle, 'fields' => $fields, 'delMessage' => $delMessage));

	echo "
	</form>";
}


function WriteForumTableContents()
{
	global $forumBoards;
	
	$boards = array();
	$cats = array();
	$forums = array();
	
	foreach ($forumBoards as $bid=>$bname)
		$boards[$bid] = array('id' => $bid, 'name' => $bname, 'cats' => array());
	
	$rCats = Query("SELECT * FROM {categories} ORDER BY board, corder, id");
	while ($cat = Fetch($rCats))
	{
		$cats[$cat['board']][$cat['id']] = $cat;
	}
	
	$rForums = Query("SELECT * FROM {forums} ORDER BY l");
	$cid = -1; $lastr = 0; $level = 1;
	while ($forum = Fetch($rForums))
	{
		if ($forum['catid'] >= 0) $cid = $forum['catid'];
		
		if ($lastr)
		{
			if ($forum['r'] < $lastr) // we went up one level
				$level++;
			else // we went down a few levels maybe
				$level -= $forum['l'] - $lastr - 1;
		}
		$forum['level'] = $level;
		$lastr = $forum['r'];
		
		$forums[$cid][$forum['id']] = $forum;
	}
	
	$btnNewForum = empty($cats) ? '' : '<button onclick="newForum();">'.__("Add forum").'</button>';
	$btnNewCategory = '<button onclick="newCategory();">'.__("Add category").'</button>';
	
	RenderTemplate('editfora_list', array(
		'boards' => $boards,
		'cats' => $cats,
		'forums' => $forums,
		'selectedForum' => (int)$_GET['s'],
		
		'btnNewForum' => $btnNewForum,
		'btnNewCategory' => $btnNewCategory,
	));
}

function mcs_forumBlock($fora, $catid, $selID, $indent, $fid)
{
	$ret = '';
	
	foreach ($fora as $forum)
	{
		if ($forum['catid'] != $catid)
			continue;
		if ($forum['id'] == $fid)
			continue;
		//if ($forum['id'] == 1337)	// HAX
		//	continue;
		
		$ret .=
'				<option value="'.$forum['id'].'"'.($forum['id'] == -$selID ? ' selected="selected"':'').'>'
	.str_repeat('&nbsp; &nbsp; ', $indent).htmlspecialchars($forum['title'])
	.'</option>
';
		$ret .= mcs_forumBlock($fora, -$forum['id'], $selID, $indent+1, $fid);
	}
	
	return $ret;
}

function MakeCatSelect($i, $o, $fora, $v, $fid)
{
	$r = '
			<label><input type="radio" name="ptype" value="0"'.($v>=0 ? ' checked="checked"':'').'>Category:</label>
			<select name="category">';
	foreach ($o as $opt)
	{
		$r .= '
				<option value="'.$opt['id'].'"'.($v == $opt['id'] ? ' selected="selected"' : '').'>
					'.htmlspecialchars($opt['name']).'
				</option>';
	}
	$r .= '
			</select>';
			
	$r .= '
			<br>
			<label><input type="radio" name="ptype" value="1"'.($v<0 ? ' checked="checked"':'').'>Forum:</label>
			<select name="pforum">';
			
	foreach ($o as $cid=>$cat)
	{
		$cname = $cat['name'];
		if ($cat['page'] == 1) $cname = 'SMG2.5 - '.$cname;
		
		$fb = mcs_forumBlock($fora, $cid, $v, 0, $fid);
		if (!$fb) continue;
			
		$r .= 
'			<optgroup label="'.htmlspecialchars($cname).'">
'.$fb.
'			</optgroup>
';
	}
	
	$r .= '
			</select>';
			
	return $r;
}
function PowerSelect($id, $s)
{
	$r = Format('
				<select name="{0}">
	', $id);
	if ($s < 0) $s = 0;
	else if ($s > 3) $s = 3;
	$powers = array(0=>__("Regular"), 1=>__("Local mod"), 2=>__("Full mod"), 3=>__("Admin"));
	foreach ($powers as $k => $v)
	{
		$r .= Format('
					<option value="{0}"{2}>{1}</option>
		', $k, $v, ($k == $s ? ' selected="selected"' : ''));
	}
	$r .= '
				</select>';
	return $r;
}

