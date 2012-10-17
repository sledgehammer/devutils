<?php
/**
 * DevUtilsPHPUnitPrinter
 */
use Sledgehammer\Html;
/**
 * A PHPUnit_Util_Printer for rendering PHPUnit results in html.
 * (Also shows successful passes)
 * @package DevUtils
 */
class DevUtilsPHPUnitPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener {

	static $failCount = 0;
	static $exceptionCount = 0;
	static $passCount = 0;
	static $skippedCount = 0;
	private $pass;
	private static $firstError = true;

	public function addError(PHPUnit_Framework_Test $test, Exception $e, $time) {
		self::$exceptionCount++;
		$this->pass = false;
		echo '<div class="unittest-assertion">';
		echo "<span class=\"label label-important\" data-unittest=\"fail\">Error</span> ";
		echo '<b>', $this->translateException($e), '</b>: ', Html::escape($e->getMessage()), '<br />';
		$this->trace($test, $e, 'contains an error');
		echo "</div>\n";
		flush();
	}

	public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) {
		self::$failCount++;
		$this->pass = false;

		echo '<div class="unittest-assertion">';
		echo "<span class=\"label label-important\" data-unittest=\"fail\">Fail</span> ";
		$type = get_class($e);
		switch ($type) {
			case 'PHPUnit_Framework_OutputError':
				echo $e->getMessage();
				break;
			case 'PHPUnit_Framework_ExpectationFailedException':
				echo $e->getMessage();
				if ($e->getComparisonFailure() !== null) {
					$diff = $e->getComparisonFailure()->getDiff();
					if ($diff !== '') {
						echo '<pre>', $diff, '</pre>';
					}
				}
				break;
			default:
				echo Html::escape($e->getMessage());
				break;
		}
		echo '<br />';
		$this->trace($test, $e, 'failed');
		echo "</div>\n";
		flush();
	}

	public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
		echo '<div class="unittest-assertion">';
		echo "<span class=\"label\" data-unittest=\"incomplete\">Incomplete</span> ";
		echo Html::escape($e->getMessage()), '<br />';
		echo '<b>'.get_class($test).'</b>-&gt;<b>'.$test->getName().'</b>() was incomplete<br />';
		echo '</div>';
	}

	public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
		self::$skippedCount++;

		echo '<div class="unittest-assertion">';
		echo "<span class=\"label label-info\"  data-unittest=\"skipped\">Skipped</span> ";
		echo Html::escape($e->getMessage()), '<br />';
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
			echo '<div class="unittest-assertion">';
			echo "<span class=\"label label-success\" data-unittest=\"pass\">Pass</span> ";
			echo get_class($test), '->', $test->getName(), '() is successful';

			echo "</div>\n";
			flush();
		}
	}

	public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {
		echo '<div class="unittest">';
		static $first = true;
		if ($first) {
			$first = false;
		} else {
			$url = false;
			$filename = \Sledgehammer\Framework::$autoloader->getFilename($suite->getName());
			if (substr($filename, 0, strlen(\Sledgehammer\MODULES_DIR)) === \Sledgehammer\MODULES_DIR) {
				$filename = substr($filename, strlen(Sledgehammer\MODULES_DIR));
				if (preg_match('@^(?<module>[^/]+)/tests/(?<file>[^/]+\.php)$@', $filename, $matches)) {
					$url = DEVUTILS_WEBPATH.'tests/'.$matches['module'].'/'.$matches['file'];
				}
			}
			if ($url) {
				echo '<h3><a href="'.$url.'">'.$suite->getName().'</a></h3>';
			} else {
				echo '<h3>'.$suite->getName().'</h3>';
			}
		}
	}

	public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {
		echo "</div>\n";
		flush();
	}

	static function summary() {
		$alert_suffix = (self::$failCount + self::$exceptionCount > 0 ? "" : " alert-success");
		echo '<div class="unittest-summary alert'.$alert_suffix.'">';
		echo "<strong>".self::$passCount."</strong> passes, ";
		echo "<strong>".self::$failCount."</strong> fails and ";
		echo "<strong>".self::$exceptionCount."</strong> exceptions.";
		echo "</div>\n";
	}

	private function trace(PHPUnit_Framework_Test $test, Exception $e, $suffix = '') {
		if (self::$firstError && ($e instanceof PHPUnit_Framework_SkippedTestError) === false) {
			echo '<b>'.get_class($test).'</b>-&gt;<b>'.$test->getName().'</b>() '.$suffix.'<br />';
			report_exception($e);
			self::$firstError = false;
			return;
		}
		$file = $e->getFile();
		$line = $e->getLine();
		if (substr(get_class($e), 0, 8) === 'PHPUnit_') {
			$phpunitPath = 'PHPUnit'.DIRECTORY_SEPARATOR.'Framework'.DIRECTORY_SEPARATOR;
			$proxyFiles = array(
				Sledgehammer\Framework::$autoloader->getFilename('Sledgehammer\Object'),
				Sledgehammer\Framework::$autoloader->getFilename('Sledgehammer\ErrorHandler'),
			);
			$backtrace = $e->getTrace();
			foreach ($backtrace as $index => $call) {
				if (empty($call['line'])) {
					continue;
				}
				if (strpos($call['file'], $phpunitPath)) {
					continue;
				}
				if (in_array($call['file'], $proxyFiles)) {
					continue;
				}
				$file = $call['file'];
				$line = $call['line'];
				break;
			}
		}
		echo '<b>'.get_class($test).'</b>-&gt;<b>'.$test->getName().'</b>() '.$suffix.'<br />';
		echo '<b>', Html::escape(get_class($e)), '</b>  thrown in <b>', $file, '</b> on line <b>'.$line, '</b>';
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
			return '<span title="'.Html::escape($class).'">'.$name.'</span>';
		}
		return $class;
	}

}

?>
