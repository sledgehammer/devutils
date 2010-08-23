<?php
/**
 * Genereert een SimpleTest TestSuites
 *
 * @package DevUtils
 */

class UtilsFolder extends VirtualFolder {

	private
		$module;

	function __construct($module) {
		parent::__construct();
		$this->handle_filenames_without_extension = true;
		$this->module = $module;
	}

	function dynamicFilename($filename) {
		$utils = $this->module->getUtilities();
		$util = $utils[$filename];
		Breadcrumbs::add($util->title);
		getDocument()->title = $util->title;
		Util::$module = $this->module;
		return $util->execute();
	}
}
?>
