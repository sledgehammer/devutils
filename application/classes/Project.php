<?php
/**
 *
 * @package DevUtils
 */

class Project extends Object {

	public 
		$identifier,
		$name,
		$path,
		$application,
		$modules;

	function __construct($projectPath) {
		$this->path = $projectPath;
		$foldernames = array_reverse(explode(DIRECTORY_SEPARATOR, $projectPath));
		foreach($foldernames as $name) {
			if (!in_array($name, array('', 'website', 'development', 'release'))) {
				$this->identifier = $name;
				$this->name = ucfirst($name);
				break;
			}
		}
		$modules = array_reverse(SledgeHammer::getModules($projectPath.'sledgehammer'.DIRECTORY_SEPARATOR));
		foreach ($modules as $identifier => $module) {
			$this->modules[$identifier] = new Module($identifier, $module['path']);
			if ($identifier == 'application') {
				$this->application = &$this->modules[$identifier];
			}
		}
	}

	function getFavicon() {
		$favicon = $this->path.'application/public/favicon.ico';
		if (file_exists($favicon)) {
			return $favicon;
		}
		return false;
	}

	function getProperties() {
		if ($this->application) {
			$info = $this->application->getProperties();
		}
		$info['Path'] = $this->path;
		$info['Environment'] = ENVIRONMENT;
		return $info;
	}

	function getUnitTests() {
		$tests = array();
		foreach ($this->modules as $identifier => $module) {
			foreach ($module->getUnitTests() as $testfile) {
				$tests[] = $identifier.'/'.$testfile;
			}
		}
		return $tests;
	}
}
?>
