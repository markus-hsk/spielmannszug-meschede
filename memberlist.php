<?php
/**
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 20.12.2016
 * Time: 15:42
 */

define('RelativePath', '.');
require_once(RelativePath.'/common.inc.php');


$members = Member::find([]);

$records = array();
foreach ($members as &$Member)
{
	$records[] = $Member->getDataArrayDeep();
}


header('Content-Type: application/json');
echo json_encode($records);