<?php

function getAge($date, $until = '')
{
	if($until == '')
		$until = time();
	
	return intval(date('Y', $until - strtotime($date))) - 1970;
}


function makeDirs($strPath, $mode = 0777)
{
   return is_dir($strPath) or ( makeDirs(dirname($strPath), $mode) and mkdir($strPath, $mode) );
}