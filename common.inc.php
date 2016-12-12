<?php
// Ausgabe komprimieren - Deaktivieren wenn Apache mod_deflate verwendet wird
if(!defined('USE_GZHANDLER') || USE_GZHANDLER == false)
	ob_start();
else
	ob_start('ob_gzhandler');


// Wenn RelativePath nicht definiert wurde, funktioniert das Projekt nicht richtig.
if(!defined('RelativePath'))
	trigger_error('Notwendige PHP-Konstante "RelativePath" wurde nicht definiert.', E_USER_ERROR);


// Notwendige Includes
require_once(RelativePath.'/cfg/database.php');
require_once(RelativePath.'/lib/rb.php');
require_once(RelativePath.'/lib/Skin.class.php');
require_once(RelativePath.'/lib/utils.php');



// Datenbank initialisieren
R::setup('mysql:host='.DB_HOST.':'.DB_PORT.';dbname='.DB_DATABASE, DB_USER, DB_PASSWORD);

R::query()

// Wartungsmodus aktiv?
if(defined('MAINTENANCE_MODE') && MAINTENANCE_MODE == true)
{
	http_response_code(503); // Service Unavailable
	header('Content-Type: application/json');
	echo 'Datenbank befindet sich aktuell im Wartungsmodus.';
	exit(0);
}


// dynamische Konstanten aus Konfiguration ableiten


// Datei-Berechtigungen beim Schreiben automatisch beschrÃ¤nken?!
umask(000);


// richtige Zeitzone und Sprache des Systems setzen
ini_set('date.timezone', 'Europe/Berlin');
// @todo in MSM auskommentiert, sollten wir uns ansehen. Evtl sollten wir setLocale fÃ¼r bestimmte Dinge wie
// Stringvergleiche und Numerische Werte auf standard/ englisch setzen um Seiteneffekte zu vermeiden
// setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');


// Session starten
// @todo in MSM auskommentiert
// ini_set("session.cookie_lifetime", 3600*24*3);
session_start();


// Errorhandling
//error_reporting(ERROR_REPORTING);


// Errorhandler setzen
if(!defined('USE_ERROR_HANDLER') || USE_ERROR_HANDLER == true)
{
	//set_error_handler('mOErrorHandler');
}


// Zeichensatz
define('CHARSET', 'UTF-8');		// @TODO sollte in die Konfiguration ausgelagert werden
ini_set('default_charset', CHARSET);


// Wenn max_input_vars ausgeschÃ¶pft wird, dann Fehlermeldung erzeugen, da es sich sonst nur um ein Warning handelt
if(ini_get('max_input_vars') > 0 && count($_POST) == ini_get('max_input_vars'))
{
	trigger_error('Zulässige Anzahl an Formularparametern überschritten - max_input_vars: '.ini_get('max_input_vars'), E_USER_ERROR);
}


?>