 <?php
class GetDataShell extends Shell {
	public function main() {
		// get pid of exist process
		$pid_file = ROOT . '/app/tmp/logs/pid.lock';
		if (file_exists($pid_file)) {
			// get pid in pid.lock file
			$pid = file_get_contents ($pid_file);
			// check if that pid is running
			if (file_exists('/proc/' . $pid)) {
				// kill exist process which has mongo connection
				exec('kill ' . $pid);
			}
		}

		// re-connect mongo connection
		$output = exec(ROOT . '/app/Console/cake Quy ' . '> /dev/null 2>&1 & echo $!;');
		// empty file if it is empty, create empty file if it is not exist
		file_put_contents ($pid_file, '');
		// write PID of above process to pid.lock
		file_put_contents ($pid_file, $output);
	}
}