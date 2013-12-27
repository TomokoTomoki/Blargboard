<?php
//  AcmlmBoard XD - Member list page
//  Access: all


$title = __("Member list");


function PageLinks2($url, $epp, $from, $total)
{
	if ($total < 1) return '';

	$numPages = ceil($total / $epp);
	$page = ceil($from / $epp) + 1;

	$first = ($from) ? "<a class=\"pagelink\" href=\"".$url."0)\">&#x00AB;</a> " : "";
	$prev = ($from) ? "<a class=\"pagelink\" href=\"".$url.($from - $epp).")\">&#x2039;</a> " : "";
	$next = ($from < $total - $epp) ? " <a class=\"pagelink\" href=\"".$url.($from + $epp).")\">&#x203A;</a>" : "";
	$last = ($from < $total - $epp) ? " <a class=\"pagelink\" href=\"".$url.(($numPages * $epp) - $epp).")\">&#x00BB;</a>" : "";

	$pageLinks = array();
	for($p = $page - 5; $p < $page + 10; $p++)
	{
		if($p < 1 || $p > $numPages)
			continue;
		if($p == $page || ($from == 0 && $p == 1))
			$pageLinks[] = $p;
		else
			$pageLinks[] = "<a class=\"pagelink\" href=\"".$url.(($p-1) * $epp).")\">".$p."</a>";
	}

	return $first.$prev.join(array_slice($pageLinks, 0, 11), " ").$next.$last;
}


if ($_GET['listing'])
{
	$tpp = $loguser['threadsperpage'];
	if($tpp<1) $tpp=50;

	if(isset($_GET['from']))
		$from = (int)$_GET['from'];
	else
		$from = 0;

	if(isset($dir)) unset($dir);
	if(isset($_GET['dir']))
	{
		$dir = $_GET['dir'];
		if($dir != "asc" && $dir != "desc")
			unset($dir);
	}

	$sort = $_GET['sort'];
	if(!in_array($sort, array('', 'id', 'name', 'karma', 'reg')))
		unset($sort);

	$sex = $_GET['sex'];
	if(isset($_GET['pow']) && $_GET['pow'] != "")
	{
		if ($_GET['pow'] == 'staff')
		{
			$pow = array();
			foreach ($usergroups as $g)
			{
				if ($g['display'] == 1)
					$pow[] = $g['id'];
			}
		}
		else
			$pow = (int)$_GET['pow'];
	}

	$order = "";
	$where = "";

	switch($sort)
	{
		case "id": $order = "id ".(isset($dir) ? $dir : "asc"); break;
		case "name": $order = "name ".(isset($dir) ? $dir : "asc"); break;
		case "reg": $order = "regdate ".(isset($dir) ? $dir : "desc"); break;
		default: $order="posts ".(isset($dir) ? $dir : "desc");
	}

	switch($sex)
	{
		case "m": $where = "sex=0"; break;
		case "f": $where = "sex=1"; break;
		case "n": $where = "sex=2"; break;
		default: $where = "1";
	}

	if(isset($pow))
	{
		if (is_array($pow))
			$where .= " AND primarygroup IN ({2c})";
		else if ($usergroups[$pow]['type'] == 0)
			$where .= " AND primarygroup={2}";
		else
			$where .= " AND (SELECT COUNT(*) FROM {secondarygroups} sg WHERE sg.userid=id AND sg.groupid={2})>0";
	}

	$query = $_GET['query'];

	if($query != "") {
			$where.= " and name like {3} or displayname like {3}";
	}

	$numUsers = FetchResult("select count(*) from {users} where ".$where, null, null, $pow, "%{$query}%");
	$rUsers = Query("select * from {users} where ".$where." order by ".$order.", name asc limit {0u},{1u}", $from, $tpp, $pow, "%{$query}%");

	$pagelinks = PageLinks2("javascript:refreshMemberlist(", $tpp, $from, $numUsers);

	$ajaxPage = true;

	echo "	<table class=\"outline margin\">";

	if($numUsers)
	{
		if($numUsers == 1)
			$nu = __("1 user found.");
		else
			$nu = format(__("{0} users found."), $numUsers);

		echo "
			<tr class=\"cell1\">
				<td colspan=\"2\">
				</td>
				<td colspan=\"6\">
					$nu
				</td>
			</tr>";
	}
	if($pagelinks)
	{
		echo "
			<tr class=\"cell2\">
				<td colspan=\"2\">
					".__("Page")."
				</td>
				<td colspan=\"6\">
					$pagelinks
				</td>
			</tr>";
	}

	$memberList = "";
	if($numUsers)
	{
		while($user = Fetch($rUsers))
		{
			$daysKnown = (time()-$user['regdate'])/86400;
			$user['average'] = sprintf("%1.02f", $user['posts'] / $daysKnown);

			$userPic = '';

			if($user["picture"] == "#INTERNAL#")
				$userPic = "<img src=\"${dataUrl}avatars/".$user['id']."\" alt=\"\" style=\"max-width: 60px;max-height:60px;\" />";
			else if($user["picture"])
				$userPic = "<img src=\"".htmlspecialchars($user["picture"])."\" alt=\"\" style=\"max-width: 60px;max-height:60px;\" />";

			$userPic = "<div style=\"width:60px; height:60px;\">{$userPic}</div>";

			$cellClass = ($cellClass+1) % 2;
			$memberList .= format(
	"
			<tr class=\"cell{0}\">
				<td>{1}</td>
				<td class=\"center\">{2}</td>
				<td>{3}</td>
				<td>{4}</td>
				<td>{5}</td>
				<td>{7}</td>
				<td>{8}</td>
			</tr>
	",	$cellClass, $user['id'], $userPic, UserLink($user), $user['posts'],
		$user['average'], null,
		($user['birthday'] ? cdate("M jS", $user['birthday']) : "&nbsp;"),
		cdate("M jS Y", $user['regdate'])
		);
		}
	}
	else
	{
		$memberList = "
			<tr class=\"cell0\">
				<td colspan=\"8\">
					".__("Nothing matched your search.")."
				</td>
			</tr>";
	}

	echo "
			<tr class=\"header1\">
				<th style=\"width: 30px; \">#</th>
				<th style=\"width: 62px; \">".__("Picture")."</th>
				<th>".__("Name")."</th>
				<th style=\"width: 50px; \">".__("Posts")."</th>
				<th style=\"width: 50px; \">".__("Average")."</th>
				<th style=\"width: 80px; \">".__("Birthday")."</th>
				<th style=\"width: 130px; \">".__("Registered on")."</th>
			</tr>
			$memberList";

	if($pagelinks)
	{
		echo "
			<tr class=\"cell2\">
				<td colspan=\"2\">
					".__("Page")."
				</td>
				<td colspan=\"6\">
					$pagelinks
				</td>
			</tr>";
	}

	echo "</table>";

	die();
}


