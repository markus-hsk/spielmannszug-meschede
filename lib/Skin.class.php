<?php
/**	Die Skin-Klasse verarbeitet alle Informationen für die Ausgabe/ Verarbeitung eines
 * 	Skins (HTML-Struktur). Hierbei lassen sich enthaltene Variablen in Form
 * 	von {VARIABLENNAME} durch neue Werte ersetzen.
 *
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 17.02.2016
 * Time: 13:30
 */

// HTML-Objecte Autoloading
spl_autoload_register(function($class)
{
	if(in_array($class, ['HTML_Select', 'HTML_Input', 'HTML_Object']))
    	require_once(__DIR__.'/html_objects/html_object.class.php');
});


// Klasse
class Skin
{
	// Klassenvariablen
	var $skin_data;				// Speichert den vollen Inhalt eines Skins

	var $skin_var_replace;		// Speichert in einem assoziativen Array
								// alle Variablennamen sowie deren Wert

	var $skin_loop_replace;		// Speichert in einem assoziativen Array
								// alle Variablennamen sowie ein untergeordnetes
								// Array mit den enthaltenen Daten für den Masseneinsatz

	// Konstanten
	const REPLACE = 0;    // Wert um alte Daten mit neuen Daten zu überschreiben
	const ATTACH  = 1;    // Wert um neue Daten an alte Daten anzuhängen
	const PREPEND = 2;    // Wert um neue Daten vor alten Daten voranzustellen


	// Konstruktor
	/**	Konstruktor der Skin-Klasse. $skin_filename ist optional und wird nur benötigt, wenn sich der Name der HTML-Datei vom Dateiname der aufrufenden Datei unterscheidet!
	 * 
	 *	@param	String		$skin_filepath		Name der HTML-Datei, wenn dieser nicht gleich der aufrufenden Datei ist
	 *	@return Skin Object
	 *	@access	public
	 *	@since	24.03.2011 - MBU
	 */
	function __construct($skin_filepath = '')
	{
		$this->skin_data 			= '';
		$this->skin_var_replace 	= array();
		$this->skin_loop_replace	= array();
		
		// Skin laden
		if($skin_filepath != '')
		{
			$this->loadSkinFile($skin_filepath);
		}
	}
	
	
	function __toString()
	{
		return $this->getSkin();
	}


	function __set($key, $value)
	{
		$this->setSkinVar($key, $value, self::REPLACE);
	}


	
	// Methoden
	/**	Setzt den HTML-Inhalt des Skin auf den übergebenen String $data. $method gibt an, wie mit
	 * 	dem übergebenen Inhalt gearbeitet werden soll. Per Default wird der Wert ersetzt (REPLACE).
	 * 	Zur Auswahl stehen auch ATTACH (anhängen) und PREPEND(voranstellen) an den potenziell bereits
	 * 	gesetzten Inhalt.
	 *
	 * 	@param	String		$data		HTML-Konstrukt
	 * 	@param	int			$method		Verarbeitungsmethodik (default: REPLACE)
	 * 	@return	void
	 * 	@access	public
	 * 	@since	24.03.2011 - MBU
	 */
	function setSkinData($data, $method = self::REPLACE)
	{
		switch($method)
		{
			case self::ATTACH:
				$this->skin_data .= $data;
				break;
			case self::PREPEND:
				$this->skin_data = $data . $this->skin_data;
				break;
			case self::REPLACE:
			default:
				$this->skin_data = $data;
				break;
		}
	}

	/**	Lädt den Inhalt einer Datei und setzt darauf den Skin fest
	 *
	 * 	@param	String		$filepath		Dateiname der zu ladenden Datei im SKINPATH-Verzeichniss oder Pfad zur Datei
	 * 	@return	void
	 * 	@access	public
	 * 	@since	24.03.2011 - MBU
	 */
	function loadSkinFile($filepath)
	{
		//if($filepath[0] != '.' && $filepath[0] != '/')
		//	$filepath = SKINPATH . $filepath;
		
		if(!file_exists($filepath))
			return false;

		$skin_data = file_get_contents($filepath);
		$this->setSkinData($skin_data);
		
		// Alle Variablen mit gleichem GET-Parameter austauschen
		if(is_array($_GET))
		{
			foreach($_GET as $key => $value)
			{
				$this->setSkinVar($key, $value);
			}
		}
	}

