<?php
/**
 * Jsonp
 */
namespace Sledgehammer;
/**
 * Renders the data as in a Jsonp wrapper
 *
 * @package MVC
 */
class Jsonp extends Json {

	/**
	 * @var string  The (javascript)function call for the jsonp response.
	 */
	private $callback;

	/**
	 * @param mixed  $data      The data to be sent as json
	 * @param string $callback  The (javascript)function call for the jsonp response, if no callback is given a normal json s
	 * @param string $charset   The encoding used in $data, use null for autodetection. Assume UTF-8 by default
	 */
	function __construct($callback, $data, $charset = 'UTF-8') {
		parent::__construct($data, array(), $charset);
		$this->callback = $callback;
		$this->headers['http']['Content-Type'] = 'text/javascript; charset=utf-8';
	}

	/**
	 * Render the $data as jsonp
	 */
	function render() {
		echo $this->callback, '(';
		parent::render();
		echo ');';
	}

}

?>