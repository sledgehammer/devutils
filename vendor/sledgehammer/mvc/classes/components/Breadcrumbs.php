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
class Breadcrumbs extends Object implements View {

	protected $active = null;
	protected $divider = '/';
	/**
	 * Format: array(array('url' => url, 'label' => label))
	 * @var array
	 */
	protected $crumbs = array();

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

	function render() {
		echo Html::element('ul', $this->attributes, true), "\n";
		$count = count($this->crumbs);
		$i = 0;
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
			$i++;
			if ($i !== $count) {
				echo ' <span class="divider">', $this->divider, '</span>';
			}
			echo "</li>\n";
		}
		echo "</ul>\n";
	}

}

?>
