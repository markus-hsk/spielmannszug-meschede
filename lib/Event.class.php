<?php
/**
 * Created by PhpStorm.
* User: mbuscher
* Date: 02.11.2018
* Time: 10:18
*/


class Event
{
	private $event_id  = 0;
	private $data_array = array();



	// ##### Magische Methoden #########################################################################################

	private function __construct($event_id)
	{
		$this->event_id = (int) $event_id;
	}

	private function __clone() {}

	public function __get($fieldname)
	{
		return $this->get($fieldname);
	}

	public function __toString()
	{
		$array = var_export($this->data_array, true);
		$output = str_replace(['array', '(', ')'], '', $array);
		return 'Event #'.$this->event_id.":\r".$output;
	}


	// ##### Instanziierungsfunktionen #################################################################################

	/**
	 * Liefert ein Array von Eventinstanzen wieder
	 *
	 * @param	array		$filters		Filterregeln
	 * @return	Event[]
	 * @since	28.12.2016
	 */
	public static function find(array $filters = [])
	{
		$sql = "SELECT *
				FROM spz_events";

		if(count($filters) > 0)
		{
			$where = array();
			foreach ($filters as $field => $value)
			{
				switch($field)
				{
					case 'EVENT_ID':
						$where[] = "`$field` = ".((int) $value);
						break;
				}
			}

			if(count($where) > 0)
			{
				$sql .= " WHERE ".implode(' AND ', $where);
			}
		}

		$records = DB::getCachedRecords( $sql );

		foreach ($records as &$record)
		{
			$record = static::compose($record);
		}

		return $records;
	}

	public static function compose(array $data_array)
	{
		$Event = new static($data_array['EVENT_ID']);
		$Event->setDataArray($data_array);

		return $Event;
	}


	public static function create(array $data_array)
	{
		$defaults = array('NAME' => '');

		$data_array = array_merge($defaults, $data_array);

		$sql = "INSERT INTO spz_events SET NAME = ".toSql($data_array['NAME']);
		DB::query($sql);

		$event_id = DB::getInsertId();

		if($event_id > 0)
		{
			$events = static::find(['EVENT_ID' => $event_id]);
			$Event = $events[0];
			
			return $Event;
		}
		else
			return false;
	}




	// ##### Instanzmethoden ###########################################################################################

	public function get($fieldname)
	{
		if($fieldname == 'UPDATE_TS' || $fieldname == 'INSERT_TS')
		{
			return date('Y-m-d H:i:s', max(filemtime(__FILE__), strtotime($this->data_array[$fieldname])));
		}
		else if(isset($this->data_array[$fieldname]))
		{
			return $this->data_array[$fieldname];
		}
		else
		{
			return null;
		}
	}

	protected function setDataArray(array $data_array)
	{
		$this->data_array = $data_array;
	}

	public function getDataArray()
	{
		return $this->data_array;
	}

	public function getDataArrayDeep()
	{
		$data_array = $this->data_array;

		return $data_array;
	}

	public function save($data)
	{
		foreach($data as $key => $value)
		{
			if(isset($this->data_array[$key]))
			{
				switch($key)
				{
					default:
						$this->data_array[$key] = $value;
						break;
				}
			}
		}

		$sql = "UPDATE spz_events SET NAME = ".toSql($this->NAME);
		$done = DB::query($sql);

		// Update-TS setzen
		$this->data_array['UPDATE_TS'] = date('Y-m-d H:i:s');
		static::setLastUpdateTs();

		return true;
	}


	public function delete()
	{
		$sql = "DELETE FROM spz_events
				WHERE EVENT_ID = ".toSql($this->event_id, dbInt);
		DB::query($sql);

		$this->data_array = array();
		static::setLastUpdateTs();

		return true;
	}


	public static function setLastUpdateTs()
	{
		$date = date('Y-m-d H:i:s');
		$done = Cache::set('EventLastUpdate', $date);
		return $done ? $date : false;
	}


	public static function getLastUpdateTs()
	{
		$cached = Cache::get('EventLastUpdate');
		if($cached === false)
			return static::setLastUpdateTs();
		else
			return $cached;
	}
}