<?php

define('RelativePath', '.');
require_once(RelativePath.'/common.inc.php');

$Skin = new Skin(RelativePath.'/templates/members.html');
echo $Skin;