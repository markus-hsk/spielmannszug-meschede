<?php
/**
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 13.02.2017
 * Time: 10:19
 */


define('RelativePath', '.');
require_once(RelativePath.'/common.inc.php');

$member_id = $_GET['member_id'];


// Daten lesen
$data_raw = file_get_contents('php://input');
if(!strlen($data_raw))
{
	http_response_code(400);
	header('Content-Type: application/json');
	header('Error: No Content-Data transferred');
	echo json_encode([]);
	exit;
}

$data_encoded = json_decode($data_raw, true);
if(!$data_encoded)
{
	http_response_code(400);
	header('Content-Type: application/json');
	header('Error: Data could not be decoded');
	echo json_encode([]);
	exit;
}


if($member_id == 'new')
{
	// Mitglied anlegen
}
else
{
	$member_id = (int) $member_id;

	// Mitglied suchen
	$members = Member::find(['MEMBER_ID' => $member_id]);

	if(count($members) == 1)
	{
		$Member = $members[0];

		$result = $Member->save($data_encoded);

		if($result)
		{
			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([$Member->getDataArrayDeep()]);
			exit;
		}
		else
		{
			http_response_code(400);
			header('Content-Type: application/json');
			header('Error: Member #'.$member_id.' could not be saved');
			echo json_encode([]);
			exit;
		}
	}
	else
	{
		http_response_code(400);
		header('Content-Type: application/json');
		header('Error: Member #'.$member_id.' not found');
		echo json_encode([]);
		exit;
	}
}
