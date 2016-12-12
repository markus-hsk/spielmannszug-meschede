<?php

//	Falls diese Klasse includiert wird, muss die übergeordnete HTML_Object-Klasse inkludiert werden. Planmäßig sollte die Klasse includiert werden, da diese alle weiteren mitnimmt.
require_once('html_object.class.php');


/**	Erstellt eine Auswahlliste.
 * 
 * 	@author Markus Buscher
 * 	@since	19.08.2011
 */ 
class HTML_Select extends HTML_Object
{	
	var $multiple;		// Gibt an, ob diese Selectbox mehrere Auswahlen erlaubt
	var $selected;		// Speichert den Wert, der ausgewählt ist
	
	/**	Konstruktor
	 * 
	 * 	@param	String		$name		Name des Inputfeldes
	 * 	@param	String		$type		Typ des Feldes
	 * 	@return	HTML_Input-Objekt
	 * 	@access	public
	 * 	@since	19.08.2011 - MBU
	 */
	function HTML_Select($name)
	{
		parent::HTML_Object('select');

		$this->setName($name);

		$this->_setTextAllowed(false);
		
		$this->setMultiple(false);
	}


	function setName($name)
	{
		$this->addAttribute('name', $name);
	}


	function setId($id)
	{
		$this->addAttribute('id', $id);
	}


	function addChild($html_object)
	{
		if(is_a($html_object, 'HTML_Option') || is_a($html_object, 'HTML_Optgroup'))
			parent::addChild($html_object);
		else
			trigger_error('A selectbox is not allowed to append childs expect HTML_Option and HTML_Optgroup', E_USER_WARNING);
	}

	
	/**	Erstellt innerhalb der Selectbox einen neue Optionengruppe. Über das zurückgegebene Objekt können zu dieser
	 * 	Gruppe weitere Optionen zugefügt werden
	 * 	
	 * 	@param	String			$label		Der Text, der als Gruppenbezeichnung angezeigt werden soll
	 * 	@return	HTML_Optgroup	Objekt
	 * 	@access	public
	 * 	@since	23.08.2011 - MBU
	 */
	function addOptionGroup($label)
	{
		$OptionGroup = new HTML_Optgroup($label) ;
		
		parent::addChild($OptionGroup);
		
		return $OptionGroup;
	}
	

	function addOption($value, $text, $selected = false)
	{
	//	$Option = new HTML_Object('option');
	//	$Option->addAttribute('value', $value);
	//	$Option->addText($text);

	//	if($selected == true || $value == $this->selected)
	//	{
	//		$Option->addAttribute('selected', NULL);
	//	}

		// Parameter überschreiben
		if($value === $this->selected)
		 	$selected = true;

		$Option = new HTML_Option($value, $text, $selected);

		parent::addChild($Option);
		
		return $Option;
	}


	/**	Setzt den ausgewählten Wert
	 * 	
	 * 	@param	MIXED		$value			Wert, der ausgewählt werden soll
	 * 	@return	void
	 * 	@access	public
	 * 	@since	24.08.2011 - MBU
	 */
	function setSelected($value)
	{
		if(is_array($value))
		{
			foreach($value as $single_value)
			{
				$this->setSelected($single_value);			// Rekursion!
			}
		}
		else
		{
			foreach($this->childs as $Object)
			{
				if(is_a($Object, 'HTML_Optgroup'))
				{
					$Object->setSelected($value);
				}
				else
				{
					if($Object->getAttribute('value') == $value)
					{
						$Object->addAttribute('selected', NULL);
						
						if(!$this->multiple)
							$this->selected = $value;
						else
							$this->selected[] = $value;
					}
					else
					{
						if(!$this->multiple || !strlen($value))
							$Object->deleteAttribute('selected');
					}
				}
			}
		}
	}
	
	
	/**	Legt fest, ob es sich bei der Selektbox um eine Multiauswahl handelt
	 * 	
	 * 	@param	boolean		$multiple		Multiauswahl ja|nein
	 * 	@return	void
	 * 	@access	public
	 * 	@since	24.08.2011 - MBU
	 */
	function setMultiple($multiple)
	{
		$this->multiple = $multiple ? true : false;
		
		if($this->multiple)
			$this->addAttribute('multiple', 'multiple');
		else
			$this->deleteAttribute('multiple');
		
		// Es darf jetzt nur noch der erste Eintrag selekiert sein
		if(!$this->multiple)
		{
			if(is_array($this->selected) && count($this->selected) > 1)
			{
				$this->setSelected($this->selected[0]);
			}
		}
	}
}


/**	Klasse zum anlegen einer Option
 * 
 * 	@since	18.12.2012
 * 	@author Markus Buscher
 *
 */
class HTML_Option extends HTML_Object
{
	function HTML_Option($value, $text, $selected = false)
	{
		parent::HTML_Object('option');
		
		$this->addAttribute('value', $value);
		parent::addText($text);
		$this->setSelected($selected);
	}
	
	function addChild($html_object)
	{
		if(is_string($html_object))
			parent::addChild($html_object);
		else
			trigger_error('An option is not allowed to append childs', E_USER_WARNING);
	}
	
	function setSelected($selected)
	{
		if($this->getAttribute('selected') === false && $selected == true)
			$this->addAttribute('selected', NULL);
		else if($this->getAttribute('selected') !== false && $selected != true)
			$this->deleteAttribute('selected');
	}
}


/**	Klasse zum anlegen einer Optionengruppe
 * 
 * 	@since	23.08.2011
 * 	@author Markus Buscher
 *
 */
class HTML_Optgroup extends HTML_Object
{
	function HTML_Optgroup($label)
	{
		parent::HTML_Object('optgroup');

		$this->addAttribute('label', $label);

		$this->_setTextAllowed(false);
	}

	
	function addChild($html_object)
	{
		if(is_string($html_object) || is_a($html_object, 'HTML_Option'))
			parent::addChild($html_object);
		else
			trigger_error('An optgroup is not allowed to append childs expect HTML_Option', E_WARNING);
	}
	
	
	function addOption($value, $text, $selected = false)
	{
		$Option = new HTML_Option($value, $text, $selected);

	//	$Option = new HTML_Object('option');
	//	$Option->addAttribute('value', $value);
	//	$Option->addText($text);

	//	if($selected == true || $value == $this->selected)
	//	{
	//		$Option->addAttribute('selected', NULL);
	//	}

		parent::addChild($Option);
		
		return $Option;
	}
	

	function setSelected($value)
	{
		foreach($this->childs as $Object)
		{
			if($Object->getAttribute('value') == $value)
				$Object->addAttribute('selected', NULL);
			else
				$Object->deleteAttribute('selected');
		}
	}
}
?>