<?php
/**
 * Module.
 */

namespace Sledgehammer\Devutils;

use Sledgehammer\Core\Object;

deprecated('Module is deprecated use Sledgehammer\Devutils\Package');
/**
 * Een Module binnen een Project.
 */
class Module extends Object
{
    public $name,
        $identifier,
        $path,
        $icon = 'module.png';

    private $info = array();

    public function __construct($identifier, $path)
    {
        $this->identifier = $identifier; // De identifier wordt binnen de DevUtils website als bestands -en mapnaam gebruikt
        if (in_array(substr($path, -1), array('/', '\\')) == false) {
            $path .= DIRECTORY_SEPARATOR; // trailing "/" toevoegen
        }
        $this->path = $path;
        if (file_exists($path.'composer.json')) {
            $this->info = Json::decode(file_get_contents($path.'composer.json'), true);
            $this->name = ucfirst(basename($this->info['name']));
            if (isset($this->info['icon'])) {
                $this->icon = $this->info['icon'];
            }
        } else {
            $this->name = ucfirst($identifier);
        }
    }

    public function getProperties()
    {
        $info = array();
        foreach ($this->info as $name => $value) {
            if (!in_array($name, array('required_modules', 'optional_modules', 'name'))) {
                $info[ucfirst($name)] = $value;
            }
        }

        return $info;
    }

    public function getUtilities()
    {
        Util::$module = $this;
        $path = $this->path.'utils/';
        if (file_exists($path)) {
            if (file_exists($path.'classes')) {
                Framework::$autoloader->importFolder($path.'classes/');
            }

            return include $path.'getUtils.php';
        }

        return array();
    }

    public function getUnitTests($path = null)
    {
        $tests = array();
        if (is_dir($this->path.'tests/')) {
            $basepath = $this->path.'tests/';
        } else {
            $basepath = $this->path;
        }
        if ($path === null) {
            $path = $basepath;
        }
        $dir = new \DirectoryIterator($path);
        foreach ($dir as $entry) {
            if ($entry->isDot()) {
                continue;
            }
            if ($entry->isDir()) {
                $tests = array_merge($tests, $this->getUnitTests($entry->getPathname()));
                continue;
            }
            $filename = $entry->getPathname();
            if (substr($filename, -8) == 'Test.php' && strpos(file_get_contents($filename), 'function test')) {
                $tests[] = substr($filename, strlen($basepath));
            }
        }
        ksort($tests); // Sorteer de tests alfabetisch
        return $tests;
    }
}
