<?php
/**
 * Breadcrumb
 */
namespace Sledgehammer;
/**
 * Breadcrumb Navigation
 *
 * @package MVC
 */
class Breadcrumbs extends HtmlElement {

	public $active = null;
	/**
	 * Format: array(array('url' => url, 'label' => label))
	 * @var array
	 */
	public $crumbs = array();

	/**
	 * @var string
	 */
	public $tag = 'ul';
	/**
	 * Attributes for the html element.
	 * @var array
	 */
	protected $attributes = array(
		'class' => 'breadcrumb'
	);

	/**
	 * Constructor
	 * @param array $options
	 */
	function __construct($options = array()) {
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
	 *
	 * @param string|array $crumb
	 * @param string $url
	 */
	function add($crumb, $url = null) {
		if (is_array($crumb) == false) {
			$crumb = array(
				'label' => $crumb,
			);
		}
		$crumb['url'] = $url;
		$this->crumbs[] = $crumb;
	}

	function renderContents() {
		echo "\n";
		$count = count($this->crumbs);
		foreach ($this->crumbs as $crumb) {
			if ($crumb['url'] == false || value($crumb['active'])) {
				echo "\t<li class=\"active\">";
			} else {
				echo "\t<li>";
			}
			if (isset($crumb['icon'])) {
				$label = Html::icon($crumb['icon']).' '.Html::escape($crumb['label']);
			} else {
				$label = Html::escape($crumb['label']);
			}
			if ($crumb['url']) {
				echo Html::element('a', array('href' => $crumb['url']), $label);
			} else {
				echo $label;
			}
			echo "</li>\n";
		}
	}

}

?>
