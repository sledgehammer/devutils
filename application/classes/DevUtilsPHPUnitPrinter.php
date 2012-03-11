<?php
/**
 * A PHPUnit_Util_Printer for rendering PHPUnit results in html.
 * (Also shows successful passes)
 *
 * @package DevUtils
 */
class DevUtilsPHPUnitPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener {

	private
		$failCount = 0,
		$exceptionCount = 0,
		$passCount = 0,
		$pass;

	public function addError(PHPUnit_Framework_Test $test, Exception $e, $time) {
		$this->exceptionCount++;
		$this->pass = false;
		echo '<div class="assertion">';
		echo "<span class=\"fail label label-important\">Error</span> ";
		echo htmlentities($e->getMessage());
		echo "</div>\n";
		flush();
	}


	public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) {
		$this->failCount++;
		$this->pass = false;
//		$trace = $this->_getStackTrace($message);
		$testName = get_class($test) . '(' . $test->getName() . ')';

		echo '<div class="assertion">';
		echo "<span class=\"fail label label-important\">Fail</span> ";
		echo $this->htmlentities($e->getMessage());
		echo '<br />In '.$testName;
//		echo $trace;
		echo "</div>\n";
		flush();
	}

	public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) {

	}

	public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) {

	}




	public function startTest(PHPUnit_Framework_Test $test) {
		$this->pass = true;
	}

	public function endTest(PHPUnit_Framework_Test $test, $time) {
		if ($this->pass) {
			$this->passCount++;
			echo '<div class="assertion">';
			echo "<span class=\"pass label label-success\">Pass</span> ";
			echo $this->htmlentities($test->getName());
			echo "</div>\n";
			flush();
		}
	}

	public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {
		$title = $suite->getName();
		if (isset($GLOBALS['title'])) {
			$title = $GLOBALS['title'];
		}
		echo '<h2 class="unittest_heading">'.$title.' <span class="label">Running tests</span></h2>';
		echo '<div class="assertions">';
		flush();
	}

	public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {
		echo "</div>\n";
		$alert_suffix = ($this->failCount + $this->exceptionCount > 0 ? "" : " alert-success");
		echo '<div class="unittest_summary alert'.$alert_suffix.'">';
		echo $suite->count();
		echo " test cases:\n";
		echo "<strong>".$this->passCount."</strong> passes, ";
		echo "<strong>".$this->failCount."</strong> fails and ";
		echo "<strong>".$this->exceptionCount."</strong> exceptions.";
		echo "</div>\n";
	}

	private function htmlentities($string) {
		if (preg_match('/^This test printed output:/', $string)) {
			return $string;
		}
		return htmlentities($string, ENT_NOQUOTES, \SledgeHammer\Framework::$charset);
	}
}

?>
