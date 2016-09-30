<?php
class GetAccountInfoShell extends AppShell {
	public function main() {
		$time_start = microtime(true);
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->account_info;
		
		// readfile
		$file = fopen(APP."Vendor/username.txt", "r");
		
		// read file line by line and assign into array
		while(!feof($file)){
			$line = fgets($file);
			$all_account[] = trim(preg_replace('/\s\s+/', ' ', $line));;
		}
		fclose($file);
		$count = 1;
		$result = array();
		if (!empty($all_account)) {
			foreach ($all_account as $name) {
				$data = $this->__getAccountInfo($name);
				if (isset($data->user)) {
					$date_now = date('d M Y');
					$result[] = $data->user;
					echo $count . ". Account " . $name . " completed!" . PHP_EOL;
					$count ++;
				} else {
					echo $name . " Failed !!!!!!!!!!!!!!!!!!!!!!!!!!" . PHP_EOL;
				}
			}
			// drop old data
			$collection->drop();
			echo "Inserting into mongo..." . PHP_EOL;
			// insert new data
			$collection->batchInsert($result);
			// reconnect mongo and re-insert if insert unsuccessfully
			while (!$collection) {
				exec('sudo service mongod restart');
				$m = new MongoClient();
				$db = $m->instagram_account_info;
				$collection = $db->account_info;
				$collection->batchInsert($result);
			}
			// indexing
			echo "Indexing account_info ..." . PHP_EOL;
			$collection->createIndex(array('id' => 1));
			echo "Indexing account_info completed!" . PHP_EOL;
			echo "Total documents: " . $collection->count() . PHP_EOL;
			// save follows_by
			$this->__saveFollows();
			$time_end = microtime(true);
			echo "Time to get all account: " . ($time_end - $time_start) . " seconds" . PHP_EOL;
		}
	}
	
	private function __getAccountInfo($username) {
		$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/?__a=1');
		return $data;
	}
	
}