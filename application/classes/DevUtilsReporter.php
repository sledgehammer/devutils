<?php

/**
 * A custom SimpleTest Reporter, that also shows successful passes.
 * @package DevUtils
 */
class DevUtilsReporter extends HtmlReporter {

	function paintFail($message) {
		SimpleScorer::paintFail($message);
		print '<div class="assertion">';
		print "<span class=\"fail label label-important\">Fail</span> ";
		print $this->htmlEntities($message);
		print "</small></div>\n";
		flush();
	}

	function paintPass($message) {
		parent::paintPass($message);
		print '<div class="assertion">';
		print "<span class=\"pass label label-success\">Pass</span> ";
		print $this->htmlEntities($message);
		print "</small></div>\n";
		flush();
	}

	function paintHeader($test) {
		$this->sendNoCacheHeaders();
		print '<h1 class="unittest_heading">'.$test.' <span class="label">Running tests</span></h1>';
		print '<div class="assertions">';
		flush();
	}

	function paintFooter($test) {
		print "</div>\n";
		$alert_suffix = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "" : " alert-success");
		print '<div class="unittest_summary alert'.$alert_suffix.'">';
		print $this->getTestCaseProgress()."/".$this->getTestCaseCount();
		print " test cases complete:\n";
		print "<strong>".$this->getPassCount()."</strong> passes, ";
		print "<strong>".$this->getFailCount()."</strong> fails and ";
		print "<strong>".$this->getExceptionCount()."</strong> exceptions.";
		print "</div>\n";
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
