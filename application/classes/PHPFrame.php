<?php
/**
 * PHPFrame, een Component dat een url inlaad en direct laat zien.
 *
 * Een soort iframe principe, maar dan wordt de html direct in document gezet.
 */
namespace SledgeHammer;
class PHPFrame extends Object implements View {

	private
		$url;

	/**
	 *
	 * @param $url absolute url, deze wordt namelijk vanuit dit script ingelezen
	 */
	function __construct($url) {
		$this->url = $url;
	}

	function render() {
		// De output buffers flushen zodat de uitvoer direct getoond wordt.
		while (ob_get_level() > 0) {
			ob_end_flush();
		}
		$fp = fopen($this->url, 'r');
		if ($fp) {
			$bufferSize = 25; // Na elke X karakters de uitvoer doorsturen.
			while(!feof($fp)) {
				echo fgets($fp, $bufferSize);
				flush();
			}
			fclose($fp);
		}
	}
}
?>