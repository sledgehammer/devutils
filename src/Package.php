<?php

namespace Sledgehammer\Devutils;

use DirectoryIterator;
use Sledgehammer\Core\Object;

class Package extends Object
{
    public $name;
    public $vendor;
    public $path;

    public function __construct($name)
    {
        $this->name = $name;
        $this->vendor = substr($name, 0, strpos($name, '/'));
        $this->path = \Sledgehammer\VENDOR_DIR.$name.'/';
    }

    public function getProperties()
    {
        return [];
    }

    public function getUtilities()
    {
        Util::$module = $this;
        $path = $this->path.'devutils.php';
        if (file_exists($path)) {
            return include $path;
        }

        return [];
    }

    public function getUnitTests($path = null)
    {
        $tests = [];
        if (is_dir($this->path.'tests/')) {
            $basepath = $this->path.'tests/';
        } else {
            $basepath = $this->path;
        }
        if ($path === null) {
            $path = $basepath;
        }
        $dir = new DirectoryIterator($path);
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
