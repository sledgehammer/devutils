<?php
namespace SledgeHammer;
/**
 * Run a shell command as another user.
 *
 * @param string $username
 * @param string $password
 * @param string $command
 * @return string|false
 */
function suexec($username, $password, $command) {
		$commandline = escapeshellcmd(APPLICATION_DIR.'suexec.sh').' '.escapeshellarg($username).' '.escapeshellarg($password).' '.escapeshellarg($command);
		$output = shell_exec($commandline);
		$pos = strpos($output, $command);
		if ($pos) {
			return trim(substr($output, $pos + strlen($command) + 1, -2));
		}
		return false;
	}
?>