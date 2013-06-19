<?php
/**
 * Nav
 */
namespace Sledgehammer;
/**
 * Nav, tabs, and pills Highly customizable list-style navigation.
 *
 * .nav-list: OS X Finder/iTunes style navigation.
 * .nav-stacked: Vertical tabs or pills.
 *
 * @package MVC
 */
class Nav extends HtmlElement {

	/**
	 * @var array
	 */
	public $items;

	/**
	 * @var string
	 */
	public $tag = 'ul';

	/**
	 * Attributes for the ul element.
	 * @var array
	 */
	protected $attributes = array(
		'class' => 'nav',
	);

	/**
	 * Constructor
	 * @param array $items format: array(url => label, ...) of array(url => array('icon' => icon_url, 'label' => label))
	 * @param $options array
	 */
	function __construct($items, $options = array()) {
		$this->items = $items;
		// Set attributes and properties
		foreach ($options as $option => $value) {
			if (property_exists($this, $option)) {
				$this->$option = $value;
			} else {
				$this->attributes[$option] = $value;
			}
		}
	}

	/**
	 * Render the items.
	 */
	function renderContents() {
		foreach ($this->items as $url => $action) {
			if (is_int($url) && is_string($action)) {
				echo "\t<li class=\"nav-header\">".Html::escape($action)."</li>\n";
			} else {
				echo "\t<li><a href=\"".$url.'">';
				if (is_array($action)) { //  has an icon?
					echo Html::icon($action['icon']), ' ', Html::escape($action['label']);
				} else {
					echo Html::escape($action);
				}
				echo "</a></li>\n";
			}
		}
	}

	/**
	 * Build simple stacked navs, great for sidebars
	 * @param type $items
	 * @param array $options
	 * @return \Sledgehammer\Nav
	 */
	static function lists($items, $options = array()) {
		$options['nav'] = 'list';
		return new Nav($items, $options);
	}

	static function tabs($items, $options = array()) {
		$options['nav'] = 'tabs';
		return new Nav($items, $options);
	}

	static function pills($items, $options = array()) {
		$options['nav'] = 'pills';
		return new Nav($items, $options);
	}

}

?>