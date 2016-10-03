<?php
class GetAccountInfoShell extends AppShell {
	public $m;
	public $db;
	const ACCOUNT_GET = "account_info";
	const ACCOUNT_ORIGIN = "account_info";
	
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
		// check if any account is missing
		$check_acc = $this->__checkAccount($this->db->{self::ACCOUNT_GET}->count());
		$i = 0;
		while (!$check_acc && $i < 3) {
			// re-get account (maximum 3 times)
			$this->__reGetAccount();
			$check_acc = $this->__checkAccount($this->db->{self::ACCOUNT_GET}->count());
			$i++;
		}
		if (!$check_acc) {
			// print out account which is missing
			$this->__listMissingAccount();
		}
		
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

/**
 * Check if cannot get account info
 * @param int $total_account
 * @return boolean
 */
	private function __checkAccount($account_get) {
		$total_account = $this->db->{self::ACCOUNT_ORIGIN}->count();
		if($total_account == $account_get) {
			return true;
		} else {
			return false;
		}
	}

/**
 * Re-get account if any account is missing
 */
	private function __reGetAccount() {
		// list account after get info from instagram
		$acc_get = $this->db->{self::ACCOUNT_GET}->find(array(), array('username' => true));
		// list account we can get
		foreach ($acc_get as $value) {
			$username_get[] = $value['username'];
		}
		// list account after register
		$acc_origin = $this->db->{self::ACCOUNT_ORIGIN}->find(array(), array('username' => true));
		foreach ($acc_origin as $value) {
			$username_origin[] = $value['username'];
		}
		// we find out which accounts are missed
		$acc_missed = array_diff($acc_origin, $acc_get);
		// get data of missed accounts
		foreach ($acc_missed as $name) {
			$data = $this->__getAccountInfo($name);
			if (isset($data->user)) {
				$result[] = $data->user;
				echo "Re-get " . $name . " completed!" . PHP_EOL;
				$count ++;
			} else {
				echo $name . " Re-get Failed !!!!!!!!!!!!!!!!!!!!!!!!!!" . PHP_EOL;
			}
		}
		// insert account's data
		$this->db->{self::ACCOUNT_GET}->batchInsert($result);
	}
	
/**
 * Print out missing account
 */
	private function __listMissingAccount() {
		// list account after get info from instagram
		$acc_get = $this->db->{self::ACCOUNT_GET}->find(array(), array('username' => true));
		// list account we can get
		foreach ($acc_get as $value) {
			$username_get[] = $value['username'];
		}
		// list account after register
		$acc_origin = $this->db->{self::ACCOUNT_ORIGIN}->find(array(), array('username' => true));
		foreach ($acc_origin as $value) {
			$username_origin[] = $value['username'];
		}
		// we find out which accounts are missed
		$acc_missed = array_diff($acc_origin, $acc_get);
		echo "List of missed accounts: ";
		foreach ($acc_missed as $name) {
			print_r($name) . ' | ';
		}
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