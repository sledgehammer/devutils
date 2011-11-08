<?php
/**
 * Geeft een gestylede versie van de phpinfo() weer.
 *
 * @package DevUtils
 */
namespace SledgeHammer;
class PHPInfo extends Object implements Component {

	function getHeaders() {
		return array(
			'title' => 'PHP info',
			'css' => WEBROOT.'css/phpinfo.css',
		);
	}

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
