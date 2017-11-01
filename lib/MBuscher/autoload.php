<?php

namespace MBuscher;

spl_autoload_register(function ($class)
{
	switch($class)
	{
		case 'MBuscher\BasicModel':
			include_once(__DIR__.'/BasicModel.class.php');
			break;
			
		case 'MBuscher\Cache':
			include_once(__DIR__.'/Cache.class.php');
			break;
				
		case 'MBuscher\MySqlDb':
		case 'MBuscher\MySqlDbStatic':
			include_once(__DIR__.'/DB.class.php');
			break;
			
		case 'MBuscher\Benchmark':
			include_once(__DIR__.'/Benchmark.class.php');
			break;
	}
}, true, true);