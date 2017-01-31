<?php

function getDurationYears($date, $until = '')
{
	if($until == '' || $until === null)
		$until = date('Y-m-d H:i:s', time());

	$date_ts  = strtotime($date);
	$until_ts = strtotime($until);

	$age = date("Y", $until_ts) - date("Y", $date_ts);
	if(date("z", $until_ts) < date("z", $date_ts))
		$age--;

	return $age;
}


function makeDirs($strPath, $mode = 0777)
{
   return is_dir($strPath) or ( makeDirs(dirname($strPath), $mode) and mkdir($strPath, $mode) );
}