<?php
/**
 * 
 * @package DevUtils
 */

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
		$contents = file_get_contents(URL::info('scheme').'://'.URL::info('host').str_replace('%2F', '/', rawurlencode($this->getPath())).'rewrite_check.html');
		if ($contents != 'Apache Rewrite module is enabled') { // Is het openen van de rewrite_check.html mislukt?
			return new HTML('<h1>Error loading "/rewrite_check.html"</h1>&quot;AllowOverride All&quot; is required in your httpd.conf for this &lt;Directory&gt;<hr />');
		}
		if (!$this->project) {
			return new MessageBox('error', 'Geen project gevonden', 'Controleer de stappen in "devutils/docs/installatie.txt"');
		}
		$iconPrefix = WEBROOT.'icons/';
		// Utilities
		$utilityList = array();
		foreach ($this->project->modules as $moduleID => $module) {
			foreach ($module->getUtilities() as $utilFilename => $util) {
				$utilityList[$moduleID.'/utils/'.$utilFilename] = array('icon' => $iconPrefix.$util->icon, 'label' => $util->title);
			}
		}
		$utilityList['phpinfo.html'] = array('icon' => $iconPrefix.'php.gif', 'label' => 'PHP Info');
		// Documentation
		$documentationList = array(
			'phpdocs.html' => array('icon' => $iconPrefix.'documentation.png', 'label' => 'API Documentation')
		);
		if (file_exists($this->project->path.'docs/')) {
			$documentationList['files/docs/'] = array('icon' => $iconPrefix.'documents.png', 'label' => 'Documentation');
		}
		// Modules
		$moduleList = array();
		foreach ($this->project->modules as $name => $module) {
			if ($name != 'application') {
				$moduleList[$name.'/'] = array('icon' => WEBROOT.'module_icons/'.$name.'.png', 'label' => $module->name);
			}
		}
		// Unittests
		$tests = $this->project->getUnitTests();
		$unittestList['tests/'] = array('icon' => $iconPrefix.'accept.png', 'label' => 'Run TestSuite');
		foreach ($tests as $testfile) {
			$unittestList['tests/'.$testfile] = array('icon' => $iconPrefix.'test.png', 'label' => substr($testfile, 0, -4));
		}
		$template = new Template('project.html', array(
			'utilities' => new ActionList($utilityList),
			'documentation' => new ActionList($documentationList),
			'modules' => new ActionList($moduleList),
			'unittests' => new ActionList($unittestList),
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
			render_file($favicon);
			exit;
		}
		return $this->onFolderNotFound();
	}


	function flush_phpdocs() {
		$count = rmdirs(PATH.'tmp/phpdocs/');
		return new MessageBox('ok.gif', 'Flushing PhpDocumentor files', $count.' files deleted');
	}


	/**
	 * Als er geen bestand in de module_icons map staat voor de opgegeven module, geeft dan het standaard icoon weer
	 */
	function module_icons_folder() {
		return new FileDocument(PATH.'application/public/images/icons/module.png');
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

	function  generateContent() {
		if ($this->project) {
			Breadcrumbs::add($this->project->name.' project', $this->getPath());
		}
		return parent::generateContent();
	}

	/*
	function login() {
		if (isset($_SESSION['devutils_login']) &&  $_SESSION['devutils_login'] == 'LOGGED_IN') {
			return true;
		}
		//Input::build('requiredtext', 'username');
		$Form = new Form(array(), array(
			new Fieldset('DevUtils Login', array(
				'username' => new FieldLabel('Username', Input::build('required text', 'username', array('class' => 'firstfocus'))),
				'password' => new FieldLabel('Password', new Input('password', 'password')),
				new Input('submit', NULL, array('value' => 'Login', 'style' => 'float:right;margin-top:10px')),
			), array('style' => 'width:310px'))
		));
		$values = $Form->import($error);
		if ($values) { // Is het formulier succesvol geimporteerd?
			$credentials = $values[0];
			if ($credentials['username'] == 'dev' && $credentials['password'] == 'utils') { // Zijn de credentials correct?
				$_SESSION['devutils_login'] = 'LOGGED_IN';
				return true;
			}
		}
		$GLOBALS['Document']->javascript(get_public_html().'js/jquery.js');
		$GLOBALS['Document']->javascript(get_public_html().'js/devutils.js');
		$GLOBALS['Viewports']['menu'] = new HTML('&nbsp;');
		$GLOBALS['Viewport'] = $Form; // Toon het inlog formulier
		return false;
	}

	private function buildMenu() {
		$prefix = $this->getPath();
		$menu = array(
			'Projects' => array(NULL, 'project.png', 'items' => array()),
			'PHPInfo' => array($prefix.'phpinfo.html', 'php.gif'),
		);
		$projects = $this->get_project_links();
		foreach ($projects as $url => $project) {
			$menu['Projects']['items'][$project['label']] = array($url, $project['icon']);
		}
		if (count($menu['Projects']['items']) == 0) {
			unset($menu['Projects']);
		}
		return new JSCookMenu($menu, 'hbr');
	}

	function handlssseFile($filename) {
		if ($filename == 'rewrite_check.html') { // Voor de rewrite_check.html is geen inloggen niet nodig
			if (!$this->login()) { // Controleer of er is ingelogd of verwerk de log in procedure
				return false; // Er is nog niet ingelogd
			}
		}
	}

	function handlsseFolder($folder, $filename) {
		$Command = new PhpMetaAdmin;
		$Command->execute();
	}
*/
	protected function wrapContent($content) {
		$icon = false;
		$properties = false;
		if ($this->project) {
			if ($this->project->getFavicon()) {
				$icon = true;
			}
			// Properties
			if ($this->project->application) {
				$properties = new DefinitionList($this->project->getProperties());
			}
		}
		$template = new Template('layout.html', array(
			'icon' => $icon,
			'properties' => $properties,
			'breadcrumbs' => new Breadcrumbs,
			'contents' => $content,
		), array(
			//'title' => 'DevUtils',
			'css' => WEBROOT.'stylesheets/devutils.css',
		));
		return $template;
	}

	function onSessionStart() {
		return false;
	}
}
?>
