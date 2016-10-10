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
		$all_account = $acc_missing = array();
		foreach ($acc_origin as $acc) {
			$all_account[] = $acc['username'];
		}
		$count = 1;
		$date  = date('dmY');
		$myfile = fopen(APP."Vendor/Data/".$date.".acc.json", "w+") or die("Unable to open file!");
		foreach ($all_account as $name) {
			$data = $this->__getAccountInfo($name);
			if (isset($data->user)) {
				fwrite($myfile, json_encode($data->user)."\n");
				echo $count . ". Account " . $name . " completed!" . PHP_EOL;
				$count ++;
			} else {
				// store missing account into an array
				$acc_missing[] = $name;
				echo $name . " Failed !!!!!!!!!!!!!!!!!!!!!!!!!!" . PHP_EOL;
			}
		}
		fclose($myfile);
		// check account if all account is got
		$checkAcc = $this->__checkAccount($date);
		$checkAccCount = 0;
		// re-get missing account (maximum 5 times)
		while (!$checkAcc && $checkAccCount < 5) {
			$checkAcc = $this->__reGetAccount($acc_missing, $date);
		}
		// save account info into db
		$this->__saveIntoDb($date);
		
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
	
	private function __checkAccount($date) {
		$filename = APP . "Vendor/Data/" . $date . ".acc.json";
		$fp = file($filename);
		$lines = count($fp);
			
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->account_username;
		$total_acc = $collection->count();

		$miss_count = $total_acc - $lines;
		if ($miss_count == 0){
			return true;
		} else {
			return false;
		}
	}
	
	private function __reGetAccount($acc_missing, $date) {
		$myfile = fopen(APP."Vendor/Data/".$date.".acc.json", "a") or die("Unable to open file!");
		foreach ($acc_missing as $name) {
			$data = $this->__getAccountInfo($name);
			if (isset($data->user)) {
				fwrite($myfile, json_encode($data->user)."\n");
				echo $count . ". Re-getttttt account " . $name . " completed!" . PHP_EOL;
				$count ++;
			} else {
				echo $name . " Re-gettttt failed !!!!!!!!!!!!!!!!!!!!!!!!!!" . PHP_EOL;
			}
		}
		fclose($myfile);
		return $this->__checkAccount($date);
	}
	
	private function __saveIntoDb($date) {
		// name of file which store account info's data
		$filename = APP."Vendor/Data/".$date.".acc.json";
		$file = fopen($filename, "r");
		$data = array();
		if ($file) {
			while (($line = fgets($file)) !== false) {
				// store media into an array
				$data[] = json_decode($line);
			}
			fclose($file);
		}
		// drop exist collection
		$this->db->{self::ACCOUNT_GET}->drop();
		echo "Inserting into mongo..." . PHP_EOL;
		// insert new data
		$this->db->{self::ACCOUNT_GET}->batchInsert($data, array('timeout' => -1));
	
		// indexing
		echo "Indexing account_info ..." . PHP_EOL;
		$this->db->{self::ACCOUNT_GET}->createIndex(array('id' => 1));
		echo "Indexing account_info completed!" . PHP_EOL;
		echo "Total documents: " . $this->db->{self::ACCOUNT_GET}->count() . PHP_EOL;
	}
}