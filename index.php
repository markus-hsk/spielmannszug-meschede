<?php

define('RelativePath', '.');

include_once(RelativePath.'/lib/Skin.class.php');
	
$Skin = new Skin(RelativePath.'/template.html');

echo $Skin;