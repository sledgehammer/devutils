<?php
/**
 * PHPSandbox
 */
namespace Sledgehammer;
/**
 * Run phpcode in a separate process.
 * @package DevUtils
 */
class PHPSandbox extends Object implements View {

	/**
	 * @var string PHP source code
	 */
	private $code;


	/**
	 *
	 * @param string $php string containing PHP code
	 */
	function __construct($php) {
		$this->code = $php;
	}

	function render() {
		$descriptorspec = array(
			0 => array('pipe', 'r'), // stdin
			1 => array('pipe', 'w'), // stdout
			2 => array('pipe', 'w')  // stderr
		);


		/* @var $process resource */
		$process = proc_open('php', $descriptorspec, $pipes, NULL, NULL);

		if ($process === false) {
			warning('Failed to run php in a separate process');
			return;
		}
		/* @var $stdin resource The input stream of the php process (write) */
		$stdin = $pipes[0];
		/* @var $stdout resource The output stream of the php process (read) */
		$stdout = $pipes[1];
		/* @var $stderr resource The error stream of the php process (read) */
		$stderr = $pipes[2];

		// De phpcode naar het php proces sturen
		fwrite($stdin, $this->code);
		fclose($stdin);
		$errors = '';
		// De uitvoer uitlezen en weergeven
		while (!feof($stdout)) {
			$read = array($stdout, $stderr);
			if (stream_select($read, $write, $except, 30)) {
				foreach ($read as $stream) {
					if ($stream === $stdout) {
						echo fgets($stdout, 100);
						flush();
					} else {
						$errors .= fgets($stderr, 100);
					}
				}
			}
		}
		fclose($stdout);
		// De uitvoer van het error kanaal uitlezen ern weergeven
		$errors .= stream_get_contents($stderr);
		fclose($stderr);
		$return_value = proc_close($process);
		if ($errors) {
			echo '<h3>PHP Errors</h2>';
			if ($return_value !== 0) {
				echo ' exit() status: '.$return_value;
			}
			echo '<pre class="alert alert-danger">', $errors, '</pre>';
		}
	}
}

?>