$allgroups = array();
$allgroups[''] = __('(any)');
$g = Query("SELECT id,name,type FROM {usergroups} WHERE display>-1 ORDER BY type, rank");

$allgroups[__('Primary')] = null;
$s = false;
while ($group = Fetch($g))
{
	if (!$s && $group['type'] == 1)
	{
		$s = true;
		$allgroups['staff'] = __('(all staff)');
		$allgroups[__('Secondary')] = null;
	}
	
	$allgroups[$group['id']] = $group['name'];
}


MakeCrumbs(array(actionLink("memberlist") => __("Member list")), $links);

if (!$isBot)
{
	echo "
	<script type=\"text/javascript\" src=\"".resourceLink("js/memberlist.js")."\"></script>
	<table>
	<tr>
	<td id=\"userFilter\" style=\"margin-bottom: 1em; margin-left: auto; margin-right: auto; padding: 1em; padding-bottom: 0.5em; padding-top: 0.5em;\">
		<label>
		".__("Sort by").":
		".makeSelect("orderBy", array(
			"" => __("Post count"),
			"id" => __("ID"),
			"name" => __("Name"),
			"reg" => __("Registration date")
		))." &nbsp;
		</label>
		<label>
		".__("Order").":
		".makeSelect("order", array(
			"desc" => __("Descending"),
			"asc" => __("Ascending"),
		))." &nbsp;
		</label>
		<label>
		".__("Sex").":
		".makeSelect("sex", array(
			"" => __("(any)"),
			"n" => __("N/A"),
			"f" => __("Female"),
			"m" => __("Male")
		))." &nbsp;
		</label>
		<label>
		".__("Group").":
		".makeSelect("power", $allgroups)."
		</label>
	</td>
	<td style=\"text-align: right;\">
			<form action=\"javascript:refreshMemberlist();\">
				<div style=\"display:inline-block\">
					<input type=\"text\" name=\"query\" id=\"query\" placeholder=\"".__("Search")."\" />
					<button id=\"submitQuery\">&rarr;</button>
				</div>
			</form>
	</td></tr></table>";
}

echo "
	<div id=\"memberlist\">
		<div class=\"center\" style=\"padding: 2em;\">
			".__("Loading memberlist...")."
		</div>
	</div>";


//We do not need a default index.
//All options are translatable too, so no need for __() in the array.
//Name is the same as ID.

function makeSelect($name, $options) 
{
	$result = "<select name=\"".$name."\" id=\"".$name."\">";

	$i = 0;
	$hasgroups = false;
	foreach ($options as $key => $value) 
	{
		if ($value == null)
		{
			if ($hasgroups) $result .= "\n\t</optgroup>";
			$result .= "\n\t<optgroup label=\"".$key."\">";
			$hasgroups = true;
			continue;
		}
		
		$result .= "\n\t<option".($i = 0 ? " selected=\"selected\"" : "")." value=\"".$key."\">".$value."</option>";
	}

	if ($hasgroups) $result .= "\n\t</optgroup>";
	$result .= "\n</select>";

	return $result;
}


?>
