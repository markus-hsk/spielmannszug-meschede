<?php
// Wenn RelativePath nicht definiert wurde, funktioniert das Projekt nicht richtig.
if(!defined('RelativePath'))
{
	trigger_error('Notwendige PHP-Konstante "RelativePath" wurde nicht definiert.', E_USER_ERROR);
	exit;
}

// Prüfen ob Konfiguration erstellt wurde
if(!file_exists(RelativePath . '/cfg/config.inc.php'))
{
	trigger_error('Die notwendige Konfigurationsdatei /cfg/config.inc.php wurde noch nicht angelegt.', E_USER_ERROR);
	exit;
}

// Notwendige Initial-Includes
require_once(RelativePath.'/cfg/config.inc.php');
require_once(RelativePath.'/lib/utils.php');


// Ausgabe komprimieren - Deaktivieren wenn Apache mod_deflate verwendet wird
if(!defined('USE_GZHANDLER') || USE_GZHANDLER == false)
	ob_start();
else
	ob_start('ob_gzhandler');


// Wartungsmodus aktiv?
if(defined('MAINTENANCE_MODE') && MAINTENANCE_MODE == true)
{
	http_response_code(503); // Service Unavailable
	header('Content-Type: application/json');

	echo json_encode(array(
		'success' => false,
		'rows' => false,
		'total' => 0,
		'error' => array(
			'error_message' => 'Diese Seite befindet sich aktuell im Wartungsmodus. Bitte versuchen Sie es später erneut',
			'error_code' => -1040
		)
	));
	exit(0);
}


// dynamische Konstanten aus Konfiguration ableiten


// Datei-Berechtigungen beim Schreiben automatisch beschränken?!
umask(000);


// richtige Zeitzone und Sprache des Systems setzen
date_default_timezone_set('UTC');


// Stringvergleiche und Numerische Werte auf standard/ englisch setzen um Seiteneffekte zu vermeiden
setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');


// Session starten
if(defined('SESSION_LIVETIME'))
	ini_set("session.cookie_lifetime", SESSION_LIVETIME);
session_start();


// Errorhandling
if(defined('ERROR_REPORTING'))
	error_reporting(ERROR_REPORTING);


// Errorhandler setzen
if(defined('USE_ERROR_HANDLER') && USE_ERROR_HANDLER == true)
{
	// @todo
	//require_once(RelativePath . '/scripts/includes/error_handler.inc.php');
	//set_error_handler('');
}


// Zeichensatz
define('CHARSET', 'UTF-8');
ini_set('default_charset', CHARSET);


// Wenn max_input_vars ausgeschöpft wird, dann Fehlermeldung erzeugen, da es sich sonst nur um ein Warning handelt
$max_input_vars = ini_get('max_input_vars');
if($max_input_vars > 0 && count($_POST) >= $max_input_vars)
{
	trigger_error('Zulässige Anzahl an Formularparametern überschritten - max_input_vars: '.$max_input_vars, E_USER_ERROR);
}

// Versionsnummer
define('VERSION', '0.0.1');


// Weitere Includes tätigen
require_once(RelativePath.'/lib/DB.class.php');
require_once(RelativePath.'/lib/Cache.class.php');
require_once(RelativePath.'/lib/Member.class.php');


// Datenbank und andere Objekte initialisieren
DB::init();

if(defined('USE_CACHE') && USE_CACHE)
	Cache::enable();
else
	Cache::disable();


// Zugriffsberechtigung erfragen
if(file_exists(__DIR__.'/auth.inc.php'))
{
	$allowed = include 'auth.inc.php';
	if (!$allowed)
	{
		http_response_code(401); // Authentication required
		header('Content-Type: application/json');
	
		echo json_encode(array(
				'success' => false,
				'rows' => false,
				'total' => 0,
				'error' => array(
						'error_message' => 'Authentication required',
						'error_code' => -1050
				)
		));
		exit(0);
	}
}


?>