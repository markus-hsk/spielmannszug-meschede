<?php
/**
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 02.11.2018
 * Time: 10:17
 */


define('RelativePath', '.');
require_once(RelativePath.'/common.inc.php');

$filters = $_GET;

$update_ts = Event::getLastUpdateTs();
$cachekey  = 'events '.serialize($filters).' '.$update_ts;

$records = Cache::get($cachekey);
if($records === false)
{
	$events = Event::find($filters);

	$records = array();
	foreach ($events as &$Event)
	{
		$records[] = $Event->getDataArrayDeep();
	}

	Cache::set($cachekey, $records);
}

header('Content-Type: application/json');
echo json_encode($records);