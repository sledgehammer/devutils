<?php
/**
 * A custom SimpleTest Reporter, that also shows successful passes.
 * @package DevUtils
 */
class DevUtilsReporter extends HtmlReporter {

	function paintFail($message) {
		SimpleScorer::paintFail($message);
        print "<span class=\"fail\">Fail</span> ";
        print$this->htmlEntities($message) . "<br />\n";
    }

	function paintPass($message) {
        parent::paintPass($message);
        print "<span class=\"pass\">Pass</span> ";
        print  $this->htmlEntities($message) . "<br />\n";
	}

	protected function getCss() {
		return ".fail { background-color: red; color: white; padding: 0 9px 0 2px; }" .
               ".pass { background-color: green; color: white; padding: 0 2px; }" .
               " pre { background-color: lightgray; color: inherit; }";
    }

	private function shortpath($path) {
		$len = strlen(PATH);
		if (substr($path, 0, $len) == PATH) {
			return substr($path, $len);
		}
		return $path;
	}
}
?>
