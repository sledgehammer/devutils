<?php
/**
 * Form
 */
namespace Sledgehammer;
/**
 * Generate and import a Form
 * @package MVC
 */
class Form extends HtmlElement implements Import {

	/**
	 * @var string
	 */
	public $legend;

	/**
	 * @var array
	 */
	public $fields = array();

	/**
	 * @var array
	 */
	public $actions = array();

	/**
	 * @var bool
	 */
	public $fieldset = true;

	/**
	 * @var string
	 */
	public $tag = 'form';

	/**
	 * @var array
	 */
	protected $attributes = array(
		'method' => 'post'
	);

	public function __construct($options) {
		if (is_indexed($options)) {
			$options = array('fields' => $options);
		}
		parent::__construct($options);
	}

	function initial($values) {
		foreach ($values as $field => $value) {
			$this->fields[$field]->initial($value);
		}
	}

	function import(&$error, $request = null) {
		if ($request === null) {
			if (strtolower($this->getAttribute('method')) === 'post') {
				$request = $_POST;
			} elseif (strtolower($this->getAttribute('method')) === 'get') {
				$request = $_GET;
			} else {
				notice('Invalid import method');
				$request = $_REQUEST;
			}
		}
		if (count($request) == 0) {
			$error = false;
			return null;
		}
		$data = array();
		foreach ($this->fields as $key => $field) {
			$name = $field->getAttribute('name');
			if ($name === null) {
				continue;
			}
			$data[$name] = $field->import($fieldError, $request);
			if ($fieldError) {
				$error[$field->name] = $fieldError;
			}
		}
		if (count($error)) {
			return null;
		}
		return $data;
	}

	function renderContents() {
		echo "\n";
		if (array_value($this->attributes, 'class') === 'form-horizontal') {
			$renderControlGroups = true;
		} else {
			$renderControlGroups = false;
		}
		if ($this->fieldset) {
			echo "<fieldset>\n";
			if ($this->legend !== null) {
				echo "\t<legend>", Html::escape($this->legend), "</legend>\n";
			}
		}

		// Render form fields
		foreach ($this->fields as $label => $field) {
			echo "\t";
			if ($renderControlGroups) {
				echo '<div class="control-group">';
				if (is_int($label) === false) {
					echo '<label class="control-label">', Html::escape($label), '</label>';
				}
				echo '<div class="controls">';
				render($field);
				echo "</div></div>";
			} else {
				if (is_int($label) === false) {
					echo '<label>', Html::escape($label), '</label>';
				}
				render($field);
			}
			echo "\n";
		}

		// Render form actions
		if (count($this->actions) !== 0) {
			echo '<div class="form-actions">';
			foreach ($this->actions as $name => $action) {
				if (is_string($name)) {
					if (is_array($action) === false) {
						$action = array(
							'label' => $action
						);
					}
					$action['name'] = $name;
				}
				echo new Button($action);
			}
			echo '</div>';
		}
		if ($this->fieldset) {
			echo "</fieldset>\n";
		}
	}

}

?>