	/**	Legt fest, welchen Wert $value eine eingebettete Variable $varname aus dem Skin bei der
	 * 	Verarbeitung erhalten soll. $method gibt an, wie der neue Wert verarbeitet werden soll.
	 * 	M?glich sind REPLACE (ersetzt potentiell bereits gesetzte Werte), ATTACH (h?ngt den neuen
	 * 	Wert an den potenziell bereits gesetzten Wert an) und PREPEND (f?gt den neuen Wert vor die
	 * 	potenziell bereits gesetzen Werte ein).
	 *
	 * 	@param	String		$varname			Name der eingebetteten Variable (nicht case-sensitive)
	 * 	@param	MIXED		$value				Ein String, der den Wert angibt oder ein Array f?r einen Loop-Wert
	 * 	@param	int			$method				Verarbeitungsmethodik (default: REPLACE)
	 * 	@return	boolean		Erfolgreich aufgenommen
	 * 	@access	public
	 * 	@since	24.03.2011 - MBU
	 */
	function setSkinVar($varname, $value, $method = self::REPLACE)
	{
		$varname = strtoupper($varname);
		if(preg_match('/^[A-Z0-9_]+$/', $varname))
    	{
			if(is_array($value))
			{
				switch($method)
				{
					case self::ATTACH:
						$this->skin_loop_replace[$varname] = array_merge($this->skin_loop_replace[$varname], $value);
						break;
					case self::PREPEND:
						$this->skin_loop_replace[$varname] = array_merge($value, $this->skin_loop_replace[$varname]);
						break;
					case self::REPLACE:
					default:
						$this->skin_loop_replace[$varname] = $value;
						break;
				}
				return true;
			}
			else
			{
				switch($method)
				{
					case self::ATTACH:
						$this->skin_var_replace[$varname] .= (string) $value;
						break;
					case self::PREPEND:
						$this->skin_var_replace[$varname] = (string) $value . $this->skin_var_replace[$varname];
						break;
					case self::REPLACE:
					default:
						$this->skin_var_replace[$varname] = (string) $value;
						break;
				}
				return true;
			}
    	}
    	else
    		return false;
	}
	
	
	/**	Alias f?r Skin::setSkinVar(), da CodeCharge eine Funktion mit gleicher Aufgabe nutzt die setVar() hei?t 
	 * 
	 * 	@param	String		$varname 		Name der eingebetteten Variable (nicht case-sensitive)
	 * 	@param	String		$value			Ein String, der den Wert angibt
	 * 	@return	boolean ausgef?hrt
	 * 	@access	public
	 * 	@since	24.03.2011 - MBU
	 * 	@see	Skin::setSkinVar()
	 */
	function setVar($varname, $value)
	{
		return $this->setSkinVar($varname, $value, self::REPLACE);
	}

	/**	Liefert den Skin wieder. Der optionale Parameter $process_runtimes gibt an, wie h?ufig eine Parsung
	 *	durchgef?hrt werden soll. Per Default wird nur einmal die Skin-Daten geparst und Variablen ersetzt.
	 *	Durch Erh?hung der Durchlaufvariable k?nnen auch Variablen, die als Werte mit ?bergeben wurden
	 *	ersetzt werden. Wird die Durchlaufvariable auf kleiner 1 gesetzt, erh?lt man den Skin ungeparst wieder.
	 *	Mit dem Parameter $clean_up kann man dar?ber hinaus auch noch steuern, ob ?brig bleibende Variablen
	 *	entfernd werden sollen vor der R?ckgabe. Per Default wird dieses durchgef?hrt.
	 *
	 *	@param	bool		$clean_up				Gibt an, ob nicht ersetzte Variablen gel?scht werden sollen (default: true)
	 *	@param	int			$process_runtimes		Durchl?ufe des Parsers (default: 1)
	 *	@return	String		HTML-Ausgabe
	 *	@access	public
	 *	@since	24.03.2011 - MBU
	 */
	function getSkin($clean_up = true, $process_runtimes = 1)
	{
		if(!is_int($process_runtimes))
			return false;

		$return_data = $this->skin_data;
		$runtimes = 0;
		while($runtimes < $process_runtimes)
		{
			$return_data = $this->parse($return_data);
			$runtimes++;
		}

		if($clean_up)
		{
			$return_data = preg_replace('/\{([a-z0-9_]+)\}/i','',$return_data);						// Entfernd normale Variablen
			$return_data = preg_replace('/\{S:([a-z0-9_]+)\}(.+)\{E:\\1\}/is','',$return_data);		// Entfernd Loop-Variablen
		}

		return $return_data;
	}


	
	
	
	// private Methoden
	/**	Parst den ?bergebenen Skin auf Grundlage der gesetzten Variablenwerte.
	 *
	 * 	@param	String		$skin_data			Den zu parsenden String
	 * 	@return	String 		geparster String
	 * 	@access	private
	 * 	@since	24.03.2011 - MBU
	 */
	function parse($skin_data)
	{
		$skin_data = preg_replace_callback('/\{S:([a-z0-9_]+)\}(.+)\{E:\\1\}/is', array($this, "getLoopReplace"), $skin_data);
		$skin_data = preg_replace_callback('/\{([a-z0-9_]+)\}/i', array($this, "getVarReplace"), $skin_data);

		return $skin_data;
	}


