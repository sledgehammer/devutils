<?php
/**
 * Detect and include the DevUtils installation.
 */
define('Sledgehammer\STARTED', microtime(true));
// Detecteer de locatie van de devutils bestanden.
$locations = array(
	dirname(__DIR__).'/', // Is installed in full
	dirname(dirname(__DIR__)).'/devutils/', // In $project/
	dirname(dirname(dirname(__DIR__))).'/devutils/', // In a  $project/public
	dirname(dirname(dirname(dirname(__DIR__)))).'/devutils/', // in a $project/app/webroot
	'/var/www/devutils/',
);
$devutilsPath = false;
foreach ($locations as $path) {
	if (file_exists($path.'devutils.php')) {
		$devutilsPath = $path;
		break;
	}
}
if ($devutilsPath == false) {
	error_log('DevUtils not found in "'.implode('", "', $locations).'"', E_USER_WARNING);
	die('Error: DevUtils installation not found.');
}
// Define the bootstrap files to initialize, by default uses "vendor/autoload.php".
$devutilsIncludes = [
    __DIR__.'/../../vendor/autoload.php'
];
require($devutilsPath.'devutils.php');