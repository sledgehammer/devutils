<?php

use Sledgehammer\Core\Debug\ErrorHandler;
use Sledgehammer\Core\Debug\Autoloader;
use Sledgehammer\Devutils\DevUtilsWebsite;
use Sledgehammer\Mvc\Template;

define('Sledgehammer\DEVUTILS_PATH', __DIR__.'/');
/**
 * Start the DevUtils App
 *
 * @package DevUtils
 */
$projectPath = dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR;
$found = false;
// Doorzoek alle hoger gelegen mappen naar een sledgehammer enabled project.

while (strlen($projectPath) > 4) {
    //  Extract vendor-dir from composer.json
    if (file_exists($projectPath . 'composer.json')) {
        $composerJson = json_decode(file_get_contents($projectPath . 'composer.json'), true);
        $vendorDir = (isset($composerJson['config']['vendor-dir'])) ? $composerJson['config']['vendor-dir'].'/' : 'vendor/';
    } else {
        $vendorDir = 'vendor/';
    }
    if (file_exists($projectPath . $vendorDir)) {
        $found = true; // De map is gevonden
        break;
    }
    $projectPath = dirname($projectPath) . DIRECTORY_SEPARATOR;
}
if (!$found) {
    $projectPath = __DIR__ . '/';
}
// Controleer of er een UnitTest gestart moet worden?
$webpath = dirname($_SERVER['SCRIPT_NAME']);
if ($webpath != '/') {
    $webpath .= '/';
}
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Het path gedeelte van de uri
$filename = substr($uriPath, strlen($webpath)); // Bestandsnaam is het gedeelte van de uriPath zonder de WEBPATH
ini_set('display_errors', true);
error_reporting(E_ALL);
if (substr($filename, 0, 4) === 'run/') {
    // Include het gegenereerde unitest bestand.
    $script = sys_get_temp_dir(). 'devutils/' . basename($filename);
    register_shutdown_function(function ($script) { unlink($script); }, $script);
    include($script);
    exit;
}

// Render static files vanuit de Devutils map
require_once(__DIR__ . '/vendor/sledgehammer/core/src/render_public_folders.php');
// Include the target first (sets the Sledgehammer constants based on the target)
foreach($devutilsIncludes as $include) {
    require_once($include);    
}
// Include the devutils autoloader, and configure the ordering of autoloaders, target first,  devutils last.
$loader = require_once(__DIR__ . '/vendor/autoload.php');
$loader->unregister();
spl_autoload_unregister([Autoloader::class, 'lazyRegister']);
$loader->register(false);
spl_autoload_register([Autoloader::class, 'lazyRegister']);

// Initialize ErrorHandler and configure templates
ErrorHandler::enable();
Template::$includePaths[] = __DIR__.'/vendor/';
Template::$includePaths[] = __DIR__.'/templates/';

// Handle the request
$website = new DevUtilsWebsite($projectPath);
$website->handleRequest();
