<?php

define('RelativePath', '.');
include_once(RelativePath.'/lib/rb.php');
include_once(RelativePath.'/lib/Skin.class.php');
include_once(RelativePath.'/cfg/database.php');
include_once(RelativePath.'/lib/utils.php');

$records = R::getAll( 'SELECT * FROM spz_members' );

$Skin = new Skin(RelativePath.'/templates/members.html');


$rows = array();
/*foreach ($records as &$record)
{
	$row = $record;

	if($record['GENDER'] == 'w')
		$row['GENDER'] = '<i class="fa fa-venus female" aria-hidden="true" aria-label="weiblich"></i>';
	else
		$row['GENDER'] = '<i class="fa fa-mars male" aria-hidden="true" aria-label="männlich"></i>';
	
	$row['BIRTHDATE'] = date('d.m.Y', strtotime($record['BIRTHDATE']));
	
	if(!strlen($record['DEATHDATE']) || $record['DEATHDATE'] == '0000-00-00')
	{
		$row['AGE'] = '('.getAge($record['BIRTHDATE']).')';
		
		$row['STATE'] = $record['CURRENT_STATE'];
	}
	else
	{
		$deathdate = strtotime($record['DEATHDATE']);
		$row['DEAD'] = ' <b>&dagger;</b>';
		$row['BIRTHDATE'] .= ' -';
		$row['AGE'] = date('d.m.Y', $deathdate). ' ('.getAge($record['BIRTHDATE'], $deathdate).')';
		$row['STATE'] = 'verstorben';
	}
	
	$row['CONTACT'] = '';
	
	$rows[] = $row;
}
$Skin->setSkinVar('ROWS', $rows);*/

echo $Skin;