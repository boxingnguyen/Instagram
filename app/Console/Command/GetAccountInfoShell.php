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
		
		foreach ($all_account as $name) {
			$data = $this->__getAccountInfo($name);
			if (isset($data->user)) {
				$result[] = $data->user;
				echo $count . ". Account " . $name . " completed!" . PHP_EOL;
				$count ++;
			} else {
				echo $name . " Failed !!!!!!!!!!!!!!!!!!!!!!!!!!" . PHP_EOL;
			}
		}
		$this->db->{self::ACCOUNT_GET}->drop();
		echo "Inserting into mongo..." . PHP_EOL;
		// insert new data
		$this->db->{self::ACCOUNT_GET}->batchInsert($result);
		
		// indexing
		echo "Indexing account_info ..." . PHP_EOL;
		$this->db->{self::ACCOUNT_GET}->createIndex(array('id' => 1));
		echo "Indexing account_info completed!" . PHP_EOL;
		echo "Total documents: " . $this->db->{self::ACCOUNT_GET}->count() . PHP_EOL;
		// save follows_by
		$this->__saveFollows();
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

	private function __saveFollows() {
		$follows = $this->db->follows;
		$currentDate = date('Y-m-d');

		$data = $this->db->{self::ACCOUNT_GET}->find(array(), array('id' => 1, 'followed_by.count' => 1));
		
		if(isset($data) && $data->count() > 0) {
			foreach($data as $val) {
				$dataFollow = $follows->find(array('id' => $val['id'], 'time' => $currentDate));
				if($dataFollow->count() > 0){
					$follows->update(array(), array('$set' => array('follows' => $val['followed_by']['count'])));
				} else {
					$val['follows'] = $val['followed_by']['count'];
					$val['time'] = $currentDate;
					unset($val['followed_by']);
					$follows->insert($val);
				}
			}
		}
	}
}