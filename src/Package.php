<?php

namespace Sledgehammer\Devutils;

use DirectoryIterator;
use Exception;
use Sledgehammer\Core\Object;
use Sledgehammer\Core\Json;

class Package extends Object
{

    /**
     * @var string
     */
    public $path;

    /**
     * @var object
     */
    public $composer;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $vendor;

    /**
     * @var Package
     */
    public $project;

    public function __construct($path, $project = null)
    {
        if (substr($path, -1) !== '/') {
            $path .= '/';
        }
        $this->path = $path;
        $this->project = $project;
        if (file_exists($path.'composer.json') === false) {
            throw new Exception('Invalid path "'.$path.'", composer.json not found');
        }
        $this->composer = Json::decode(file_get_contents($path.'composer.json'));
        $this->name = $this->composer->name;
        $this->vendor = substr($this->name, 0, strpos($this->name, '/'));
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
            foreach ($xml->xpath('//testsuite') as $suite) {
                foreach ($suite->directory as $dirNode) {
                    $suffix = (string) $dirNode['suffix'] ?: 'Test.php';
                    $dir = (string) $dirNode;
                    $dir = preg_replace('/^\.\//', '', $dir); // strip leading "./"
                    $tests = array_merge($tests, $this->detectTestsIn($this->path.$dir, $suffix));
                }
            }
        } elseif (is_dir($this->path.'tests')) {
            $tests = $this->detectTestsIn($this->path.'tests');
        }
        return $tests;
    }

    protected function detectTestsIn($path, $suffix = 'Test.php')
    {
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
