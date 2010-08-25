<?php
/**
 * Een Module binnen een Project
 * @package DevUtils
 */

class Module extends Object {

	public
		$name,
		$identifier,
		$path,
		$icon = 'module.png';

	private
		$info = array();

	function __construct($identifier, $path) {
		$this->identifier = $identifier; // De identifier wordt binnen de DevUtils website als bestands -en mapnaam gebruikt
		if (in_array(substr($path, -1), array('/', '\\')) == false) {
			$path .= DIRECTORY_SEPARATOR; // trailing "/" toevoegen
		}
		$this->path = $path;
		if ($identifier == 'application') {
			$this->name = 'Application';
		} else {
			// Laad de module.ini of application.ini
			$this->info = parse_ini_file($path.'module.ini');
			$this->name = $this->info['name'];
			if (isset($this->info['icon'])) {
				$this->icon = $this->info['icon'];
			}
		}
	}

	function getProperties() {
		$info = array();
		foreach ($this->info as $name => $value) {
			if (!in_array($name, array('required_modules', 'optional_modules', 'name'))) {
				$info[ucfirst($name)] = $value;
			}
		}
		return $info;
	}

	/**
	 */
	function getUtilities() {
		Util::$module = $this;
		$path = $this->path.'utils/';
		if (file_exists($path)) {
			if (file_exists($path.'classes')) {
				$GLOBALS['AutoLoader']->inspectFolder($path.'classes/');
			}

			return include($path.'getUtils.php');
		}
		return array();
	}

	function getUnitTests() {
		$tests = array();
		$path = $this->path.'tests/';
		if (!file_exists($path)) {
			return $tests;
		}
		$dir = new DirectoryIterator($path);
		foreach ($dir as $entry) {
			if (!$entry->isFile()) {
				continue;
			}
			$filename = $entry->getFilename();
			if (substr($filename, -4) == '.php' && strpos(file_get_contents($entry->getPathname()), 'function test')) {
				if ($filename != 'DatabaseTestCase.php') {
					$tests[] = $filename;
				}
			}
		}
		ksort($tests); // Sorteer de tests alfabetisch 
		return $tests;
	}
}
?>
