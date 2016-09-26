 <?php
class GetMediaShell extends AppShell {
	public function main() {
		
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->media;
		
		$all_account = array();

		$file = fopen(APP."Vendor/username.txt", "r");

		while(!feof($file)){
		    $line = fgets($file);
		    $all_account[] = trim(preg_replace('/\s\s+/', ' ', $line));;
		}
		fclose($file);
		
		if (!empty($all_account)) {
			// drop old data
			$collection->drop();
			// we get data of 25 accounts at a time
			$account_chunks = array_chunk($all_account, 25);
			foreach ($account_chunks as $account) {
				foreach ($account as $name) {
					// create 2 processes here
					$pid = pcntl_fork();
					if ($pid == -1) {
						die('could not fork');
					} else if ($pid) {
						// we are the parent
						// collect process id to know when children complete
						$pids[] = $pid;
					} else {
						// we are the child
						$max_id = null;
						echo "Get media of " . $name . PHP_EOL;
						$data = $this->__getMedia($name, $max_id);
						do {
							$data = $this->__getMedia($name, $max_id);
							// insert to mongo
							if(isset($data->items) && !empty($data->items)) {
								$collection->batchInsert($data->items);
								$max_id = end($data->items)->id;
							} else {
								break;
							}
						}
						while (isset ($data->more_available) && ($data->more_available == true || $data->more_available == 1));
						// Jump out of loop in this child. Parent will continue.
						echo "Get media of " . $name . " completed!" . PHP_EOL;
						exit;
					}
				}
				foreach ($pids as $pid) {
					pcntl_waitpid($pid, $status);
					unset($pids[$pid]);
				}
			}
			// indexing
			echo "Indexing media ..." . PHP_EOL;
			$collection->createIndex(array('user.id' => 1), array($option = array('timeout' => -1)));
			$collection->createIndex(array('created_time' => 1), array($option = array('timeout' => -1)));
			echo "Indexing media completed!" . PHP_EOL;
			echo "Total documents: " . $collection->count();
		}
	}
	
	private function __getMedia($username, $max_id = null) {
		if ($max_id != null) {
			$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/media/?max_id=' . $max_id);
		} else {
			$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/media/');
		}
		return $data;
	}
}