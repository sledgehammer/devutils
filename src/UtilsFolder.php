<?php
namespace Sledgehammer\Devutils;

use Sledgehammer\Mvc\Component\HttpError;
use Sledgehammer\Mvc\ViewHeaders;
use Sledgehammer\Mvc\VirtualFolder;
/**
 * Run utilities from inside a module folder.
 * @package DevUtils
 */
class UtilsFolder extends VirtualFolder {

	private $module;

	function __construct($module) {
		parent::__construct();
		$this->handle_filenames_without_extension = true;
		$this->module = $module;
	}

	function dynamicFilename($filename) {
		$utils = $this->module->getUtilities();
		$util = $utils[$filename];
		$this->addCrumb($util->title, false);
		Util::$module = $this->module;
		$component = $util->generateContent();
		if (\Sledgehammer\is_valid_view($component)) {
			return new ViewHeaders($component, array(
				'title' => $util->title,
			), true);
		}
		warning(get_class($util).'->generateContent() didn\'t return a Component');
		return new HttpError(500);
	}
}
?>
