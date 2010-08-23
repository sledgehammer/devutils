<?php
/**
 * Wordt aangeroepen vanuit de rewrite.php
 * 
 */

$projectPath = dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME']))).'/'; // public/devutils/rewrite.php = 3x dirname
$found = false;
// Doorzoek alle hoger gelegen mappen naar een sledgehammer enabled project.
while (strlen($projectPath) > 4) {
	if (file_exists($projectPath.'sledgehammer/core/init_framework.php')) {
		$found = true;
		// De map is gevonden 
		break;
	}
	$projectPath = dirname($projectPath).'/';
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
		include(dirname(dirname(__FILE__)).'/tmp/unittests/'.basename($filename));
		exit;
	}
} else {
	$projectPath = '.';
}

// Render static files vanuit de Devutils map
require_once(dirname(dirname(__FILE__)).'/sledgehammer/core/render_public_folders.php');

// Build website
require_once(dirname(dirname(__FILE__)).'/sledgehammer/core/init_framework.php');

$website = new DevUtilsWebsite($projectPath);
$website->render();
?>
