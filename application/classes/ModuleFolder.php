<?php
/**
 * ModuleFolder
 * @package DevUtils
 */
namespace Sledgehammer;
/**
 * De Modules map, toon eigenschappen, toon unittests
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
			$properties['Owner'] = htmlentities($properties['Owner']).' &lt;<a href="mailto:'.$properties['Owner_email'].'">'.$properties['Owner_email'].'</a>&gt;';
			unset($properties['Owner_email']);
		}
		$utilityList = array();
		foreach ($this->module->getUtilities() as $utilFilename => $util) {
			$utilityList['utils/'.$utilFilename] = array('icon' => $util->icon, 'label' => $util->title);
		}
		if ($utilityList) {
			$utilities = new Nav($utilityList, array('class' => 'nav nav-list'));
		} else {
			$utilities = false;
		}
		return new Template('module.php', array(
					'module' => $this->module,
					'properties' => new DescriptionList($properties, array('class' => 'dl-horizontal')),
					'documentation' => $this->getDocumentationList(),
					'utilities' => $utilities,
					'unittests' => $this->getUnitTestList(),
						), array(
					'title' => $this->module->name.' module',
				));
	}

	function generateContent() {
		$name = $this->module->name.' module';
		// getDocument()->title = $name;
		$this->addCrumb($name, $this->getPath());
		return parent::generateContent();
	}

	function phpdocs_folder() {
		$folder = new PhpDocs($this->module);
		return $folder->generateContent();
	}

	function files_folder() {
		$this->addCrumb('Files', $this->getPath(true));
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
			'Owner' => htmlentities($module->owner).' &lt;<a href="mailto:'.$module->owner_email.'">'.$module->owner_email.'</a>&gt;',
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

	protected function getDocumentationList() {
		$iconPrefix = WEBROOT.'icons/';
		$actions = array(
			'phpdocs/' => array('icon' => $iconPrefix.'documentation.png', 'label' => 'API Documentation'),
		);
		if (file_exists($this->module->path.'docs/')) {
			$actions['files/docs/'] = array('icon' =>  $iconPrefix.'documentation.png', 'label' => 'Documentation folder');
		}
		//'files/' => array('icon' => 'mime/folder.png', 'label' => 'Files'),
		return new Nav($actions, array('class' => 'nav nav-list'));
	}

	/**
	 *
	 * @return View
	 */
	protected function getUnitTestList() {
		$tests = $this->module->getUnitTests();
		if (count($tests) == 0) {
			return new HTML('<span class="muted">No tests found</span>');
		}
		$iconPrefix = WEBROOT.'icons/';
		$list = array(
			WEBROOT.'tests/'.$this->module->identifier.'/' => array('icon' => 'play', 'label' => 'Run all'),
		);
		foreach ($tests as $testfile) {
			if (text($testfile)->endsWith('Test.php')) {
				$label = substr($testfile, 0, -8);
			} else {
				$label = substr($testfile, 0, -4);
			}
			$list[WEBROOT.'tests/'.$this->module->identifier.'/'.$testfile] = array('icon'=> $iconPrefix.'test.png', 'label' => $label);
		}

		return new Nav($list, array('class' => 'nav nav-list'));
	}

}

?>
