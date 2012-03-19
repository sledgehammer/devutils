<?php
namespace SledgeHammer;
/**
 * The DevUtils Application FrontController
 *
 * @package DevUtils
 */
class DevUtilsWebsite extends Website {

	private $project;

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
			return new HTML('<h1>Error loading "/rewrite-check.html"</h1>&quot;AllowOverride All&quot; is required in your httpd.conf for this &lt;Directory&gt;<hr />');
		}
		if (!$this->project) {
			return MessageBox::error('Geen project gevonden', 'Controleer de stappen in "devutils/docs/installatie.txt"');
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
		$tests = $this->project->getUnitTests();
		foreach ($tests as $testfile) {
			$unittestList['tests/'.$testfile] = array('icon' => 'unittest', 'label' => substr($testfile, 0, -4));
		}
		$template = new Template('project.php', array(
					'project' => $this->project->name,
					'properties' => new DescriptionList($properties, array('class' => 'property-list')),
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

	function project_icon() {
		$favicon = $this->project->getFavicon();
		if ($favicon) {
			return new FileDocument($favicon);
		}
		return new FileDocument(APPLICATION_DIR.'public/icons/project.png');
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
		$webpath = preg_replace('/\/.+$/', '', substr($_SERVER['REQUEST_URI'], strlen(WEBPATH)));
		$skipLogin = in_array($webpath, array('project_icon.ico', 'rewrite-check.html', 'module_icons'));
		if ($skipLogin === false && $this->login() == false) {
			$doc = new HTMLDocument();
			$doc->content = new HttpError(401);
			return $doc;
		}
		return parent::generateDocument();
	}

	function generateContent() {
		if ($this->project) {
			//$this->project->name.' project'
			Breadcrumbs::add(array('icon' => 'home', 'label' => 'Home'), $this->getPath());
		}
		return parent::generateContent();
	}

	function wrapContent($content) {
		if (!$this->project) {
			return $content;
		}

		$navigation = array(
			'Application',
			WEBPATH => array('icon' => WEBPATH.'project_icon.ico', 'label' => $this->project->name),
		);
		// Documentation
		$navigation[WEBROOT.'phpdocs/'] = array('icon' => 'icons/documentation.png', 'label' => 'API Documentation');
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

	private function login() {
		$auth = new HttpAuthentication('SledgeHammer DevUtils');
		$credentials = $auth->import($error);
		if ($credentials) {
			$whoami = suexec($credentials['username'], $credentials['password'], 'whoami');
			if ($whoami !== false) {
				return true;
			}
			$auth->reset();
		}
		return false;
	}

	static function suexec($command) {
		$auth = new HttpAuthentication(null);
		$credentials = $auth->import($errorMessage);
		return suexec($credentials['username'], $credentials['password'], $command);
	}

}

?>
