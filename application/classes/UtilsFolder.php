<?php
/**
 * Genereert een SimpleTest TestSuites
 *
 * @package DevUtils
 */
namespace SledgeHammer;
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
		//getDocument()->title = $util->title;
		Util::$module = $this->module;
		$component = $util->generateContent();
		if (is_valid_component($component)) {
			return new ComponentHeaders($component, array(
				'title' => $util->title,
			), true);
		}
		warning(get_class($util).'->generateContent() didn\'t return a Component');
		return new HttpError(500);
	}
}
?>
