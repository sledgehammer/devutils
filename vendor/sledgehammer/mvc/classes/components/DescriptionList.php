<?php
/**
 * DescriptionList
 */
namespace Sledgehammer;
/**
 * A Description list <dl><dt><dd></dl>
 *
 * Tip: Use 'class' => 'dl-horizontal' for a different layout
 *
 * @package MVC
 */
class DescriptionList extends Object implements View {

	private $items;
	private $attributes;

	function __construct($items, $attributes = array()) {
		$this->items = $items;
		$this->attributes = $attributes;
	}

	function render() {
		echo Html::element('dl', $this->attributes, true);
		foreach($this->items as $label => $values) {
			echo "\t";
			echo Html::element('dt', array(), $label);
			if (is_array($values)) {
				foreach ($values as $value) {
					echo Html::element('dd', array(), $value);
				}
			} else {
				echo Html::element('dd', array(), $values);
			}
		}
		echo '</dl>'."\n";
	}
}
?>
