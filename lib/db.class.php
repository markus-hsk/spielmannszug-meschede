<?php
/**
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 27.09.2016
 * Time: 14:01
 */


final class DB
{
	protected static $Instance = null;

	private function __construct() {}
	private function __clone() {}

	protected function connect()
	{

	}



	public static function query($sql, $values)
	{
		foreach($values as $varname => $value)
		{
			str_replace("$varname", $dbValue->get())
		}
	}

}









abstract class dbValue
{
	protected $value;

	abstract function __construct($value, $options = null);

	function get()
	{
		return $this->value;
	}
}

class dbInteger extends dbValue
{
	public function __construct($value, $options = null)
	{
		$this->value = (int) $value;
	}
}

class dbFloat extends dbValue
{
	public function __construct($value, $options = null)
	{
		$this->value = (float) $value;
	}
}