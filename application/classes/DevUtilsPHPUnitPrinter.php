<?php

use SledgeHammer\HTML;

/**
 * A PHPUnit_Util_Printer for rendering PHPUnit results in html.
 * (Also shows successful passes)
 *
 * @package DevUtils
 */
class DevUtilsPHPUnitPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener {

	static $failCount = 0;
	static $exceptionCount = 0;
	static $passCount = 0;
	static $skippedCount = 0;
	private $pass;

	public function addError(PHPUnit_Framework_Test $test, Exception $e, $time) {
		self::$exceptionCount++;
		$this->pass = false;
		echo '<div class="assertion">';
		echo "<span class=\"fail label label-important\">Error</span> ";
		echo '<b>', $this->translateException($e), '</b>: ', HTML::escape($e->getMessage()), '<br />';
		$this->trace($test, $e, 'contains an error');
		echo "</div>\n";
		flush();
	}

	public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) {
		self::$failCount++;
		$this->pass = false;

		echo '<div class="assertion">';
		echo "<span class=\"fail label label-important\">Fail</span> ";
		$type = get_class($e);
		if ($type === 'PHPUnit_Framework_OutputError') {
			echo $e->getMessage();
		} else {
			echo HTML::escape($e->getMessage());
		}
		echo '<br />';
		$this->trace($test, $e, 'failed');
		echo "</div>\n";
		flush();
	}

	public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
		echo '<div class="assertion">';
		echo "<span class=\"incomplete label\">Incomplete</span> ";
		echo HTML::escape($e->getMessage()), '<br />';
		echo '<b>'.get_class($test).'</b>-&gt;<b>'.$test->getName().'</b>() was incomplete<br />';
		echo '</div>';
	}

	public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
		self::$skippedCount++;

		echo '<div class="assertion">';
		echo "<span class=\"skipped label label-info\">Skipped</span> ";
		echo HTML::escape($e->getMessage()), '<br />';
		$this->trace($test, $e, 'was skipped');
		echo "</div>\n";
		flush();
	}

	public function startTest(PHPUnit_Framework_Test $test) {
		$this->pass = true;
	}

	public function endTest(PHPUnit_Framework_Test $test, $time) {
		if ($this->pass) {
			self::$passCount++;
			echo '<div class="assertion">';
			echo "<span class=\"pass label label-success\">Pass</span> ";
			echo get_class($test), '->', $test->getName(), '() is successful';

			echo "</div>\n";
			flush();
		}
	}

	public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {
		static $first = true;
		if ($first) {
			$first = false;
		} else {
			echo '<h3 class="testsuite-heading">'.$suite->getName().'</h3>';
		}
		echo '<div class="assertions">';
	}

	public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {
		echo "</div>\n";
		flush();
	}

	static function summary() {
		$alert_suffix = (self::$failCount + self::$exceptionCount > 0 ? "" : " alert-success");
		echo '<div class="unittest_summary alert'.$alert_suffix.'">';
		echo "<strong>".self::$passCount."</strong> passes, ";
		echo "<strong>".self::$failCount."</strong> fails and ";
		echo "<strong>".self::$exceptionCount."</strong> exceptions.";
		echo "</div>\n";
	}

	private function trace(PHPUnit_Framework_Test $test, Exception $e, $suffix = '') {
		echo '<b>'.get_class($test).'</b>-&gt;<b>'.$test->getName().'</b>() '.$suffix.'<br />';
		$errorHandlerPath = SledgeHammer\Framework::$autoLoader->getFilename('SledgeHammer\ErrorHandler');
		$backtrace = $e->getTrace();
		foreach ($backtrace as $index => $call) {
			if (empty($call['line'])) {
				continue;
			}
			if (isset($call['class']) && $call['class'] === 'PHPUnit_Framework_Assert') {
				$file = $backtrace[$index + 1]['file'];
				$line = $backtrace[$index + 1]['line'];
				break;
			}
			if (isset($call['class']) && (substr($call['class'], 0, 8) === 'PHPUnit_' || in_array($call['class'], array('ReflectionMethod', 'SledgeHammer\ErrorHandler')))) {
				continue;
			}
			if (isset($call['file']) && $call['file'] === $errorHandlerPath) {
				continue;
			}
			$file = $call['file'];
			$line = $call['line'];
			break;
		}
		echo '<b>', HTML::escape(get_class($e)), '</b>  thrown in <b>', $file, '</b> on line <b>'.$line, '</b>';
	}

	private function translateException(Exception $e) {
		$class = get_class($e);
		$name = $class;
		if ($e instanceof PHPUnit_Framework_Error) {
			if ($class === 'PHPUnit_Framework_Error') {
				$name = 'Error';
			} else {
				$name = substr($class, 24);
			}
		}
		if ($name !== $class) {
			return '<span title="'.HTML::escape($class).'">'.$name.'</span>';
		}
		return $class;
	}

}

?>
