<?php
/**
 * Project
 */
namespace Sledgehammer;
/**
 * Een sledgehammer project/applicatie.
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
		$foldernames = array_reverse(explode(DIRECTORY_SEPARATOR, realpath($projectPath)));
		foreach($foldernames as $name) {
			if (!in_array($name, array('', 'website', 'development', 'release'))) {
				$this->identifier = $name;
				$this->name = ucfirst($name);
				break;
			}
		}
		$moduleDir = $projectPath.'vendor/sledgehammer/';
		if (file_exists($projectPath.'composer.json')) {
			$composerJson = json_decode(file_get_contents($projectPath.'composer.json'), true);
			if (isset($composerJson['config']['vendor-dir'])) {
				$moduleDir = $projectPath.$composerJson['config']['vendor-dir'].'/sledgehammer/';
			}
		}
		$modules = array_reverse(Framework::getModules($moduleDir));
		foreach ($modules as $identifier => $module) {
			$this->modules[$identifier] = new Module($identifier, $module['path']);
			if ($identifier == 'application') {
				$this->application = &$this->modules[$identifier];
			}
		}
	}

	function getFavicon() {
		$favicon = $this->path.'app/public/favicon.ico';
		if (file_exists($favicon)) {
			return $favicon;
		}
		return false;
	}

	function getProperties() {
		if ($this->application) {
			$info = $this->application->getProperties();
		}
		$info['Environment'] = ENVIRONMENT;
		$info['Path'] = $this->path;
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
