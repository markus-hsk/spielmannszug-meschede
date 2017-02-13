<?php
/**
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 20.12.2016
 * Time: 15:42
 */

define('RelativePath', '.');
require_once(RelativePath.'/common.inc.php');

$filters = $_GET;

$update_ts = Member::getLastUpdateTs();
$cachekey  = 'memberlist '.serialize($filters).' '.$update_ts;

$records = Cache::get($cachekey);
if($records === false)
{
	$members = Member::find($filters);
	
	$records = array();
	foreach ($members as &$Member)
	{
		$records[] = $Member->getDataArrayDeep();
	}
	
	Cache::set($cachekey, $records);
}

header('Content-Type: application/json');
echo json_encode($records);