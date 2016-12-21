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



	/*public static function query($sql)
	{

	}*/


	public static function getRecords($sql)
	{
		static::connect();

		return R::getAll( $sql );
	}
}