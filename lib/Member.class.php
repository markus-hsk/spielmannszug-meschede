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


	// ##### Magische Methoden #########################################################################################

	private function __construct($member_id)
	{
		$this->member_id = (int) $member_id;
	}

	private function __clone() {}


	// ##### Instanziierungsfunktionen #################################################################################

	/**
	 * Liefert ein Array von Mitgliederinstanzen wieder
	 *
	 * @param	array		$filters		Filterregeln
	 * @return	Member[]
	 * @since	28.12.2016
	 */
	public static function find(array $filters)
	{
		$sql = 'SELECT * FROM spz_members';
		$records = DB::getRecords( $sql );

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
		$data_array['NSTRUMENT']	= rand(0,40);

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

	}
}