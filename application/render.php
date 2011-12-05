<?php
/**
 * Wordt aangeroepen vanuit de rewrite.php
 *
 */
$projectPath = dirname($_SERVER['SCRIPT_FILENAME']);
$found = false;
// Doorzoek alle hoger gelegen mappen naar een sledgehammer enabled project.
while (strlen($projectPath) > 4) {
	if (file_exists($projectPath.'sledgehammer/core/init_framework.php')) {
		if (realpath($projectPath) != realpath(dirname(dirname(__FILE__)))) { // Is dit NIET de sledgehammer map binnen deze devutils?
			$found = true; // De map is gevonden
			break;
		}
	}
	$projectPath = dirname($projectPath).DIRECTORY_SEPARATOR;
}

if ($found) {
	// Controleer of er een UnitTest gestart moet worden?
	$webpath = dirname($_SERVER['SCRIPT_NAME']);
	if ($webpath != '/') {
		$webpath .= '/';
	}
	$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Het path gedeelte van de uri
	$filename = substr($uriPath, strlen($webpath)); // Bestandsnaam is het gedeelte van de uriPath zonder de WEBPATH
	if (substr($filename, 0, 10) == 'run_tests/') {
		// Include het gegenereerde unitest bestand.
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
		$tmpDir = dirname(dirname(__FILE__)).'/tmp'.DIRECTORY_SEPARATOR;
		if ((is_dir($tmpDir) && is_writable($tmpDir)) == false) {  // Use the project heeft geen schrijfbare tmp folder?
			$tmpDir = '/tmp/sledgehammer-'.md5(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
			if (function_exists('posix_getpwuid')) {
				$user = posix_getpwuid(posix_geteuid());
				$tmpDir .= '-'.$user['name'];
			}
			$tmpDir .= '/';
		}
		include($tmpDir.'UnitTests/'.basename($filename));
		exit;
	}
} else {
	$projectPath = '.';
}

// Render static files vanuit de Devutils map
require_once(dirname(dirname(__FILE__)).'/sledgehammer/core/render_public_folders.php');

// Build website
require_once(dirname(dirname(__FILE__)).'/sledgehammer/core/init_framework.php');


//dump($AutoLoader->inspectFile(PATH.'application/classes/DevUtilsWebsite.php'));
//dump($AutoLoader->getInfo('SledgeHammer\DevUtilsWebsite'));
$website = new SledgeHammer\DevUtilsWebsite($projectPath);
$website->handleRequest();
?>
