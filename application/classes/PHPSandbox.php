<?php
/**
 * Een script in een eigen thread/process laten draaien
 *
 * @package DevUtils
 */

class PHPSandbox extends Object implements Component {

	private
		$php_code,

		$process,
		$stdin,
		$stdout,
		$stderr;

	function __construct($php_code) {
		$this->php_code = $php_code;
	}

	function render() {
		if ($this->start()) {
			// Output buffering uitzetten
			while (ob_get_level() > 0) {
				ob_end_flush(); 
			}
			// De phpcode naar het php proces sturen
			fwrite($this->stdin, $this->php_code);
			fclose($this->stdin);
			// De uitvoer uitlezen en weergeven
			while(!feof($this->stdout)) {
				echo fgets($this->stdout, 100);
				flush();
			}
			fclose($this->stdout);
			// De uitvoer van het error kanaal uitlezen ern weergeven
			$errors = stream_get_contents($this->stderr);
			fclose($this->stderr);
			$return_value = proc_close($this->process);
			if ($errors) {
				echo '<div class="errorblock"><h2 style="margin:0">PHP Errors</h2>';
				if ($return_value !== 0) {
					echo ' exit() status: '.$return_value;
				}
				echo '<pre style="color:red">'.$errors.'</pre>';
				echo '</div>';
			}
		}
	}

	private function start() {
		$descriptorspec = array(
			0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
			1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
			2 => array('pipe', 'w')
		);

		$this->process = proc_open('php', $descriptorspec, $pipes, NULL, NULL);

		if (is_resource($this->process)) {
			// $pipes now looks like this:
			// 0 => writeable handle connected to child stdin
			// 1 => readable handle connected to child stdout
			// Any error output will be appended to /tmp/error-output.txt

			$this->stdin = $pipes[0];
			$this->stdout = $pipes[1];
			$this->stderr = $pipes[2];
			return true;
		}
		return false;
	}

	function __destruct() {
		// It is important that you close any pipes before calling
		// proc_close in order to avoid a deadlock
		if (is_resource($this->process)) {
			proc_close($this->process);
		}
	}
}
?>
