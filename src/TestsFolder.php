<?php

namespace Sledgehammer\Devutils;

use Sledgehammer\Core\Html;
use Sledgehammer\Core\Url;
use Sledgehammer\Mvc\ViewHeaders;
use Sledgehammer\Mvc\VirtualFolder;

/**
 * Configure and run PHPUnit unittests.
 */
class TestsFolder extends VirtualFolder
{
    /**
     * @var Package
     */
    private $package;

    public function __construct($package)
    {
        parent::__construct();
        $this->package = $package;
    }

    public function index()
    {
        $tests = $this->package->getUnitTests();

        return $this->build($this->package->name.' TestSuite', $this->package->path, value($_GET['group']));
    }

    public function dynamicFoldername($folder, $filename = null)
    {
        $files = array();
        if ($folder === 'project' && empty($this->package->modules[$folder])) { // A non sledgehammer app?
            $module = new Module('project', $this->package->path);
        } elseif ($folder === 'app' && empty($this->package->modules[$folder])) { // A non sledgehammer app?
            $module = new Module('app', $this->package->path.'app');
        } else {
            $module = $this->package->modules[$folder];
        }
        $testsPath = is_dir($module->path.'tests') ? $module->path.'tests' : $module->path;
        $this->addCrumb($module->name.' module', $this->getPath(true));
        if ($filename === 'index.html') {
            return $this->build('UnitTests - '.$module->name, $testsPath, value($_GET['group']));
        } elseif ($filename === false) {
            $filename = substr(URL::getCurrentURL()->path, strlen($this->getPath(true)));
            file_extension(basename($filename), $title);
            $this->addCrumb($title, false);

            return $this->build($title, $testsPath.DIRECTORY_SEPARATOR.$filename, value($_GET['group']));
        } else {
            // Gaat het om een enkele unittest
            file_extension($filename, $title);
            $this->addCrumb($title, false);

            return $this->build($title, $testsPath.DIRECTORY_SEPARATOR.$filename, value($_GET['group']));
        }
    }
    public function dynamicFilename($filename)
    {
        \Sledgehammer\file_extension(basename($filename), $title);
        $this->addCrumb($title, false);

        return $this->build($title, $this->package->path.'tests'.DIRECTORY_SEPARATOR.$filename, value($_GET['group']));
    }

    public function generateContent()
    {
        $this->addCrumb('TestSuite', $this->getPath());

        return parent::generateContent();
    }

    private function build($title, $tests, $group = null)
    {
        $source = $this->generateTestSuite($title, $tests, $group);
        $dir = sys_get_temp_dir().'devutils';
        \Sledgehammer\mkdirs($dir);
        $path = tempnam($dir, 'test');
        file_put_contents($path, $source);
        $url = Url::getCurrentURL();
        $url->path = \Sledgehammer\WEBPATH.'run/'.basename($path);

        return new ViewHeaders(new PHPFrame($url), array('title' => $title));
    }

    private function generateTestSuite($title, $path, $group = null)
    {
        $xml = false;
        $bootstrap = false;
        if (file_exists($this->package->path.'phpunit.xml')) {
            $xml = $this->package->path.'phpunit.xml';
        }
        if (file_exists($this->package->path.'phpunit.xml.dist')) {
            $xml = $this->package->path.'phpunit.xml.dist';
        }
        if ($xml) {
            $config = simplexml_load_file($xml);
            if ($config['bootstrap'] == 'vendor/autoload.php' && file_exists($this->package->path.'vendor/autoload.php') === false) { // modules 
                $bootstrap = \Sledgehammer\VENDOR_DIR.'autoload.php';
            }
        }
        $source = '<h1 class="unittest-heading">'.Html::escape($title)." <span class=\"label\" data-unittest=\"indicator\">Running tests</span></h1>\n";
        $source .= "<?php\n";
        $source .= 'chdir('.var_export($this->package->path, true).");\n";
        $source .= "define('DEVUTILS_WEBPATH', '".\Sledgehammer\WEBPATH."');\n";
        $source .= "\$GLOBALS['title'] = '".$title."';\n";
        $source .= "\$_SERVER['argv'] = array(\n";
        $source .= "\t'--printer', ".var_export(PHPUnitPrinter::class, true).",\n";
        $flags = [
            'report-useless-tests',
            'strict-coverage',
            'disallow-test-output',
            'enforce-time-limit',
            'debug',
        ];
        foreach ($flags as $flag) {
            $source .= "\t'--".$flag."',\n";
        }
        if ($bootstrap) {
            $source .= "\t'--bootstrap', '".addslashes($bootstrap)."',\n";
        }
        if ($group) {
            $source .= "\t'--', '".addslashes($group)."',\n";
        }
        $source .= "\t'".addslashes($path)."',\n";
        $source .= ");\n";

        $source .= "\$loader = require_once('".\Sledgehammer\DEVUTILS_PATH."vendor/autoload.php');\n";
        $source .= PHPUnitPrinter::class."::summary();\n";
        $source .= "PHPUnit_TextUI_Command::main(false);\n";
        $source .= PHPUnitPrinter::class."::summary();\n";
        $source .= "echo '<center>';\n";
        $source .= "Sledgehammer\statusbar();\n";
        $source .= "echo '</center>';\n";
        $source .= '?>';

        return $source;
    }
}
