<?php
/**
 * DevUtilsWebsite
 */
namespace Sledgehammer;
/**
 * The DevUtils Application FrontController
 * @package DevUtils
 */
class DevUtilsWebsite extends Website {

	private $project;
	private static $username;
	private static $password;

	/**
	 *
	 * @var Breadcrumbs
	 */
	private $breadcrumbs;

	function __construct($projectPath) {
		$this->breadcrumbs = new Breadcrumbs();
		if ($projectPath) {
			$this->project = new Project($projectPath);
			$this->addCrumb(array('icon' => 'home-white', 'label' => 'Home'), $this->getPath());
		}
		parent::__construct();
	}

	function index() {
		if (!$this->project) {
			return new ViewHeaders(Alert::error('<h3>No projects detected</h3>See "devutils/docs/installation.txt" for more info'), array('title' => 'Error', 'css' => WEBROOT.'mvc/css/bootstrap.css'));
		}
		$iconPrefix = WEBROOT.'icons/';

		// Properties
		$properties = $this->project->getProperties();

		// Utilities
		$utilityList = array();
		foreach ($this->project->modules as $moduleID => $module) {
			foreach ($module->getUtilities() as $utilFilename => $util) {
				$utilityList[$moduleID.'/utils/'.$utilFilename] = array('icon' => $util->icon, 'label' => $util->title);
			}
		}
		$utilityList['phpinfo.php'] = array('icon' => $iconPrefix.'php.gif', 'label' => 'PHP Info');

		// Unittests
		$modules = $this->project->modules;
		if ($this->project->application === null && file_exists($this->project->path.'app/tests')) {
			// A non sledgehammer app? extract tests
			array_key_unshift($modules, 'app', new Module('app', $this->project->path.'app'));
		}
		foreach ($modules as $identifier => $module) {
			foreach ($module->getUnitTests() as $testfile) {
				if (text($testfile)->endsWith('Test.php')) {
					$label = substr($testfile, 0, -8);
				} else {
					$label = substr($testfile, 0, -4);
				}
				$label = basename($label);
				$unittestList['tests/'.$identifier.'/'.$testfile] = array('icon' => $iconPrefix.'test.png', 'label' => ucfirst($identifier).' - '.$label);
			}
		}
		$template = new Template('project.php', array(
			'project' => $this->project->name,
			'properties' => new DescriptionList($properties, array('class' => 'dl-horizontal')),
			'utilities' => new Nav($utilityList, array('class' => 'nav nav-list')),
			'unittests' => new Nav($unittestList, array('class' => 'nav nav-list')),
				), array(
			'title' => $this->project->name.' project',
		));
		return $template;
	}

	function phpinfo() {
		$this->addCrumb('PHP Info');
		return new PHPInfo;
	}

	function rewrite_check() {
		die('Apache Rewrite module is enabled');
	}

	function project_icon() {
		$favicon = $this->project->getFavicon();
		if ($favicon) {
			return new FileDocument($favicon);
		}
		return new FileDocument(APP_DIR.'public/icons/project.png');
	}

	/**
	 * Als er geen bestand in de module_icons map staat voor de opgegeven module, geeft dan het standaard icoon weer
	 */
	function module_icons_folder() {
		$url = URL::getCurrentURL();
		file_extension($url->getFilename(), $module);
		$icon = $this->project->modules[$module]->path.'icon.png';
		if (file_exists($icon)) {
			return new FileDocument($icon);
		}
		return new FileDocument(APP_DIR.'public/icons/module.png');
	}

	function files_folder() {
		$this->addCrumb('Files', $this->getPath(true));
		$command = new FileBrowser($this->project->path, array('show_fullpath' => true, 'show_hidden_files' => true));
		return $command->generateContent();
	}

	function tests_folder() {
		$folder = new UnitTests($this->project);
		return $folder->generateContent();
	}

	function phpunit_folder($filename) {
		$url = URL::getCurrentURL();
		$folders = $url->getFolders();
		$module = $this->project->modules[$folders[$this->depth + 1]];

		$command = '/usr/local/bin/phpunit --strict --bootstrap '.escapeshellarg($this->project->modules['core']->path.'init_tests.php').' '.escapeshellarg(($module->path)."tests/".$url->getFilename());
		$source = '<?php echo "<pre>"; passthru("'.$command.'"); echo "</pre>";';
		return new ViewHeaders(new PHPSandbox($source), array('title' => 'PHPUnit'));
	}

	function phpdocs_folder() {
		$folder = new PhpDocs($this->project);
		return $folder->generateContent();
	}

	function dynamicFoldername($folder) {
		if (isset($this->project->modules[$folder])) {
			$command = new ModuleFolder($this->project->modules[$folder]);
			return $command->generateContent();
		}
		return $this->onFolderNotFound();
	}

	function generateDocument() {
		if ($this->login() == false) {
			$doc = new HtmlDocument();
			$doc->content = new HttpError(401);
			return $doc;
		}
		return parent::generateDocument();
	}

	function wrapContent($content) {
		if (!$this->project) {
			return $content;
		}
		$navigation = array(
			WEBPATH => array('icon' => WEBPATH.'project_icon.ico', 'label' => $this->project->name),
		);
		// Documentation
		$navigation[WEBROOT.'phpdocs/'] = array('icon' => 'icons/documentation.png', 'label' => 'API Documentation');
		if (file_exists($this->project->path.'docs/')) {
			$navigation[WEBROOT.'files/docs/'] = array('icon' => 'icons/documentation.png', 'label' => 'Documentation folder');
		}
		// UnitTests
		$navigation[WEBROOT.'tests/'] = array('icon' => WEBROOT.'icons/test.png', 'label' => 'Run TestSuite');

		// Modules
		$navigation[] = 'Modules';
		$sortedModules = $this->project->modules;
		ksort($sortedModules);
		foreach ($sortedModules as $name => $module) {
			if ($name != 'application' && $name != 'app') {
				$navigation[WEBROOT.$name.'/'] = array('icon' => WEBROOT.'module_icons/'.$name.'.png', 'label' => $module->name);
			}
		}
		$template = new Template('layout.php', array(
			'navigation' => new Nav($navigation, array('class' => 'nav nav-list')),
			'breadcrumbs' => $this->breadcrumbs,
			'contents' => $content,
				), array(
			'css' => array(
				WEBROOT.'mvc/css/bootstrap.css',
				WEBROOT.'mvc/css/bootstrap-theme.css',
				WEBROOT.'css/devutils.css',
			),
		));
		return $template;
	}

	function onSessionStart() {
		return false;
	}

	function addCrumb($crumb, $url = null) {
		$this->breadcrumbs->add($crumb, $url);
	}

	private function login() {
		$auth = new HttpAuthentication('DevUtils', function ($username, $password) {
			su_exec($username, $password, 'true', $retval);
			return ($retval === 0);
		});
		$credentials = $auth->authenticate();
		if ($credentials) {
			self::$username = $credentials['username'];
			self::$password = $credentials['password'];
			return true;
		}
		return false;
	}

	static function suExec($command, &$retval = null) {
		return su_exec(self::$username, self::$password, $command, $retval);
	}

	static function sudo($command) {
		return sudo(self::$username, self::$password, $command);
	}

}

?>
