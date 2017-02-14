<?php
/**
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 14.02.2017
 * Time: 15:38
 */


define('RelativePath', '.');
require_once(RelativePath.'/common.inc.php');

$member_id = $_GET['member_id'];

$member_id = (int) $member_id;

// Mitglied suchen
$members = Member::find(['MEMBER_ID' => $member_id]);

if(count($members) == 1)
{
	$Member = $members[0];

	$result = $Member->delete();

	if($result)
	{
		http_response_code(200);
		header('Content-Type: application/json');
		echo json_encode([]);
		exit;
	}
	else
	{
		http_response_code(400);
		header('Content-Type: application/json');
		header('Error: Member #'.$member_id.' could not be deleted');
		echo json_encode([]);
		exit;
	}
}
else
{
	http_response_code(404);
	header('Content-Type: application/json');
	header('Error: Member #'.$member_id.' not found');
	echo json_encode([]);
	exit;
}