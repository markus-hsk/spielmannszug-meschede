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
	private $state_history = null;
	private $contact_data = null;
	


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
		$sql = 'SELECT * FROM spz_members';
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
		if(isset($this->data_array[$fieldname]))
			return $this->data_array[$fieldname];
		else
			return null;
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

		$data_array['AGE']		= $this->getAge();
		$data_array['STATES']	= $this->getMembershipStates();
		$data_array['AKTIV_JAHRE']	= rand(0,40);
		
		// Kontaktdaten anh�ngen
		$data_array['CONTACT'] = $this->getContactData();
		
		return $data_array;
	}

	public function getAge()
	{
		// Zusätzliche Daten
		if(!strlen($this->data_array['DEATHDATE']) || $this->data_array['DEATHDATE'] == '0000-00-00')
		{
			return getAge($this->data_array['BIRTHDATE']);
		}
		else
		{
			$deathdate = strtotime($this->data_array['DEATHDATE']);
			return getAge($this->data_array['BIRTHDATE'], $deathdate);
		}
	}

	public function getMembershipStates()
	{
		if($this->state_history === null)
		{
			// @todo Statushistorie abrufen
			$this->state_history = array();
		}
		
		return $this->state_history;
	}
	
	public function getContactData()
	{
		if($this->contact_data === null)
		{
			$this->contact_data = array();
			
			// Kontaktdaten abrufen
			$sql = "SELECT * 
					FROM spz_contact_informations
					WHERE MEMBER_ID = ".((int) $this->member_id);
			$records = DB::getCachedRecords( $sql );
			
			foreach ($records as &$record)
			{
				$this->contact_data[$record['CONTACT_TYPE']] = $record['VALUE'];
			}
		}
		
		return $this->contact_data;
	}
}