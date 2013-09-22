<?php
/**
 * Dialog
 */
namespace Sledgehammer;
/**
 * A dialog popup with where selected choice is posted back the server.
 * Compatible with Bootrap 3 `.modal-dialog`
 *
 * @package MVC
 */
class Dialog extends Object implements View, Import {

	private $title;
	private $body;
	private $choices;
	private $identifier = 'answer';
	private $method = 'post';
	private $close = false;

	/**
	 *
	 * @param string $title
	 * @param string $body html
	 * @param array $choices
	 * @param array $options [optional]
	 */
	function __construct($title, $body, $choices = array(), $options = array()) {
		$this->title = $title;
		$this->body = $body;
		$this->choices = $choices;
		foreach ($options as $option => $value) {
			$this->$option = $value;
		}
	}

	function initial($default) {
		$indexed = is_indexed($this->choices);
		if ($indexed) {
			$key = array_search($default, $this->choices);
			if ($key === false) {
				foreach ($this->choices as $index => $choice) {
					if (is_array($choice) && $choice['label'] === $default) {
						$key = $index;
						break;
					}
				}
			}
		} else {
			$key = $default;
		}
		if ($key !== false) {
			if (is_array($this->choices[$key]) === false) {
				$this->choices[$key] = array(
					'label' => $this->choices[$key]
				);
			}
			$this->choices[$key]['class'] = 'btn btn-primary';
		}
 	}

	function import(&$error, $request = null) {
		if ($request === null) {
			$request = $_POST;
		}
		if (extract_element($request, $this->identifier, $answer) == false) {
			$error = false;
			return null;
		}
		$indexed = is_indexed($this->choices);
		if ($indexed) {
			if (in_array($answer, $this->choices)) {
				return $answer;
			}
			foreach ($this->choices as $choice) {
				if (is_array($choice) && $choice['label'] === $answer) {
					return $answer;
				}
			}
		} elseif (isset($this->choices[$answer])) {
			return $answer;
		}
		$error = 'Unexpected anwser "'.$answer.'", expecting "'.implode(', ', array_keys($this->choices)).'"';
			return null;
	}

	function getHeaders() {
		return array(
			'title' => $this->title
		);
	}

	function render() {
		echo "<div class=\"modal-dialog\">\n";
		echo "\t<div class=\"modal-content\">\n";
		echo "\t\t<div class=\"modal-header\">";
		echo '<h4 class="modal-title">';
		if ($this->close) {
			echo '<button class="close" data-dismiss="modal">&times;</button>';
		}
		echo Html::escape($this->title), "</h4></div>\n";
		echo "\t\t<div class=\"modal-body\">\n\t\t\t", $this->body, "\n\t\t</div>\n";
		if (count($this->choices) !== 0) {
			echo "\t\t<form class=\"modal-footer\" action=\"".Url::getCurrentURL()."\" method=\"".$this->method."\">\n";
			$indexed = is_indexed($this->choices);
			foreach (array_reverse($this->choices) as $answer => $choice) {
				if (is_array($choice) === false) {
					$choice = array('label' => $choice);
				}
				$choice['type'] = 'submit';
				$choice['name'] = $this->identifier;
				if ($indexed) {
					$choice['value'] = $choice['label'];
				} else {
					$choice['value'] = $answer;
				}
				$button = new Button($choice);
				echo "\t\t\t", $button, "\n";
			}
			echo "\t\t</form>\n";
		}
		echo "\t</div>\n";
		echo "</div>\n";
	}

}

?>