<?php
/**
 * Diese Datei liefert ein JSON mit allen Mitgliedern passend zu den gewählten Filtern zurück
 *
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 06.09.2016
 * Time: 21:11
 */

define('RelativePath', '.');
include_once(RelativePath.'/common.inc.php');


$filter_search  = isset($_GET['search']) ? strip_tags($_GET['search']) : '';
$filter_state   = isset($_GET['state'])  ? $_GET['state']              : [];
$filter_gender  = isset($_GET['gender']) ? $_GET['gender']             : [];
$filter_age     = isset($_GET['age'])    ? $_GET['age']                : [];

$rb_filters = [];
$sql = "SELECT * FROM spz_members ";
$sql_where = '';

if(strlen($filter_search))
{
	$sql_where = "WHERE (LASTNAME LIKE :search OR FIRSTNAME LIKE :search OR BIRTHNAME LIKE :search OR
						 STREET LIKE :search OR ZIP LIKE :search OR CITY LIKE :search ) ";
	$rb_filters[':search'] = '%'.$filter_search.'%';
}

if(is_array($filter_state) && count($filter_state))
{
	$states = array();

	if(in_array('aktiv', $filter_state))
		$states[] = 'aktiv';

	if(in_array('passiv', $filter_state))
		$states[] = 'passiv';

	if(in_array('ehemalig', $filter_state))
		$states[] = 'ehemalig';

	if(in_array('ehemalig', $filter_state))
		$states[] = 'ausbildung';

	if(in_array('verstorben', $filter_state))
	{
		$states[] = 'verstorben';
		$sql_where .= strlen($sql_where) ? " AND " : "WHERE ".
						"( DEATHDATE IS NOT NULL AND DEATHDATE != '0000-00-00' ) ";
	}

	if(count($states))
	{
		$sql_where .= strlen($sql_where) ? " AND " : "WHERE ".
					  "CURRENT_STATE IN :states ";
		$rb_filters[':states'] = $states;
	}
}

if(is_array($filter_gender) && count($filter_gender))
{
	if(in_array('m', $filter_gender))
		$genders[] = 'm';

	if(in_array('w', $filter_gender))
		$genders[] = 'w';

	if(count($genders))
	{
		$sql_where .= strlen($sql_where) ? " AND " : "WHERE ".
			"GENDER IN :genders ";
		$rb_filters[':genders'] = $genders;
	}
}

echo '<br><br><pre>';

R::fancyDebug( TRUE );

$members = R::getAll( $sql.$sql_where, [':search' => '%'.$filter_search.'%']);

print_r($members);