<?php
/**
 * Start the DevUtils App
 *
 * @package DevUtils
 */
$projectPath = dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR;
$found = false;
// Doorzoek alle hoger gelegen mappen naar een sledgehammer enabled project.

while (strlen($projectPath) > 4) {
	//  Extract vendor-dir from composer.json
	if (file_exists($projectPath.'composer.json')) {
		$composerJson = json_decode(file_get_contents($projectPath.'composer.json'), true);
		$vendorDir = (isset($composerJson['config']['vendor-dir'])) ? $composerJson['config']['vendor-dir'] : 'vendor';
	} else {
		$vendorDir = 'vendor';
	}
	if (file_exists($projectPath.$vendorDir.'/sledgehammer/core/bootstrap.php')) {
		if (realpath($projectPath) != realpath(__DIR__)) { // Is dit NIET de sledgehammer map binnen deze devutils?
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
		$tmpDir = __DIR__.'/tmp'.DIRECTORY_SEPARATOR;
		if ((is_dir($tmpDir) && is_writable($tmpDir)) == false) {  // Use the project heeft geen schrijfbare tmp folder?
			$tmpDir = '/tmp/sledgehammer-'.md5(__DIR__.DIRECTORY_SEPARATOR);
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
	$projectPath = './';
}

// Render static files vanuit de Devutils map
include_once(__DIR__.'/vendor/sledgehammer/core/render_public_folders.php');
require_once(__DIR__.'/vendor/autoload.php');

// Build website
$website = new Sledgehammer\DevUtilsWebsite($projectPath);
$website->handleRequest();
?>
