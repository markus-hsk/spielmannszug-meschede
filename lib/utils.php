<?php
function getAge($date, $until = '') {
	if($until == '')
		$until = time();
	
	return intval(date('Y', $until - strtotime($date))) - 1970;
}