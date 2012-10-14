<?php
/**
 * Meganisme dat phpcode uit een ander projecten uitvoert
 *
 * @package DevUtils
 */
namespace Sledgehammer;
abstract class Util extends Object implements Controller {

	public static $module;
	public
		$icon = 'util.png',
		$title,
		$paths;

	function __construct($title, $icon = 'icons/util.png') {
		$this->title = $title;
		$this->icon = $icon;
		$this->paths = array(
			'utils' => self::$module->path.'utils/',
			'modules' => dirname(self::$module->path).'/',
			'project' => dirname(dirname(self::$module->path)).'/',
		);
	}
}
?>
