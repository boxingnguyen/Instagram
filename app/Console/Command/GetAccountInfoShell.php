<?php
class GetAccountInfoShell extends AppShell {
	public function main() {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->account_info;
// 		$collection->drop();
		
		// readfile
		$file = fopen("/www/htdocs/PHPInstagram/app/Vendor/username.txt", "r");
		
		// read file line by line and assign into array
		while(!feof($file)){
			$line = fgets($file);
			$all_account[] = trim(preg_replace('/\s\s+/', ' ', $line));;
		}
		fclose($file);
		$count = 1;
		if (!empty($all_account)) {
			foreach ($all_account as $name) {
				$data = $this->__getAccountInfo($name);
				if (isset($data->user)) {
					$date_now = date('d M Y');
					$data->user->date_add_to_mongo = $date_now;
					$collection->insert($data->user);
					echo $count . ". " . $name . " completed!" . PHP_EOL;
					$count ++;
				} else {
					echo $name . " Failed !!!!!!!!!!!!!!!!!!!!!!!!!!" . PHP_EOL;
				}

			}
			echo "Total documents: " . $collection->count();
		}
	}
	
	private function __getAccountInfo($username) {
		$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/?__a=1');
		return $data;
	}
}