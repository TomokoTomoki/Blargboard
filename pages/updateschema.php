<?php

if(!$loguser['root'])
	Kill(__("You're not an administrator. There is nothing for you here."));

MakeCrumbs(array(__("Admin") => actionLink("admin"), __("Update table structure") => actionLink("updateschema")), "");

Upgrade();

?>

