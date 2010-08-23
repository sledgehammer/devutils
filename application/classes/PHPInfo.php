<?php
/**
 * Geeft een gestylede versie van de phpinfo() weer.
 *
 * @package DevUtils
 */

class PHPInfo extends Object implements Component {

	function render() {
		ob_start();
		phpinfo();
		$phpinfo = ob_get_clean();
		$pos = strpos($phpinfo, '</table>');
		echo '<div id="phpinfo"><h2 style="font-size:14pt">PHP Version '.phpversion().'</h2>';
		echo substr($phpinfo, $pos + 8, -14); // <html> t/m de eerste tabel strippen.
	}
}
?>
