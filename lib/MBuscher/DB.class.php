<?php

namespace MBuscher;

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

final class MySqlDb
{
	protected $db_host		= '';
	protected $db_port		= '';
	protected $db_user		= '';
	protected $db_password	= '';
	protected $db_database  = '';
	
	/** @var string $table_prefix allows to define a prefix for the tables names to be automatically prepended on the given tables */
	protected $table_prefix = '';
	
	/** @var mysqli $db */
	protected $db = null;
	
	protected $result = null;



	function __construct($host, $port, $user, $password, $database, $table_prefix = '')
	{
		$this->db_host		= $host;
		$this->db_port		= $port;
		$this->db_user 		= $user;
		$this->db_password	= $password;
		$this->db_database	= $database;
		$this->table_prefix	= $table_prefix;
		
		$this->connect();
	}
	
	
	protected function connect()
	{
		if($this->db === null)
		{
			// @todo implement retries on failure
			$this->db = mysqli_connect($this->db_host, $this->db_user, $this->db_password, $this->db_database, $this->db_port);
			mysqli_set_charset($this->db, 'utf8');
		}

		return true;
	}
	
	
	public function select($table, array $fields = [], array $filter = null, array $sort = null, array $limit = null, array $options = [])
	{
		$cache_key = $this->getCacheKey('select', func_get_args());
		if(($cached = Cache::get($cache_key)) !== false)
		{
			return $cached;
		}
		
		$sql = "SELECT ";
		
		// append the field list
		if(count($fields) == 0)
		{
			$sql .= "* ";
		}
		else
		{
			$sql .= "`" . implode("`, `", $fields) . "` ";
		}
		
		// append table name
		$sql .= " FROM `".$this->table_prefix . $table . "` ";
		
		$where = $this->buildWhere($filter);
		if(strlen($where))
		{
			$sql .= "WHERE $where ";
		}
		
		if(is_array($sort))
		{
			$order_by = "";
				
			foreach($sort as $field => $dir)
			{
				$order_by .= "`$field` ".($dir == SORT_DESC ? 'DESC' : 'ASC')." ";
			}
				
			if(strlen($order_by))
			{
				$sql .= "ORDER BY $order_by";
			}
		}
		
		if(is_array($limit))
		{
			list($skip, $amount) = $limit;
			$sql .= "LIMIT $skip,$amount";
		}
		echo $sql;
		if($result = mysqli_query($this->db, $sql))
		{
			$records = array();
			while($data = mysqli_fetch_assoc($result))
			{
				$records[] = $data;
			}
			
			Cache::set($cache_key, $records);
				
			return $records;
		}
		else
		{
			$error_code = mysqli_errno($this->db);
			$error_message = mysqli_error($this->db);
			
			throw new MySqlDbException($error_message, $error_code);
		}
	}
	
	
	
	protected function buildWhere(array $filter = null)
	{
		if(is_array($filter) && count($filter) > 0)
		{
			$wheres = array();
	
			foreach($filter as $key => $value)
			{
				switch($key)
				{
					case '$like':
						$wheres[] = "`$key` LIKE '$value'";
						break;
	
					default:
						if(!is_numeric($value))
						{
							$wheres[] = "`$key` = ".$this->toSql($value, dbText);
						}
						else if(!is_int($value))
						{
							$wheres[] = "`$key` = ".$this->toSql($value, dbFloat);
						}
						else
						{
							$wheres[] = "`$key` = ".$this->toSql($value, dbInt);
						}
						break;
				}
			}
	
			return implode(' AND ', $wheres);
		}
		else
		{
			return '';
		}
	}
	
	protected function toSql($value, $value_type = '', $empty_as = '')
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
				return "'" . mysqli_real_escape_string($this->db, $value) . "'";
				break;
		}
	}
	
	protected function getCacheKey()
	{
		$args = serialize(func_get_args());
		
		return 'MySqlDb '.$this->db_host.':'.$this->db_port.' '.$this->db_database.' '.$this->table_prefix.' '.$args;
	}
}


final class MySqlDbStatic
{
	/** @var MySqlDb $db_instance */
	protected static $db_instance;
	
	
	private function __construct(){}
	private function __clone(){}
	
	
	static function connect($host, $port, $user, $password, $database, $table_prefix = '')
	{
		static::$db_instance = new MySqlDb($host, $port, $user, $password, $database, $table_prefix);
	}
	
	
	/**
	 * @param	void
	 * @return 	MySqlDb
	 */
	static function getDbInstance()
	{
		if(static::$db_instance === null)
		{
			trigger_error('There is no static instance connected yet', E_USER_ERROR);
		}
	
		return static::$db_instance;
	}
	
	
	
	public static function select($table, array $fields = [], array $filter = null, array $sort = null, array $limit = null, array $options = [])
	{
		return static::getDbInstance()->select($table, $fields, $filter, $sort, $limit, $options);
	}
}


final class MySqlDbException extends \Exception
{}