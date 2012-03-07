<?php

/**
 * Genereert een SimpleTest TestSuites
 *
 * @package DevUtils
 */

namespace SledgeHammer;

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
			$files[] = $this->project->modules[$module]->path . 'tests' . DIRECTORY_SEPARATOR . $filename;
		}
		return $this->build($this->project->name . ' TestSuite', $files);
	}

	function dynamicFoldername($folder, $filename = null) {
		$files = array();
		$module = $this->project->modules[$folder];
		Breadcrumbs::add($module->name . ' module', $this->getPath(true));
		if ($filename != 'index.html') { // Gaat het om een enkele unittest
			file_extension($filename, $filename_without_extention);
			Breadcrumbs::add($filename_without_extention);
			return $this->build($filename_without_extention, array($module->path . 'tests' . DIRECTORY_SEPARATOR . $filename));
		} else {
			$tests = $module->getUnitTests();
			foreach ($tests as $filename) {
				$files[] = $module->path . 'tests' . DIRECTORY_SEPARATOR . $filename;
			}
			return $this->build('UnitTests - ' . $module->name, $files);
		}
	}

	function generateContent() {
		Breadcrumbs::add('TestSuite', $this->getPath());
		return parent::generateContent();
	}

	private function build($title, $tests) {
		$source = $this->generateTestSuite($title, $tests);
//		return new ComponentHeaders(new PHPSandbox($url), array('title' => $title));
		$filename = md5(serialize($tests)) . '.php';
		$tmpFile = TMP_DIR . 'UnitTests/' . $filename;
		mkdirs(dirname($tmpFile));
		file_put_contents($tmpFile, $source);
		$url = URL::getCurrentURL();
		$url->path = WEBPATH . 'run_tests/' . $filename;
		return new ViewHeaders(new PHPFrame($url), array('title' => $title));
	}

	private function generateTestSuite($title, $tests) {
		$source = "<?php\n";
		$source .= "require_once('" . $this->project->path . "sledgehammer/core/init_framework.php');\n";
		$source .= "\SledgeHammer\Framework::\$autoLoader->importModule(array(\n";
		$source .= "\t'name'=> 'DevUtils/SimpleTest',\n";
		$source .= "\t'path'=> '" . addslashes(MODULES_DIR) . "simpletest'\n";
		$source .= "));\n";
		foreach ($this->project->modules as $module) {
			if (is_dir($module->path . 'tests')) {
				$source .= "\SledgeHammer\Framework::\$autoLoader->importFolder('" . addslashes($module->path . 'tests') . "', array(\n";
				$source .= "\t'mandatory_definition' => false,\n";
				$source .= "));\n";
			}
		}
		$source .= "require_once('" . $this->project->path . "sledgehammer/core/tests/TestCase.SimpleTest.inc');\n";
		$source .= "require_once('" . PATH . "application/classes/DevUtilsReporter.php');\n";
		$source .= "\$testSuite = new TestSuite('$title');\n";
		foreach ($tests as $testfile) {
			$source .= "\$testSuite->addFile('" . addslashes($testfile) . "');\n";
		}
		$source .= "\$testSuite->run(new DevUtilsReporter());\n";
		$source .= "echo '<center>';\n";
		$source .= "SledgeHammer\statusbar();\n";
		$source .= "echo '</center>';\n";
		$source .= '?>';
		return $source;
	}

}

?>
