<?php
/**
 * UnitTests
 */
namespace Sledgehammer;
/**
 * Configure and run PHPUnit unittests.
 * @package DevUtils
 */
class UnitTests extends VirtualFolder {

	private $project;

	function __construct($project) {
		parent::__construct();
		$this->project = $project;
	}

	function index() {
		$tests = $this->project->getUnitTests();
		return $this->build($this->project->name.' TestSuite', $this->project->path, value($_GET['group']));
	}

	function dynamicFoldername($folder, $filename = null) {
		$files = array();
        if ($folder === 'project' && empty($this->project->modules[$folder])) { // A non sledgehammer app?
			$module = new Module('project', $this->project->path);
		} elseif ($folder === 'app' && empty($this->project->modules[$folder])) { // A non sledgehammer app?
			$module = new Module('app', $this->project->path.'app');
		} else {
			$module = $this->project->modules[$folder];
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

	function generateContent() {
		$this->addCrumb('TestSuite', $this->getPath());
		return parent::generateContent();
	}

	private function build($title, $tests, $group = null) {
		$source = $this->generateTestSuite($title, $tests, $group);
		$filename = md5(serialize($tests)).'.php';
		$tmpFile = TMP_DIR.'UnitTests/'.$filename;
		mkdirs(dirname($tmpFile));
		file_put_contents($tmpFile, $source);
		$url = URL::getCurrentURL();
		$url->path = WEBPATH.'run_tests/'.$filename;
		return new ViewHeaders(new PHPFrame($url), array('title' => $title));
	}

	private function generateTestSuite($title, $path, $group = null) {
		$source = "<h1 class=\"unittest-heading\">".Html::escape($title)." <span class=\"label\" data-unittest=\"indicator\">Running tests</span></h1>\n";
		$source .= "<?php\n";
		$source .= "const DEVUTILS_WEBPATH = '".WEBPATH."';\n";
		$source .= "\$GLOBALS['title'] = '".$title."';\n";
		$source .= "\$_SERVER['argv'] = array(\n";
		$source .= "\t'--printer', 'DevUtilsPHPUnitPrinter',\n";
        $flags = [
            'report-useless-tests',
            'strict-coverage',
            'disallow-test-output',
            'enforce-time-limit',
            'debug'
        ];
        foreach ($flags as $flag) {
            $source .= "\t'--".$flag."',\n";
        }
		if ($group) {
            $source .= "\t'--group', '".addslashes($group)."',\n";
        }
		$source .= "\t'".addslashes($path)."',\n";
		$source .= ");\n";
		$source .= "require_once('".PATH."phpunit/vendor/autoload.php');\n";
		$source .= "require_once('".$this->project->modules['core']->path."phpunit_bootstrap.php');\n";
		if (file_exists($this->project->path.'phpunit.xml')) {
			$config = simplexml_load_file($this->project->path.'phpunit.xml');
			if ($config['bootstrap']) {
				$source .= "require_once('".addslashes($this->project->path.$config['bootstrap'])."');\n";
			}
		}
		$source .= "require_once('".__DIR__."/DevUtilsPHPUnitPrinter.php');\n";
		$source .= "PHPUnit_TextUI_Command::main(false);\n";
		$source .= "\DevUtilsPHPUnitPrinter::summary();\n";
		$source .= "echo '<center>';\n";
		$source .= "Sledgehammer\statusbar();\n";
		$source .= "echo '</center>';\n";
		$source .= '?>';
		return $source;
	}

}

?>
