<?php
/**
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 20.12.2016
 * Time: 15:42
 */

define('RelativePath', '.');
require_once(RelativePath.'/common.inc.php');

$sql = 'SELECT * FROM spz_members';
$records = DB::getRecords( $sql );

foreach($records as &$record)
{
	// Zusätzliche Daten
	if(!strlen($record['DEATHDATE']) || $record['DEATHDATE'] == '0000-00-00')
	{
		$record['AGE'] = getAge($record['BIRTHDATE']);
	}
	else
	{
		$deathdate = strtotime($record['DEATHDATE']);
		$record['AGE'] = getAge($record['BIRTHDATE'], $deathdate);
	}
}

header('Content-Type: application/json');
echo json_encode($records);