	/**	Liefert den Replace-Wert einer Variable zur?ck
	 *
	 * 	@param	Array			$preg_match 			Das Match-Array des preg-Vergleichs
	 * 	@return	String			Wert
	 * 	@access	private
	 * 	@since	24.03.2011 - MBU
	 */
	function getVarReplace($preg_match)
	{
		if(isset($this->skin_var_replace[strtoupper($preg_match[1])]))
			return $this->skin_var_replace[strtoupper($preg_match[1])];
		else
			return $preg_match[0];
	}

	/**	Liefert den Replace-Wert f?r die Loop-Variable zur?ck
	 *
	 * 	@param	Array			$preg_match				Das Match-Array des preg-Vergleichs
	 * 	@return	String			Wert
	 * 	@access	private
	 * 	@since	24.03.2011 - MBU
	 */
	function getLoopReplace($preg_match)
	{
		if(isset($this->skin_loop_replace[$preg_match[1]]))
		{
			$return = '';
			$row = 0;
			foreach($this->skin_loop_replace[$preg_match[1]] as $id => $data)
			{
				$clsSkinClass = new Skin();
				$clsSkinClass->setSkinData($preg_match[2]);
				$clsSkinClass->skin_var_replace = $this->skin_var_replace;
				if($row % 2)
				{
					$clsSkinClass->setSkinVar('LINE_CLASS', 'odd');
				}
				else
				{
					$clsSkinClass->setSkinVar('LINE_CLASS', 'even');
				}
				$clsSkinClass->setSkinVar('LINE_NO', $row);
				foreach($data as $tag => $value)
				{
					$clsSkinClass->setSkinVar($tag, $value);
				}
				$return .= $clsSkinClass->getSkin();
				$row++;
			}
			return $return;
		}
		return $preg_match[0];
	}


	public static function createSelectBox(array $data, $opts = null)
	{
		// Pflichtvariablen setzen
		$select_name = '';

		if(is_array($opts) && count($opts) > 0)
		{
			$select_name = isset($opts['NAME']) ? $opts['NAME'] : '';
			$selected    = isset($opts['SELECTED']) ? $opts['SELECTED'] : '';
			$id          = isset($opts['ID']) ? $opts['ID'] : '';
			$multiple    = isset($opts['MULTIPLE']) ? (bool)$opts['MULTIPLE'] : false;
			$class       = isset($opts['CLASS']) ? $opts['CLASS'] : '';
			$css         = isset($opts['CSS']) ? $opts['CSS'] : null;
		}

		// Selectbox erstellen
		$SelectBox = new HTML_Select($select_name);

		if(count($data) > 0)
		{
			foreach($data as $key => $text)
			{
				if(is_array($text))
				{
					$group_data = $text;
					$OptGroup = $SelectBox->addOptionGroup($key);

					foreach($group_data as $key => $text)
					{
						$OptGroup->addOption($key, $text, ($selected == $key));
					}
				}
				else
				{
					$SelectBox->addOption($key, $text, ($selected == $key));
				}
			}
		}


		// Weitere Anpassungen durchf?hren
		if(strlen($id))
			$SelectBox->setId($id);

		$SelectBox->setMultiple($multiple);

		if(strlen($class))
			$SelectBox->setClass($class);

		if(is_array($css))
		{
			foreach($css as $param => $value)
			{
				$SelectBox->addCSS($param, $value);
			}
		}

		return $SelectBox;
	}
}
?>