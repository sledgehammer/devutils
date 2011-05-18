<?php
/**
 * De Modules map, toon eigenschappen, toon unittests 
 *
 * @package DevUtils
 */

class ModuleFolder extends VirtualFolder {

	/**
	 * @var Module $Module Een Module OF een Project object
	 */
	protected
		$module;

	function __construct($module) {
		parent::__construct();
		$this->module = $module;
	}

	function index() {
		$properties = $this->module->getProperties();
		if (isset($properties['Owner_email'])) {
			$properties['Owner'] = htmlentities($properties['Owner']). ' &lt;<a href="mailto:'.$properties['Owner_email'].'">'.$properties['Owner_email'].'</a>&gt;';
			unset($properties['Owner_email']);
		}

//		$this->utils_viewport();
		return new Template('module.html', array(
			'module' => $this->module,
			'properties' => new DefinitionList($properties),
			'documentation' => new ActionList($this->getDocumentationList()),
		), array(
			'title' => $this->module->name.' module',
		));
	}

	function generateContent() {
		$suffix = (get_class($this->module) == 'Module') ? ' module' : ' project';
		$name = $this->module->name.$suffix;
		// getDocument()->title = $name;
		Breadcrumbs::add($name, $this->getPath());
		return parent::generateContent();
	}

	function phpdocs() {
		$command = new PhpDocs($this->module);
		return $command->generateContent();
	}

	function phpdocs_folder($filename) {
		$path = PhpDocs::documentation_path($this->module);
		if ($filename === false) {
			$filename = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen($this->getPath(true)));
		}
		return new FileDocument($path.$filename);
	}

	function files_folder() {
		Breadcrumbs::add('Files', $this->getPath(true));
		$command = new FileBrowser($this->module->path, array('show_fullpath' => true, 'show_hidden_files' => true));
		return $command->generateContent();
	}

	function utils_folder($filename) {
		$folder = new UtilsFolder($this->module);
		return $folder->generateContent();
	}

	/**
	 * 
	 * @return array
	 */
	protected function get_properties() {
		$module = $this->module;
		$properties = array(
			'Owner' => htmlentities($module->owner). ' &lt;<a href="mailto:'.$module->owner_email.'">'.$module->owner_email.'</a>&gt;',
			'Version' => $module->get_version(),
			'Revision' => $module->get_revision(),
		);
		if ($module->owner == '' && $module->owner_email == '') {
			unset($properties['Owner']);
		}
		if (!$properties['Version']) {
			unset($properties['Version']);
		}
		return $properties;
	}
/*
	protected function utils_viewport() {
		$actions = UtilsHook::get_action_list_array($this->module);
		if (count($actions) > 0) {
			$GLOBALS['Viewports']['utils'] =  new ActionList($actions);
		}
	}
*/
	protected function getDocumentationList() {
		$iconPrefix = WEBROOT.'icons/';
		$actions = array(
			'phpdocs.html' => array('icon' => $iconPrefix.'documentation.png', 'label' => 'API Documentation'),
		);
		if (file_exists($this->module->path.'docs/')) {
			$actions['files/docs/'] = array('icon' => $iconPrefix.'documents.png', 'label' => 'Documentation');
		}

		//'files/' => array('icon' => 'mime/folder.png', 'label' => 'Files'),
		return $actions;
	}

	/**
	 * 
	 * @return Component
	 */
	protected function getUnitTestList() {
		$tests = $this->module->getUnitTests();
		if (count($tests) == 0) {
			return false;
		}
		$list = array(
			'unittests/' => array('icon' => 'accept.png', 'label' => 'Run all UnitTests'),
		);
		foreach($tests as $testfile) {
			$list['unittests/'.$testfile] = array('icon' => 'test.png', 'label' => $testfile);
		}
		return new ActionList($list);
		/*
		$GLOBALS['Viewport'] = &$GLOBALS['Viewports']['unittests'];
		$Command = new UnitTests($this->module);
		$Command->build_overview('unittests/');
		*/
	}
}
?>
