<?php
/**
 * Genereert een SimpleTest TestSuites
 *
 * @package DevUtils
 */

class UnitTests extends VirtualFolder {

	private
		$project;

	function __construct($project) {
		parent::__construct();
		$this->project = $project;
	}

	function index() {
		$tests = $this->project->getUnitTests();
		foreach ($tests as $filename) {
			$module = dirname($filename);
			$filename = basename($filename);
			$files[] = $this->project->modules[$module]->path.'tests'.DIRECTORY_SEPARATOR.$filename;
		}
		return $this->build($this->project->name.' TestSuite', $files);
	}

	function dynamicFilename($filename) {
		 deprecated('??');
		//Breadcrumbs::add('UnitTest '.substr($filename, -4));
		//return $this->build($filename, array($this->module->path.'tests/'.$filename));
	}

	function dynamicFoldername($folder, $filename = null) {
		$files = array();
		$module = $this->project->modules[$folder];
		Breadcrumbs::add($module->name.' module', $this->getPath(true));
		if ($filename != 'index.html') { // Gaat het om een enkele unittest
			file_extension($filename, $filename_without_extention);
			Breadcrumbs::add($filename_without_extention);
			return $this->build($filename_without_extention, array($module->path.'tests'.DIRECTORY_SEPARATOR.$filename));
		} else {
			$tests = $module->getUnitTests();
			foreach ($tests as $filename) {
				$files[] = $module->path.'tests'.DIRECTORY_SEPARATOR.$filename;
			}
			return $this->build('UnitTests - '.$module->name, $files);
		}
	}

	function execute() {
		Breadcrumbs::add('TestSuite', $this->getPath());
		getDocument()->title = 'UnitTests';
		getDocument()->stylesheets[] = WEBROOT.'core/stylesheets/debug.css';
		getDocument()->stylesheets[] = WEBROOT.'stylesheets/simpletest.css';
		return parent::execute();
	}
	
	private function build($title, $tests) {
		$source = $this->generateTestSuite($title, $tests);
		$filename = md5(serialize($tests)).'.php';
		$tmpFile = PATH.'tmp/unittests/'.$filename;
		mkdirs(dirname($tmpFile));
		file_put_contents($tmpFile, $source);
		$uri = URL::info();
		return new PHPFrame($uri['scheme'].'://'.$uri['host'].WEBPATH.'run_tests/'.$filename);						
	} 

	private function generateTestSuite($title, $tests) {
		$source = "<?php\n";
		$source .= "require_once('".$this->project->path."sledgehammer/core/init_framework.php');\n";
		$source .= "\$GLOBALS['AutoLoader']->inspectModules(array('simpletest' => array(\n";
		$source .= "\t'name'=> 'DevUtils/SimpleTest',\n";
		$source .= "\t'path'=> '".addslashes(PATH)."sledgehammer/simpletest'\n";
		$source .= ")));\n";
		$source .= "\$testSuite = new TestSuite('$title');\n";
		foreach ($tests as $testfile) {
			$source .= "\$testSuite->addFile('".addslashes($testfile)."');\n";
		}
		//$source .= "ini_set('display_error', true);\n";
		$source .= "\$testSuite->run(new HtmlReporter());\n";
		
		$source .= "echo '<center>';\n";
		$source .= "statusbar();\n";
		$source .= "echo '</center>';\n";
		$source .= '?>';
		return $source;
	}
}
?>
