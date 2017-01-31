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

			foreach($states as $state)
			{
				if($state['END_DATE'] == null)
				{
					// Es handelt sich um einen aktuellen Status
					$this->current_state = $state;
					break;
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
			$this->contact_data = array();
			
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
}