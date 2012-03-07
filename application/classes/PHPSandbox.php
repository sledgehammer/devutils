<?php
namespace SledgeHammer;
/**
 * Run phpcode in a separate process
 *
 * @package DevUtils
 */
class PHPSandbox extends Object implements View {

	/**
	 * @var string PHP source code
	 */
	private $code;
	/**
	 * @var resource proc_open() Resouce
	 */
	private $process;
	/**
	 * @var resource The input stream of the php process (write)
	 */
	private $stdin;
	/**
	 * @var resource The output stream of the php process (read)
	 */
	private $stdout;
	/**
	 * @var resource The errro stream of the php process (read)
	 */
	private $stderr;

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

		$this->process = proc_open('php', $descriptorspec, $pipes, NULL, NULL);

		if ($this->process === false) {
			warning('Failed to run php in a separate process');
			return;
		}
		// $pipes now looks like this:
		// 0 => writeable handle connected to child stdin
		// 1 => readable handle connected to child stdout
		// 2 => readable handle connected to child stderr

		$this->stdin = $pipes[0];
		$this->stdout = $pipes[1];
		$this->stderr = $pipes[2];

		// De phpcode naar het php proces sturen
		fwrite($this->stdin, $this->code);
		fclose($this->stdin);
		$errors = '';
		// De uitvoer uitlezen en weergeven
		while (!feof($this->stdout)) {
			$read = array($this->stdout, $this->stdout);
			if (stream_select($read, $write, $except, 30)) {
				foreach ($read as $stream) {
					if ($stream === $this->stdout) {
						echo fgets($this->stdout, 100);
						flush();
					} else {
						$errors .= fgets($this->stderr, 100);
					}
				}
			}
		}
		fclose($this->stdout);
		// De uitvoer van het error kanaal uitlezen ern weergeven
		$errors .= stream_get_contents($this->stderr);
		fclose($this->stderr);
		$return_value = proc_close($this->process);
		if ($errors) {
			echo '<h3>PHP Errors</h2>';
			if ($return_value !== 0) {
				echo ' exit() status: '.$return_value;
			}
			echo '<pre class="alert alert-error">', $errors, '</pre>';
		}
	}
}

?>