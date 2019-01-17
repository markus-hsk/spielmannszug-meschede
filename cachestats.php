<?php
/**
 * Created by Eclipse.
 * User: mbuscher
 * Date: 01.02.2017
 * Time: 20:02
 */

define('RelativePath', '.');
require_once(RelativePath.'/common.inc.php');

Cache::enable();

$stats = Cache::getStats();

header('Content-Type: application/json');
echo json_encode($stats);