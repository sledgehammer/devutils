<?php
/**
 *
 * @package DevUtils
 */

class UtilScript extends Util {

	private
		$script,
		$arguments;

	/**
	 * @param array $arguments Beinvloed de de $argv en $argc variable
	 */
	function __construct($script, $title, $icon = 'script.png', $arguments = false) {
		$this->script = $script;
		$this->arguments = $arguments;
		parent::__construct($title, $icon);
	}

	function generateContent() {
		$php = '<?php echo "<pre>\\n"; ';
		if ($this->arguments !== false) {
			$argv = $this->arguments;
			array_unshift($argv, 'UtilScript_PHPSandbox');
			$php .=  '$argv = unserialize("'.addslashes(serialize($argv))."\");\n".'$argc = '.count($argv).";\n";
		}
		$php .= 'include("'.$this->paths['utils'].$this->script.'"); echo "\\n<pre>"; ?>';
		return new PHPSandbox($php);
	}
}
?>
