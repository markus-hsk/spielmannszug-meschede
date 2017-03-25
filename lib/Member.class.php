<?php
/**
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 28.12.2016
 * Time: 16:18
 */


class Member
{
	private $member_id  = 0;
	private $data_array = array();

	private $current_state = null;
	private $state_history = null;
	private $aktive_jahre  = null;
	private $contact_data  = null;
	


	// ##### Magische Methoden #########################################################################################

	private function __construct($member_id)
	{
		$this->member_id = (int) $member_id;
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
		return 'Member #'.$this->member_id.":\r".$output;
	}


	// ##### Instanziierungsfunktionen #################################################################################

	/**
	 * Liefert ein Array von Mitgliederinstanzen wieder
	 *
	 * @param	array		$filters		Filterregeln
	 * @return	Member[]
	 * @since	28.12.2016
	 */
	public static function find(array $filters = [])
	{
		$sql = "SELECT * 
				FROM spz_members";

		if(count($filters) > 0)
		{
			$where = array();
			foreach ($filters as $field => $value)
			{
				switch($field)
				{
					case 'MEMBER_ID':
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
			$record = Member::compose($record);
		}

		return $records;
	}

	public static function compose(array $data_array)
	{
		$Member = new Member($data_array['MEMBER_ID']);
		$Member->setDataArray($data_array);

		return $Member;
	}


	public static function create(array $data_array)
	{
		$defaults = array('LASTNAME' 	=> '',
						  'FIRSTNAME'	=> '',
						  'BIRTHNAME'	=> '',
						  'GENDER'		=> 'm',
						  'STREET'		=> '',
						  'ZIP'			=> '',
						  'CITY'		=> '',
					  	  'BIRTHDATE'	=> null,
						  'DEATHDATE'	=> null,
						  'INSTRUMENT'	=> '');

		$data_array = array_merge($defaults, $data_array);

		$sql = "INSERT INTO spz_members SET 
					LASTNAME	= ".toSql($data_array['LASTNAME']).",
					FIRSTNAME	= ".toSql($data_array['FIRSTNAME']).",
					BIRTHNAME	= ".toSql($data_array['BIRTHNAME']).",
					GENDER		= ".toSql(($data_array['GENDER'] == 'm' ? 'm' : 'w')).",
					STREET		= ".toSql($data_array['STREET']).",
					ZIP			= ".toSql($data_array['ZIP']).",
					CITY		= ".toSql($data_array['CITY']).",
					BIRTHDATE	= ".toSql($data_array['BIRTHDATE'], 'date').",
					DEATHDATE	= ".toSql($data_array['DEATHDATE'], 'date', null).",
					INSTRUMENT	= ".toSql($data_array['INSTRUMENT']);
		DB::query($sql);

		$member_id = DB::getInsertId();

		if($member_id > 0)
		{
			$members = Member::find(['MEMBER_ID' => $member_id]);
			$Member = $members[0];

			if(isset($data['CONTACT']))
			{
				foreach($data['CONTACT'] as $type => $value)
				{
					$Member->saveContactData($type, $value);
				}
			}

			if(isset($data['STATES']))
			{
				foreach($data['STATES'] as $state)
				{
					$Member->saveMembershipState($state);
				}
			}
		}
		else
			return false;
	}




	// ##### Instanzmethoden ###########################################################################################

	public function get($fieldname)
	{
		if($fieldname == 'UPDATE_TS')
		{
			return date('Y-m-d H:i:s', max(filemtime(__FILE__), strtotime($this->data_array['UPDATE_TS'])));
		}
		else if($fieldname == 'CURRENT_STATE')
		{
			return $this->getCurrentState();
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

		// Alter ermitteln und anhängen
		$data_array['AGE'] = $this->getAge();

		// Mitgliedsstatus etc. anhängen
		$data_array['STATES']        = $this->getMembershipStates();
		$data_array['CURRENT_STATE'] = $this->getCurrentState();
		$data_array['AKTIV_JAHRE']   = $this->getAktiveJahre();
		
		// Kontaktdaten anhängen
		$data_array['CONTACT'] = $this->getContactData();
		
		return $data_array;
	}

	public function getAge()
	{
		// Zusätzliche Daten
		if(!strlen($this->data_array['DEATHDATE']) || $this->data_array['DEATHDATE'] == '0000-00-00')
		{
			return getDurationYears($this->data_array['BIRTHDATE']);
		}
		else
		{
			$deathdate = $this->data_array['DEATHDATE'];
			return getDurationYears($this->data_array['BIRTHDATE'], $deathdate);
		}
	}




	public function getMembershipStates()
	{
		if($this->state_history === null)
		{
			// @todo Statushistorie abrufen
			$this->state_history = array();

			$sql = "SELECT * /* Member->getMembershipStates() LastUpdate: ".$this->UPDATE_TS." */
					FROM spz_membership_states
					WHERE MEMBER_ID = ".((int) $this->member_id)."
					ORDER BY START_DATE ASC";
			$records = DB::getCachedRecords( $sql );

			foreach($records as &$record)
			{
				$this->state_history[] = $record;
			}
		}

		return $this->state_history;
	}


	public function getCurrentState()
	{
		if($this->DEATHDATE !== null)
			return 'verstorben';

		if($this->current_state === null)
		{
			$states 		 = $this->getMembershipStates();
			$last_membership = '0000-00-00';
			$vorstand       = false;
			$ehrenmitglied  = false;

			foreach($states as $state)
			{
				if($state['END_DATE'] == null)
				{
					if(in_array($state['STATE'], ['1vorsitz','2vorsitz','schrift','kassierer','major']))
					{
						$vorstand = true;
					}
					else if($state['STATE'] == 'Ehrenmitglied')
					{
						$ehrenmitglied = true;
					}
					else if(in_array($state['STATE'], ['aktiv','passiv','schrift','kassierer','major']))
					{
						$this->current_state = $state;
					}
				}
				else
				{
					$last_membership = max($last_membership, $state['END_DATE']);
				}
			}

			// Wurde kein offener Status gefunden, dann ist die Person kein Mitglied mehr im Verein und damit ehemalig
			if($this->current_state === null)
			{
				$this->current_state = array('MEMBERSHIP_ID' => -1,
											 'MEMBER_ID' => $this->member_id,
											 'STATE' => 'Ehemalig',
											 'START_DATE' => $last_membership,
											 'END_DATE' => null
											);
			}
			else
			{
				if($ehrenmitglied)
				{
					$this->current_state['STATE'] = 'Ehrenmitglied';
				}
				else if($vorstand)
				{
					$this->current_state['STATE'] = 'Vorstand';
				}
			}
		}

		return $this->current_state['STATE'];
	}

	public function getAktiveJahre()
	{
		if($this->aktive_jahre === null)
		{
			$states = $this->getMembershipStates();

			$this->aktive_jahre = 0;

			foreach($states as $state)
			{
				if($state['STATE'] == 'aktiv')
				{
					// Es handelt sich um einen "aktiv"-Status
					$this->aktive_jahre += getDurationYears($state['START_DATE'], $state['END_DATE']);
				}
			}
		}

		return $this->aktive_jahre;
	}


	public function getContactData()
	{
		if($this->contact_data === null)
		{
			$this->contact_data = array('email' => '',
										'mobile' => '',
										'phone' => '');
			
			// Kontaktdaten abrufen
			$sql = "SELECT * /* Member->getContactData() LastUpdate: ".$this->UPDATE_TS." */
					FROM spz_contact_informations
					WHERE MEMBER_ID = ".((int) $this->member_id)."
					ORDER BY CONTACT_TYPE";
			$records = DB::getCachedRecords( $sql );
			
			foreach ($records as &$record)
			{
				$this->contact_data[$record['CONTACT_TYPE']] = $record['VALUE'];
			}
		}
		
		return $this->contact_data;
	}


	public function save($data)
	{
		foreach($data as $key => $value)
		{
			if(isset($this->data_array[$key]))
			{
				switch($key)
				{
					case 'GENDER':
						if($value == 'm' || $value == 'w')
							$this->data_array[$key] = $value;
						break;

					default:
						$this->data_array[$key] = $value;
						break;
				}
			}
		}

		$sql = "UPDATE spz_members SET 
					LASTNAME	= ".toSql($this->LASTNAME).",
					FIRSTNAME	= ".toSql($this->FIRSTNAME).",
					BIRTHNAME	= ".toSql($this->BIRTHNAME).",
					GENDER		= ".toSql($this->GENDER).",
					STREET		= ".toSql($this->STREET).",
					ZIP			= ".toSql($this->ZIP).",
					CITY		= ".toSql($this->CITY).",
					BIRTHDATE	= ".toSql($this->BIRTHDATE, 'date').",
					DEATHDATE	= ".toSql($this->DEATHDATE, 'date', null).",
					INSTRUMENT	= ".toSql($this->INSTRUMENT)."
				WHERE MEMBER_ID = ".((int) $this->member_id);
		$done = DB::query($sql);

		// Sollen Kontaktdaten geschrieben werden?
		if(isset($data['CONTACT']))
		{
			foreach($data['CONTACT'] as $type => $value)
			{
				$this->saveContactData($type, $value);
			}
		}

		if(isset($data['STATES']))
		{
			foreach($data['STATES'] as $state)
			{
				$this->saveMembershipState($state);
			}
		}

		// Update-TS setzen
		$this->data_array['UPDATE_TS'] = date('Y-m-d H:i:s');
		static::setLastUpdateTs();

		return true;
	}


	public function saveContactData($type, $value)
	{
		if(in_array($type, array('email', 'mobile', 'phone')))
		{
			$sql = "INSERT INTO spz_contact_informations SET 
						MEMBER_ID = ".toSql($this->member_id, dbInt).",
						CONTACT_TYPE = ".toSql($type).",
						VALUE = ".toSql($value)."
					 ON DUPLICATE KEY UPDATE 
						VALUE = ".toSql($value);
			$done = DB::query($sql);

			// Update-TS setzen
			$this->data_array['UPDATE_TS'] = date('Y-m-d H:i:s');
			static::setLastUpdateTs();

			return $done;
		}

		return false;
	}


	public function saveMembershipState($state)
	{
		if(!isset($state['MEMBERSHIP_ID']) || strpos($state['MEMBERSHIP_ID'], 'NEW') !== false)
		{
			// Neu anlegen
			$sql = "INSERT INTO spz_membership_states SET 
						MEMBER_ID = ".toSql($this->member_id, dbInt).",
						STATE = ".toSql($state['STATE']).",
						START_DATE = ".toSql($state['START_DATE']).",
						END_DATE = ".toSql($state['END_DATE'], dbText, null);
			$done = DB::query($sql);

			// Update-TS setzen
			$this->data_array['UPDATE_TS'] = date('Y-m-d H:i:s');
			static::setLastUpdateTs();

			return $done;
		}
		else
		{
			// aktualisieren
			$sql = "UPDATE spz_membership_states SET 
						STATE = ".toSql($state['STATE']).",
						START_DATE = ".toSql($state['START_DATE']).",
						END_DATE = ".toSql($state['END_DATE'], dbText, null)."
					WHERE MEMBERSHIP_ID = ".toSql($state['MEMBERSHIP_ID'], dbInt)."
					AND MEMBER_ID = ".toSql($this->member_id, dbInt);
			$done = DB::query($sql);

			// Update-TS setzen
			$this->data_array['UPDATE_TS'] = date('Y-m-d H:i:s');
			static::setLastUpdateTs();

			return $done;
		}
	}


	public function delete()
	{
		$sql = "DELETE FROM spz_members
				WHERE MEMBER_ID = ".toSql($this->member_id, dbInt);
		DB::query($sql);

		$sql = "DELETE FROM spz_contact_informations
				WHERE MEMBER_ID = ".toSql($this->member_id, dbInt);
		DB::query($sql);

		$sql = "DELETE FROM spz_membership_states
				WHERE MEMBER_ID = ".toSql($this->member_id, dbInt);
		DB::query($sql);

		$this->data_array = array();
		static::setLastUpdateTs();

		return true;
	}



	public static function setLastUpdateTs()
	{
		$date = date('Y-m-d H:i:s');
		$done = Cache::set('MemberLastUpdate', $date);
		return $done ? $date : false;
	}


	public static function getLastUpdateTs()
	{
		$cached = Cache::get('MemberLastUpdate');
		if($cached === false)
			return static::setLastUpdateTs();
		else
			return $cached;
	}
}