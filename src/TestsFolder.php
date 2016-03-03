<?php

namespace Sledgehammer\Devutils;

use Sledgehammer\Core\Html;
use Sledgehammer\Core\Url;
use Sledgehammer\Mvc\Component\Breadcrumbs;
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

        return $this->build($this->package->name . ' TestSuite', null, value($_GET['group']));
    }

    public function dynamicFoldername($folder, $filename = null)
    {
        $url = Url::getCurrentURL();
        $filename = substr($url->path, strlen($this->getPath()));

        \Sledgehammer\file_extension(basename($filename), $title);
        Breadcrumbs::instance()->add($title, false);

        return $this->build($title, $this->package->path . DIRECTORY_SEPARATOR . $filename, value($_GET['group']));
    }

    public function dynamicFilename($filename)
    {
        \Sledgehammer\file_extension(basename($filename), $title);
        Breadcrumbs::instance()->add($title, false);

        return $this->build($title, $this->package->path . DIRECTORY_SEPARATOR . $filename, value($_GET['group']));
    }

    public function generateContent()
    {
        Breadcrumbs::instance()->add('TestSuite', $this->getPath());

        return parent::generateContent();
    }

    private function build($title, $path, $group = null)
    {
        $source = $this->generateTestSuite($title, $path, $group);
        $dir = sys_get_temp_dir() . 'devutils';
        \Sledgehammer\mkdirs($dir);
        $path = tempnam($dir, 'test');
        file_put_contents($path, $source);
        $url = Url::getCurrentURL();
        $url->path = \Sledgehammer\WEBPATH . 'run/' . basename($path);

        return new ViewHeaders(new PHPFrame($url), array('title' => $title));
    }

    private function generateTestSuite($title, $path, $group = null)
    {
        $xml = false;
        $bootstrap = false;
        if (file_exists($this->package->path . 'phpunit.xml')) {
            $xml = $this->package->path . 'phpunit.xml';
        }
        if (file_exists($this->package->path . 'phpunit.xml.dist')) {
            $xml = $this->package->path . 'phpunit.xml.dist';
        }
        if ($xml) {
            $config = simplexml_load_file($xml);
            if (in_array($config['bootstrap'], ['vendor/autoload.php', './vendor/autoload.php']) && file_exists($this->package->path . 'vendor/autoload.php') === false) { // modules 
                $bootstrap = \Sledgehammer\VENDOR_DIR . 'autoload.php';
//                <div class="unittest-assertion"><span class="label label-info" data-unittest="skipped">Skipped</span> Skipping tests for "apc" backend, the php-extension "apc" is not installed.<br><b>SledgehammerTests\Core\CacheTest</b>-&gt;<b>test_startup</b>() was skipped<br><b>PHPUnit_Framework_SkippedTestError</b>  thrown in <b>/Volumes/Sites/devutils/vendor/sledgehammer/core/tests/CacheTest.php</b> on line <b>34</b></div>
//                .unittest .unittest-assertion .label {
//    text-transform: uppercase;
//    font-size: 9px;
//    padding: .1em .5em .2em;
//}
            }
        }
        $source = '<h1 class="unittest-heading">' . Html::escape($title) . " <span class=\"label label-default\" data-unittest=\"indicator\">Running tests</span></h1>\n";
        if ($bootstrap) {
            $source .= '<div class="unittest"><div class="unittest-assertion"><span class="label label-default">WARNING</span> No local vendor/autoload.php detected, using <b>'.$bootstrap.'</b></div></div>';
        }
        $source .= "<?php\n";
        $source .= 'chdir(' . var_export($this->package->path, true) . ");\n";
        $source .= "define('Sledgehammer\ENVIRONMENT', 'phpunit');\n";
        $source .= "define('DEVUTILS_WEBPATH', '" . \Sledgehammer\WEBPATH . "');\n";
        $source .= "define('DEVUTILS_TEST_URL', ".var_export($this->getPath(), true).");\n";
        $source .= "define('DEVUTILS_PACKAGE_PATH', ".var_export($this->package->path, true).");\n";
        $source .= "\$GLOBALS['title'] = '" . $title . "';\n";
        $source .= "\$_SERVER['argv'] = array(\n";
        $source .= "\t'--printer', " . var_export(PHPUnitPrinter::class, true) . ",\n";
        $flags = [
            'report-useless-tests',
            'strict-coverage',
            'disallow-test-output',
            'enforce-time-limit',
            'debug',
        ];
        foreach ($flags as $flag) {
            $source .= "\t'--" . $flag . "',\n";
        }
        if ($bootstrap) {
            $source .= "\t'--bootstrap', " . var_export($bootstrap, true) . ",\n";
        }
        if ($group) {
            $source .= "\t'--', " . var_export($group, true) . ",\n";
        }
        if ($path) {
            $source .= "\t" . var_export($path, true) . ",\n";
        }
        $source .= ");\n";
        $source .= "\$loader = require_once('" . \Sledgehammer\DEVUTILS_PATH . "vendor/autoload.php');\n";
        $source .= "PHPUnit_TextUI_Command::main(false);\n";
        $source .= PHPUnitPrinter::class . "::summary();\n";
        $source .= "echo '<center>';\n";
        $source .= "Sledgehammer\statusbar();\n";
        $source .= "echo '</center>';\n";
        return $source;
    }

}
