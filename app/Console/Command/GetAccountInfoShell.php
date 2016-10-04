<?php
class GetAccountInfoShell extends AppShell {
	public $m;
	public $db;
	const ACCOUNT_GET = "account_info";
	const ACCOUNT_ORIGIN = "account_username";
	
	public function initialize() {
		$this->m = new MongoClient;
		$this->db = $this->m->instagram_account_info;
	} 
	
	public function main() {
		$time_start = microtime(true);
		// get all instagram's username
		$acc_origin = $this->db->{self::ACCOUNT_ORIGIN}->find(array(), array('username' => true));
		foreach ($acc_origin as $acc) {
			$all_account[] = $acc['username'];
		}
		$count = 1;
		$result = array();
		$date  = date('dmY');
		$myfile = fopen(APP."Vendor/Data/".$date.".acc.json", "w+") or die("Unable to open file!");
		foreach ($all_account as $name) {
			$data = $this->__getAccountInfo($name);
			if (isset($data->user)) {
				fwrite($myfile, json_encode($data->user)."\n");
				$result[] = $data->user;
				echo $count . ". Account " . $name . " completed!" . PHP_EOL;
				$count ++;
			} else {
				echo $name . " Failed !!!!!!!!!!!!!!!!!!!!!!!!!!" . PHP_EOL;
			}
		}
		fclose($myfile);
		$this->db->{self::ACCOUNT_GET}->drop();
		echo "Inserting into mongo..." . PHP_EOL;
		// insert new data
		$this->db->{self::ACCOUNT_GET}->batchInsert($result);
		
		// indexing
		echo "Indexing account_info ..." . PHP_EOL;
		$this->db->{self::ACCOUNT_GET}->createIndex(array('id' => 1));
		echo "Indexing account_info completed!" . PHP_EOL;
		echo "Total documents: " . $this->db->{self::ACCOUNT_GET}->count() . PHP_EOL;
		
		$time_end = microtime(true);
		echo "Time to get all account: " . ($time_end - $time_start) . " seconds" . PHP_EOL;
	}

/**
 * Get data of instagram's account
 * @param string $username
 * @return object account's data
 */
	private function __getAccountInfo($username) {
		$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/?__a=1');
		return $data;
	}
}