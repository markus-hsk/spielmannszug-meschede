<?php
/**
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 27.09.2016
 * Time: 14:01
 */

require_once(RelativePath.'/lib/rb.php');

final class DB
{
	protected static $connected = false;

	const int	= 'int';
	const float	= 'float';
	const text	= 'text';
	const bool	= 'bool';


	public static function init()
	{}


	private static function connect()
	{
		if(!static::$connected)
		{
			R::setup('mysql:host='.DB_HOST.':'.DB_PORT.';dbname='.DB_DATABASE, DB_USER, DB_PASSWORD);
			static::$connected = true;
		}

		return true;
	}



	public static function query($sql)
	{
		static::connect();

		return R::exec($sql);
	}


	public static function getRecords($sql)
	{
		static::connect();

		return R::getAll( $sql );
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
			case 'int':	// Wenn ganzzahlige Werte zur�ck gegeben werden sollen
				return (int)$value;
				break;

			case 'bool':	// Wenn Wahrheitswerte zur�ck gegeben werden sollen
				return $value ? 'TRUE' : 'FALSE';
				break;

			case 'float':		// Wenn Flie�kommazahlen zur�ck gegeben werden sollen
				$float = floatval(str_replace(',', '.', $value));
				if((String) $float == 'INF')
					return 0;
				else
					return $float;
				break;

			case 'unixts':		// Wenn ein Zeitstempel erwartet wird
			case 'date':		// Wenn ein Datumswert erwartet wird
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
			case 'text':
			default:
				return "'" . addslashes($value) . "'";	// Sollte nur beim Debuggen getriggert werden
				break;
		}
	}
}


function toSql($value, $type = '', $empty_as = '')
{
	return DB::toSql($value, $type, $empty_as);
}