<?php
/**
 * Detecteer de DevUtils projectmap en de sledgehammer enabled "parent" website
 *
 * @package DevUtils
 */

define('MICROTIME_START', microtime(true));
// Detecteer de locatie van de devutils bestanden.
$locations = array(
	'/var/www/devutils/',
	dirname(dirname(dirname(dirname(__FILE__)))).'/devutils/', // Zelfde niveau als het project
	dirname(dirname(__FILE__)).'/', // de devutils map staat helemaal in de public map.
);
$devutilsPath = false;
foreach ($locations as $path) {
	if (file_exists($path.'application/classes/DevUtilsWebsite.php')) {
		$devutilsPath = $path;
		break;
	}
}
if ($devutilsPath == false) {
	error_log('DevUtils project not found in "'.implode('", "', $locations).'"');
	die('DevUtils not found');
}
require($devutilsPath.'application/render.php');
?>
