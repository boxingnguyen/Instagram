<?php
class GetAccountInfoShell extends AppShell {
	public function main() {
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
					echo $count . ". " . $name . " completed!" . PHP_EOL;
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
			// indexing
			echo "Indexing..." . PHP_EOL;
			$collection->createIndex(array('id' => true), array($option = array('background' => true)));
			echo "Indexing completed!" . PHP_EOL;
			echo "Total documents: " . $collection->count();
			//save follows_by
			$this->__saveFollows();
		}
	}
	
	private function __getAccountInfo($username) {
		$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/?__a=1');
		return $data;
	}
	
	private function __saveFollows() {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->account_info;
		$follows = $db->follows;
		$currentDate = date('Y-m-d');

		$data = $collection->find(array(), array('id' => 1, 'followed_by.count' => 1));
		if(isset($data) && $data->count() > 0) {
			foreach($data as $val) {
				$val['time'] = $currentDate;
				$follows->insert($val);
			}
		}
	}
	
}