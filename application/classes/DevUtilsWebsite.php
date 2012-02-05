<?php
/**
 * The DevUtils Application FrontController
 *
 * @package DevUtils
 */

namespace SledgeHammer;

class DevUtilsWebsite extends Website {

	public
	$project;

	function __construct($projectPath) {
		if (file_exists($projectPath.'sledgehammer/core/init_framework.php')) {
			$this->project = new Project($projectPath);
		}
		parent::__construct();
	}

	function index() {
		$url = URL::getCurrentURL();
		$url->path = $this->getPath().'rewrite-check.html';
		$contents = file_get_contents($url);
		if ($contents != 'Apache Rewrite module is enabled') { // Is het openen van de rewrite_check.html mislukt?
			return new HTML('<h1>Error loading "/rewrite_check.html"</h1>&quot;AllowOverride All&quot; is required in your httpd.conf for this &lt;Directory&gt;<hr />');
		}
		if (!$this->project) {
			return new MessageBox('error', 'Geen project gevonden', 'Controleer de stappen in "devutils/docs/installatie.txt"');
		}
		$iconPrefix = WEBROOT.'icons/';

		// Properties
		if ($this->project->application) {
			$properties = $this->project->getProperties();
		}
		// Utilities
		$utilityList = array();
		foreach ($this->project->modules as $moduleID => $module) {
			foreach ($module->getUtilities() as $utilFilename => $util) {
				$utilityList[$moduleID.'/utils/'.$utilFilename] = array('icon' => $util->icon, 'label' => $util->title);
			}
		}
		$utilityList['phpinfo.php'] = array('icon' => $iconPrefix.'php.gif', 'label' => 'PHP Info');

		// Unittests
		$tests = $this->project->getUnitTests();
		foreach ($tests as $testfile) {
			$unittestList['tests/'.$testfile] = array('icon' => 'unittest', 'label' => substr($testfile, 0, -4));
		}
		$template = new Template('project.php', array(
					'project' => $this->project->name,
					'properties' => new DefinitionList($properties),
					'utilities' => new NavList($utilityList),
					'unittests' => new NavList($unittestList),
						), array(
					'title' => $this->project->name.' project',
				));
		return $template;
	}

	function phpinfo() {
		Breadcrumbs::add('PHP Info');
		return new PHPInfo;
	}

	function rewrite_check() {
		die('Apache Rewrite module is enabled');
	}

	function phpdocs() {
		$command = new PhpDocs($this->project);
		return $command->generateContent();
	}

	function project_icon() {
		$favicon = $this->project->getFavicon();
		if ($favicon) {
			return new FileDocument($favicon);
		}
		return new FileDocument(APPLICATION_DIR.'public/icons/project.png');
	}

	function flush_phpdocs() {
		$count = rmdirs(PATH.'tmp/phpdocs/');
		return new MessageBox('ok.gif', 'Flushing PhpDocumentor files', $count.' files deleted');
	}

	/**
	 * Als er geen bestand in de module_icons map staat voor de opgegeven module, geeft dan het standaard icoon weer
	 */
	function module_icons_folder() {
		$url = URL::getCurrentURL();
		file_extension($url->getFilename(), $module);
		$icon = $this->project->path.'sledgehammer/'.$module.'/icon.png';
		if (file_exists($icon)) {
			return new FileDocument($icon);
		}
		return new FileDocument(PATH.'application/public/icons/module.png');
	}

	function files_folder() {
		Breadcrumbs::add('Files', $this->getPath(true));
		$command = new FileBrowser($this->project->path, array('show_fullpath' => true, 'show_hidden_files' => true));
		return $command->generateContent();
	}

	function tests_folder() {
		$folder = new UnitTests($this->project);
		return $folder->generateContent();
	}

	function phpdocs_folder($filename) {
		$path = PhpDocs::documentation_path($this->project);
		if ($filename === false) {
			$filename = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen($this->getPath(true)));
		}
		return new FileDocument($path.$filename);
	}

	function dynamicFoldername($folder) {
		if (isset($this->project->modules[$folder])) {
			$command = new ModuleFolder($this->project->modules[$folder]);
			return $command->generateContent();
		}
		return $this->onFolderNotFound();
	}

	function generateContent() {
		if ($this->project) {
			//$this->project->name.' project'
			Breadcrumbs::add(array('icon' => 'home', 'label' => 'Home'), $this->getPath());
		}
		return parent::generateContent();
	}

	protected function wrapContent($content) {
		if (!$this->project) {
			return $content;
		}

		$navigation = array(
			'Application',
			WEBPATH => array('icon' => WEBPATH.'project_icon.ico', 'label' => $this->project->name),
		);
		// Documentation
		$navigation[WEBROOT.'phpdocs.html'] = array('icon' => 'icons/documentation.png', 'label' => 'API Documentation');
		if (file_exists($this->project->path.'docs/')) {
			$applicationMenu[WEBROOT.'files/docs/'] = array('icon' => 'book', 'label' => 'Documentation');
		}
		// UnitTests
		$navigation[WEBROOT.'tests/'] = array('icon' => 'accept', 'label' => 'Run TestSuite');
		// Modules
		$navigation[] = 'Modules';
		$sortedModules = $this->project->modules;
		ksort($sortedModules);
		foreach ($sortedModules as $name => $module) {
			if ($name != 'application') {
				$navigation[WEBROOT.$name.'/'] = array('icon' => WEBROOT.'module_icons/'.$name.'.png', 'label' => $module->name);
			}
		}

		$template = new Template('layout.php', array(
					'navigation' => new NavList($navigation),
					'breadcrumbs' => new Breadcrumbs,
					'contents' => $content,
						), array(
					'css' => array(
						WEBROOT.'mvc/css/bootstrap.css',
						WEBROOT.'css/devutils.css',
					),
				));
		return $template;
	}

	function onSessionStart() {
		return false;
	}

}

?>
