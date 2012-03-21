<?php
/**
 *
 * @package DevUtils
 */
namespace SledgeHammer;
class UtilScript extends Util {

	private
		$script,
		$arguments;

	/**
	 * @param array $arguments Beinvloed de de $argv en $argc variable
	 */
	function __construct($script, $title, $icon = 'icons/script.png', $arguments = false) {
		$this->script = $script;
		$this->arguments = $arguments;
		parent::__construct($title, $icon);
	}

	function generateContent() {
		return $this;
	}

	function render() {
		echo  '<h2>Running '.$this->script.'</h2>';
		echo '<pre class=\"utilscript well\">';
		$arguments = array(
			escapeshellarg($this->paths['utils'].$this->script)
		);
		if ($this->arguments !== false) {
			foreach ($this->arguments as $argument) {
				$arguments[] = escapeshellarg($argument);
			}
		}
		DevUtilsWebsite::sudo('php '.implode(' ', $arguments));
		echo '</pre>';
	}
}
?>
