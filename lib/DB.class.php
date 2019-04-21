<?php
/**
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 27.09.2016
 * Time: 14:01
 */


define('dbInt',		'int');
define('dbFloat',	'float');
define('dbText',	'text');
define('dbBool',	'bool');
define('dbDate',	'date');

final class DB
{
	/** @var mysqli $DB */
	protected static $DB = null;

	/** @var mysqli_result $Result */
	protected static $Result = false;



	private function __construct() {}
	private function __clone() {}


	public static function init()
	{}


	private static function connect()
	{
		if(static::$DB === null)
		{
			static::$DB = mysqli_connect(MBU_VTOOL_DB_HOST, MBU_VTOOL_DB_USER, MBU_VTOOL_DB_PASSWORD, MBU_VTOOL_DB_DATABASE, MBU_VTOOL_DB_PORT);
			mysqli_set_charset(static::$DB, 'utf8');
		}

		return true;
	}



	public static function query($sql)
	{
		static::connect();

		static::$Result = mysqli_query(static::$DB, $sql);

		if(static::$Result)
			return true;
		else
			return false;
	}


	public static function getRecords($sql)
	{
		static::connect();

		$result = static::query($sql);
		if($result)
		{
			$records = array();
			while($data = mysqli_fetch_assoc(static::$Result))
			{
				$records[] = $data;
			}

			return $records;
		}
		else
			return false;
	}


	public static function getCachedRecords($sql)
	{
		$cache = Cache::get($sql);

		if($cache !== false)
		{
			return $cache;
		}
		else
		{
			$records = static::getRecords($sql);

			Cache::set($sql, $records);

			return $records;
		}
	}


	public static function getInsertId()
	{
		static::connect();
		return mysqli_insert_id(static::$DB);
	}


	static function toSql($value, $value_type = '', $empty_as = '')
	{
		if($value === NULL)
		{
			if($empty_as === NULL)
				return 'NULL';
			else
				$value = $empty_as;
		}

		switch($value_type)
		{
			case dbInt:	// Wenn ganzzahlige Werte zur�ck gegeben werden sollen
				return (int)$value;
				break;

			case dbBool:	// Wenn Wahrheitswerte zur�ck gegeben werden sollen
				return $value ? 'TRUE' : 'FALSE';
				break;

			case dbFloat:		// Wenn Flie�kommazahlen zur�ck gegeben werden sollen
				$float = floatval(str_replace(',', '.', $value));
				if((String) $float == 'INF')
					return 0;
				else
					return $float;
				break;

			case 'unixts':		// Wenn ein Zeitstempel erwartet wird
			case dbDate:		// Wenn ein Datumswert erwartet wird
			case 'datetime':	// Wenn ein Datum mit Zeit erwartet wird
				if(is_int($value))
				{
					if($value > 0)
						return "'".date('Y-m-d H:i:s', $value)."'";
					else
						return "'0000-00-00 00:00:00'";
				}
				else {
					if (is_string($value) && strlen($value) == 0) {
						return "'0000-00-00 00:00:00'";
					}

					return "'".date('Y-m-d H:i:s', strtotime($value))."'";
				}

				/* Der Wert wird umgewandelt in einen Zeitstempel und dann als normaler Text weiter verarbeitet */
				break;

			case 'time':		// Wenn nur eines Uhrzeit erwartet wird
				if(is_int($value))
				{
					if($value > 0)
						return "'".date('H:i:s', $value)."'";
					else
						return "'00:00:00'";
				}
				else
					return "'".date('H:i:s', strtotime($value))."'";
				/* Der Wert wird umgewandelt in einen Zeitstempel und dann als normaler Text weiter verarbeitet */
				break;


			case '':
			case dbText:
			default:
				static::connect();
				return "'" . mysqli_real_escape_string(static::$DB, $value) . "'";
				break;
		}
	}
}


function toSql($value, $type = '', $empty_as = '')
{
	return DB::toSql($value, $type, $empty_as);
}