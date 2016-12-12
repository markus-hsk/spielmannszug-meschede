<?php

//	Falls diese Klasse includiert wird, muss die übergeordnete HTML_Object-Klasse inkludiert werden. Planmäßig sollte die Klasse includiert werden, da diese alle weiteren mitnimmt.
require_once('html_object.class.php');


/**	Erstellt ein HTML-Objekt vom Typ input.
 * 
 * 	@author Markus Buscher
 * 	@since	19.08.2011
 */ 
class HTML_Input extends HTML_Object
{	
	
	/**	Konstruktor
	 * 
	 * 	@param	String		$name		Name des Inputfeldes
	 * 	@param	String		$type		Typ des Feldes
	 * 	@return	HTML_Input-Objekt
	 * 	@access	public
	 * 	@since	19.08.2011 - MBU
	 */
	function HTML_Input($name, $type = '')
	{
		parent::HTML_Object('input');
		
		$this->setName($name);
		$this->setType($type);
		$this->setValue('');
		
		$this->_setChildsAllowed(false);
		$this->_setTextAllowed(false);
	}

	/** Setzt den Name des Inputs
	 * 	
	 * 	@param	String		$name		Name
	 * 	@return	void
	 * 	@access	public
	 * 	@since	19.08.2011 - MBU
	 */
	function setName($name)
	{
		if(strlen($name))
			$this->addAttribute('name', $name);
		else
			$this->deleteAttribute('name');
	}

	
	/**	Setzt die ID des Inputs
	 * 	
	 * 	@param	String		$id			ID
	 * 	@return	void
	 * 	@access	public
	 * 	@since	19.08.2011 - MBU
	 */
	function setId($id)
	{
		$this->addAttribute('id', $id);
	}
	
	
	/**	Setzt den Inputtyp des Inputs
	 * 	
	 * 	@param	String		$type		Typ
	 * 	@return	void
	 * 	@access	public
	 * 	@since	19.08.2011 - MBU
	 */
	function setType($type = '')
	{
		if(strlen($type))
			$this->addAttribute('type', strtolower($type));
		else
			$this->deleteAttribute('type');
	}
	
	
	/**	Setzt den Wert des Inputs
	 * 	
	 * 	@param	String		$value		Wert
	 * 	@return	void
	 * 	@access	public
	 * 	@since	19.08.2011 - MBU
	 */
	function setValue($value = '')
	{
		$this->addAttribute('value', $value);
	}
	
	
	/** Legt fest, ob der Browser die autovervollständigen Funktion benutzen darf
	 * 	
	 * 	@param	boolean 		$autocomplete		aktiv ja|nein
	 * 	@return	void
	 * 	@access	public
	 * 	@since	14.08.2013 - MBU
	 */
	function setAutocomplete($autocomplete = true)
	{
		$this->addAttribute('autocomplete', $autocomplete ? 'on' : 'off');
	}
}