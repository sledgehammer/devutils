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

    public function getUnitTests()
    {
        $tests = [];
        $xml = false;
        if (file_exists($this->path.'phpunit.xml')) {
            $xml = simplexml_load_file($this->path.'phpunit.xml');
        }
        if (file_exists($this->path.'phpunit.xml.dist')) {
            $xml = simplexml_load_file($this->path.'phpunit.xml.dist');
        }
        
        if ($xml) {
            foreach ($xml->testsuite as $suite) {
                foreach ($suite->directory as $dirNode) {
                    $suffix = (string) $dirNode['suffix'] ?: 'Test.php';
                    $dir = (string) $dirNode;
                    $dir = preg_replace('/^\.\//','', $dir); // strip leading "./"
                    $tests = array_merge($tests, $this->detectTestsIn($this->path.$dir, $suffix));
                }
            }
        } elseif (is_dir($this->path.'tests')) {
            $tests = $this->detectTestsIn($this->path.'tests');
        }
        return $tests;
    }
    
    protected function detectTestsIn($path, $suffix = 'Test.php') {
        $tests = [];
        $dir = new DirectoryIterator($path);
        foreach ($dir as $entry) {
            if ($entry->isDot()) {
                continue;
            }
            if ($entry->isDir()) {
                $tests = array_merge($tests, $this->detectTestsIn($entry->getPathname(), $suffix));
                continue;
            }
            $filename = $entry->getPathname();
            if (\Sledgehammer\text($filename)->endsWith($suffix)) { // && strpos(file_get_contents($filename), 'function test')
                $tests[] = substr($filename, strlen($this->path));
            }
        }
        ksort($tests); // Sorteer de tests alfabetisch
        return $tests;
    }
}
