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
//		foreach ($tests as $filename) {
//			$module = dirname($filename);
//			$filename = basename($filename);
//			$files[] = $this->project->modules[$module]->path . 'tests' . DIRECTORY_SEPARATOR . $filename;
//		}
		return $this->build($this->project->name . ' TestSuite', $this->project->path);
	}

	function dynamicFoldername($folder, $filename = null) {
		$files = array();
		$module = $this->project->modules[$folder];
		Breadcrumbs::add($module->name . ' module', $this->getPath(true));
		if ($filename != 'index.html') { // Gaat het om een enkele unittest
			file_extension($filename, $filename_without_extention);
			Breadcrumbs::add($filename_without_extention);
			return $this->build($filename_without_extention, $module->path . 'tests' . DIRECTORY_SEPARATOR . $filename);
		} else {
//			$tests = $module->getUnitTests();
//			foreach ($tests as $filename) {
//				$files[] = $module->path . 'tests' . DIRECTORY_SEPARATOR . $filename;
//			}
			return $this->build('UnitTests - ' . $module->name, $module->path);
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

	private function generateTestSuite($title, $path) {
		$source = "<h1 class=\"unittest_heading\">".HTML::escape($title)." <span class=\"label\">Running tests</span></h1>\n";
		$source .= "<?php\n";
		$source .= "require_once('" . $this->project->path . "sledgehammer/core/init_tests.php');\n";
		$source .= "restore_error_handler();";
		$source .= "\SledgeHammer\Framework::\$autoLoader->standalone = false;\n";
		$source .= "require_once('PHPUnit/Autoload.php');\n";
		$source .= "require_once('" . PATH . "application/classes/DevUtilsPHPUnitPrinter.php');\n";
		$source .= "\$GLOBALS['title'] = '".$title."';\n";
		$source .= "\$_SERVER['argv'] = array(\n";
		$source .= "\t'--printer', 'DevUtilsPHPUnitPrinter',\n";
		$source .= "\t'--strict',\n";
		$source .= "\t'--debug',\n";
		$source .= "\t'" . addslashes($path) . "',\n";
//		foreach ($tests as $i => $testfile) {
//			$source .= "\t'UnitTest', '" . addslashes($testfile) . "',\n";
//		}
		$source .= ");\n";
		$source .= "PHPUnit_TextUI_Command::main(false);\n";
		$source .= "\DevUtilsPHPUnitPrinter::summary();\n";
		$source .= "echo '<center>';\n";
		$source .= "SledgeHammer\statusbar();\n";
		$source .= "echo '</center>';\n";
		$source .= '?>';
		return $source;
	}

}

?>
