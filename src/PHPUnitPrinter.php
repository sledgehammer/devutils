<?php

namespace Sledgehammer\Devutils;

use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Error;
use PHPUnit_Framework_SkippedTestError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_TestListener;
use PHPUnit_Framework_TestSuite;
use PHPUnit_Util_Printer;
use Exception;
use Sledgehammer\Core\Html;

/**
 * A PHPUnit_Util_Printer for rendering PHPUnit results in html.
 * (Also shows successful passes).
 */
class PHPUnitPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    public static $failCount = 0;
    public static $exceptionCount = 0;
    public static $passCount = 0;
    public static $skippedCount = 0;
    private $pass;
    private static $firstError = true;
    private $groups = array();

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        ++self::$exceptionCount;
        $this->pass = false;
        echo '<div class="unittest-assertion">';
        echo '<span class="label label-danger" data-unittest="fail">Error</span> ';
        echo '<b>', $this->translateException($e), '</b>: ', Html::escape($e->getMessage()), '<br />';
        $this->trace($test, $e, 'contains an error');
        echo "</div>\n";
        flush();
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        ++self::$failCount;
        $this->pass = false;

        echo '<div class="unittest-assertion">';
        echo '<span class="label label-danger" data-unittest="fail">Fail</span> ';
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
                        echo '<pre>', htmlentities($diff), '</pre>';
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

    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        echo '<div class="unittest-assertion">';
        echo '<span class="label label-warning" data-unittest="risky">Risky</span> ';
        echo Html::escape($e->getMessage()), '<br />';
        echo '<b>'.get_class($test).'</b>-&gt;<b>'.$test->getName().'</b>() is risky<br />';
        echo '</div>';
    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        echo '<div class="unittest-assertion">';
        echo '<span class="label label-warning" data-unittest="incomplete">Incomplete</span> ';
        echo Html::escape($e->getMessage()), '<br />';
        echo '<b>'.get_class($test).'</b>-&gt;<b>'.$test->getName().'</b>() was incomplete<br />';
        echo '</div>';
    }

    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        ++self::$skippedCount;

        echo '<div class="unittest-assertion">';
        echo '<span class="label label-info"  data-unittest="skipped">Skipped</span> ';
        echo Html::escape($e->getMessage()), '<br />';
        $this->trace($test, $e, 'was skipped');
        echo "</div>\n";
        flush();
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->pass = true;
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($this->pass) {
            ++self::$passCount;
            echo '<div class="unittest-assertion">';
            echo '<span class="label label-success" data-unittest="pass">Pass</span> ';
            echo get_class($test), '->', $this->groupLink($test), '() is successful';

            echo "</div>\n";
            flush();
        }
    }

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->groups = $suite->getGroupDetails();
        echo '<div class="unittest">';
        static $first = true;
        $name = $suite->getName();
        if ($first) {
            $first = false;
        } else {
            $url = false;
            if (strpos($name, '::') === false) {
                $class = new \ReflectionClass($name);
                $filename = $class->getFileName();
                $relPath = preg_replace('/^'.preg_quote(DEVUTILS_PACKAGE_PATH, '/').'/', '', $filename);
                if ($relPath !== $filename) {
                    $url = DEVUTILS_TEST_URL.$relPath;
                }
            }
            if ($url) {
                echo '<h3><a href="'.$url.'">'.$name.'</a></h3>';
            } else {
                echo '<h3>'.$name.'</h3>';
            }
        }
        if (ob_get_level()) {
            ob_flush();
        }
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        echo "</div>\n";
        flush();
    }

    public static function summary()
    {
        $alert_suffix = (self::$failCount + self::$exceptionCount > 0 ? '' : ' alert-success');
        echo '<div class="unittest-summary alert'.$alert_suffix.'">';
        echo '<strong>'.self::$passCount.'</strong> passes, ';
        echo '<strong>'.self::$failCount.'</strong> fails and ';
        echo '<strong>'.self::$exceptionCount.'</strong> exceptions.';
        echo "</div>\n";
    }

    private function trace(PHPUnit_Framework_Test $test, Exception $e, $suffix = '')
    {
        if (self::$firstError && ($e instanceof PHPUnit_Framework_SkippedTestError) === false) {
            echo '<b>'.get_class($test).'</b>-&gt;<b>'.$this->groupLink($test).'</b>() '.$suffix.'<br />';
            // Sledgehammer\Core\Debug\ErrorHandler::instance() = false;
            report_exception($e);
            self::$firstError = false;

            return;
        }
        $file = $e->getFile();
        $line = $e->getLine();
        if (substr(get_class($e), 0, 8) === 'PHPUnit_') {
            $phpunitPath = 'PHPUnit'.DIRECTORY_SEPARATOR.'Framework'.DIRECTORY_SEPARATOR;
            $proxyFiles = array(
                // Sledgehammer\Framework::$autoloader->getFilename('Sledgehammer\Object'),
                // Sledgehammer\Framework::$autoloader->getFilename('Sledgehammer\ErrorHandler'),
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

    private function translateException(Exception $e)
    {
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

    /**
     * When the test is in a group create a link for re-running only that group.
     *
     * @param PHPUnit_Framework_Test $test
     */
    private function groupLink(PHPUnit_Framework_TestCase $test)
    {
        $method = $test->getName();
        if (count($this->groups) > 1) {
            foreach ($this->groups as $group => $tests) {
                if ($group === 'default') {
                    continue;
                }
                if (in_array($test, $tests, true)) {
                    return Html::element('a', array('href' => '?group='.urlencode($group)), $method);
                }
            }
        }

        return $method;
    }
}
