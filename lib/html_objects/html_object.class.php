<?php

// Abhängige Klassen werden inkludiert
include_once('html_input.class.php');
include_once('html_select.class.php');


class HTML_Object
{
	var $tagname;
	var $attributes;
	var $css_params;
	var $childs;

	var $self_closeable;
	var $childs_allowed;
	var $text_allowed;


	function HTML_Object($tagname)
	{
		$this->tagname = strtolower($tagname);

		$this->attributes = array();
		$this->css_params = array();
		$this->childs     = array();

		$this->self_closeable = true;
		
		$this->_setChildsAllowed(true);
		$this->_setTextAllowed(true);
	}
	
	
	/**	Magische Methode, die aufgerufen wird wenn das Objekt als String ausgegeben werden soll
	 * 	
	 * 	@return	String HTML-Code
	 * 	@see	HTML_Object->getHTML()
	 * 	@access	public
	 * 	@since	19.08.2011 - MBU
	 */
	function __toString()
	{
		return $this->getHTML();
	}
	
	
	/**	Setzt die CSS-Klasse auf exakt den übergebenen Wert. Wünscht man mehrere CSS-Klassen muss it addClass gearbeitet werden!
	 * 	
	 * 	@param	String		$classname		CSS-Klassenname
	 * 	@return	void
	 * 	@access	public
	 * 	@since	19.08.2011 - MBU
	 */
	function setClass($classname = '')
	{
		if(strlen($classname))
			$this->addAttribute('class', $classname);
		else
			$this->deleteAttribute('class');
	}
	
	
	/**	Erweitert die CSS-Klassen um eine weitere Klasse im stil von class="class1 class2 class3". Wünscht man die Klasse explizit zu setzten so muss man setClass verwenden
	 * 	
	 * 	@param	String		$classname		CSS-Klassenname
	 * 	@return	void
	 * 	@access	public
	 * 	@since	19.08.2011 - MBU
	 */  
	function addClass($classname)
	{
		if(strlen($classname))
		{
			if(strlen($this->getAttribute('class')))
				$this->setClass($this->getAttribute('class').' '.$classname);
			else
				$this->setClass($classname);
		}	
	}


	/**	Alias für HTML_Object->addAttribute()
	 * 	
	 * 	@param	String		$attribute_name			Name des Attributes
	 * 	@param	String		$value					Wert des Attributes
	 * 	@return	void
	 * 	@see	HTML_Object->addAttribute()
	 * 	@access	public
	 * 	@since	19.08.2011 - MBU
	 */
	function setAttribute($attribute_name, $value = '')
	{
		$this->addAttribute($attribute_name, $value);
	}
	
	function addAttribute($attribute_name, $value = '')
	{
		$this->attributes[strtolower($attribute_name)] = $value;
	}


	function addCSS($css_param, $value = '')
	{
		$this->css_params[strtolower($css_param)] = $value;
	}
	
	
	function _setChildsAllowed($allowed = true)
	{
		$this->childs_allowed = $allowed ? true : false;
	}
	
	function _setTextAllowed($allowed = true)
	{
		$this->text_allowed = $allowed ? true : false;
	}


	function addChild($html_object)
	{
		if(is_string($html_object))
		{
			if($this->text_allowed)
				$this->childs[] = $html_object;
			else
				trigger_error('Object '.$this->tagname.' is not allowed to have a text', E_USER_WARNING);
		}
		else if(is_a($html_object, 'HTML_Object'))
		{
			if($this->childs_allowed)
				$this->childs[] = $html_object;
			else
				trigger_error('Object '.$this->tagname.' is not allowed to append childs', E_USER_WARNING);
		}
		else
		{
			trigger_error('given object is not a HTML_Object', E_USER_WARNING);
		}
	}


	function addText($text)
	{
		$this->addChild($text);
	}


	function getHTML()
	{
		$css_style = '';
		if(count($this->css_params) > 0)
		{
			foreach($this->css_params as $param => $value)
			{
				$css_style .= $param.':'.$value.';';
			}
			$this->addAttribute('style', $css_style);
		}

		$attributes_string = '';
		if(count($this->attributes) > 0)
		{
			foreach($this->attributes as $attribute_name => $value)
			{
				if($value !== NULL)
					$attributes_string .= ' '.$attribute_name.'="'.$value.'"';
				else
					$attributes_string .= ' '.$attribute_name;
			}
		}

		$html_string = '<'.$this->tagname.$attributes_string;

		if(count($this->childs) > 0)
		{
			$html_string .= '>';

			foreach($this->childs as $child)
			{
				if(is_string($child))
				{
					$html_string .= $child;
				}
				else if(is_a($child, 'HTML_Object'))
				{
					$html_string .= $child->getHTML();
				}
			}

			$html_string .= '</'.$this->tagname.'>';
		}
		else
		{
			if($this->self_closeable)
			{
				$html_string .= '/>';
			}
			else
			{
				$html_string .= '></'.$this->tagname.'>';
			}
		}

		return $html_string;
	}


	function getAttribute($attribute_name)
	{
		if(isset($this->attributes[strtolower($attribute_name)]))
			return $this->attributes[strtolower($attribute_name)];
		else
			return false;
	}


	function deleteAttribute($attribute_name)
	{
		if(array_key_exists(strtolower($attribute_name), $this->attributes))
			unset($this->attributes[strtolower($attribute_name)]);
	}
}