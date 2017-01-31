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

$cachekey = 'memberlist '.serialize($filters);	// @todo Zeitstempel aus Member-Klasse holen, um bei Veralterung der Daten nicht mehr auf diesen zur�ck zu greifen.
												// alternativ diesen Cache beim �ndern eines Users automatisch l�schen
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