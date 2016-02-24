<?php
/**
 * PHPInfo
 */
namespace Sledgehammer\Devutils;
/**
 * phpinfo() with TwBootstrap styling
 * @package DevUtils
 */
class PHPInfo extends Object implements View {

	function getHeaders() {
		return array(
			'title' => 'PHP Version '.phpversion(),
		);
	}

	function render() {
		echo '<div class="phpinfo"><h2>PHP Version '.phpversion().'</h2>';
		ob_start();
		phpinfo();
		$phpinfo = ob_get_clean();
		$pos = strpos($phpinfo, '</table>');
		$html = substr($phpinfo, $pos + 8, -14); // strip default styling.
		$html = str_replace('<table ', '<table class="table table-striped table-condensed" ', $html);
		echo $html;
	}
}
?>