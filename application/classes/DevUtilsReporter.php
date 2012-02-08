<?php

/**
 * A custom SimpleTest Reporter, that also shows successful passes.
 * @package DevUtils
 */
class DevUtilsReporter extends HtmlReporter {

	function paintPass($message) {
		SimpleScorer::paintPass($message);
		print '<div class="assertion">';
		print "<span class=\"pass label label-success\">Pass</span> ";
		print $this->htmlEntities($message);
		print "</div>\n";
		flush();
	}

	function paintFail($message) {
		SimpleScorer::paintFail($message);
		print '<div class="assertion">';
		print "<span class=\"fail label label-important\">Fail</span> ";
		print $this->htmlEntities($message);
		print "</div>\n";
		flush();
	}

	function paintError($message) {
        SimpleScorer::paintError($message);
		print '<div class="assertion">';
        print "<span class=\"fail label label-important\">Error</span> ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode(" -&gt; ", $breadcrumb);
        print " -&gt; <strong>" . $this->htmlEntities($message) . "</strong>";
		print "</div>\n";

    }

	function paintException($exception) {
		print '<div class="assertion">';
        SimpleScorer::paintException($exception);
        print "<span class=\"fail label label-important\">Exception</span> ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode(" -&gt; ", $breadcrumb);
        $message = 'Unexpected exception of type [' . get_class($exception) .
                '] with message ['. $exception->getMessage() .
                '] in ['. $exception->getFile() .
                ' line ' . $exception->getLine() . ']';
        print " -&gt; <strong>" . $this->htmlEntities($message) . "</strong>";
		print "</div>\n";

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
