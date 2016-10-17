 <?php
class GetMediaShell extends AppShell {
	
	public function main() {
		$start_time = microtime(true);
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->media;
		
		$all_account = $this->__sortAccountByMedia();
		$date = date("dmY");
		
		if (!empty($all_account)) {
			// drop old data
			$collection->drop();
			// empty file contain accounts that missed media
			file_put_contents(APP."Vendor/Data/tmp_missing_acc.json", "");
			
			// store data OF PUBLIC ACCOUNT into json file (if data is fully get, store data into db)
			$this->__saveDataPublic($all_account['public'], $collection, $date);
			
			// re-get media if media is missing (maximum 5 times, of public account)
			$this->__getMissingMedia($collection, $date, false);
			
			// empty file contain public accounts that missed media
			file_put_contents(APP."Vendor/Data/tmp_missing_acc.json", "");
			
			// store data OF PRIVATE ACCOUNT into json file (if data is fully get, store data into db)
			$this->__saveDataPrivate($all_account['private'], $collection, $date);
			
			// re-get media if media is missing (maximum 5 times, of private account)
			$this->__getMissingMedia($collection, $date, true);
			
			// indexing
			$this->__createIndex($collection);
		}
		$end_time = microtime(true);
		echo "Time to get all media: " . ($end_time - $start_time) . " seconds" . PHP_EOL;
	}
	
	private function __getMedia($username, $max_id = null) {
		$data = array();
		if(isset($username) && !empty($username) && is_string($username)){
			if ($max_id != null) {
				$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/media/?max_id=' . $max_id);
			} else {
				$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/media/');
			}
		}
		return $data;
	}
	
	private function __sortAccountByMedia() {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->account_info;
		
		$data = $collection->find()->sort(array('media.count' => -1))->fields(array('username' => true, 'media.count' => true, 'is_private' => true));
		$result = array();
		foreach ($data as $value) {
			if ($value['is_private'] == 1) {
				$result['private'][] = $value['username'];
			} else {
				$result['public'][] = $value['username'];
			}
		}
		return $result;
	}
	
	private function __checkMedia($name) {
		if (isset($name) && !empty($name)) {
			$date = date("dmY");
			$filename = APP . "Vendor/Data/" . $date . "." . $name . ".media.json";
			$fp = file($filename);
			$lines = count($fp);
			
			$m = new MongoClient();
			$db = $m->instagram_account_info;
			$collection = $db->account_info;
			$query = array('username' => $name);
			$result = $collection->find($query,array('media.count','media.nodes'));
			$total_media = 0;
			$timeMediaFirst = 0;
			foreach ($result as $v) {
				$total_media = $v['media']['count'];
				if(!empty($v['media']['nodes'])){
					$timeMediaFirst = $v['media']['nodes'][0]['date'];
				}
			}

			$miss_count = $total_media - $lines;
			if ($miss_count >= 0 && $miss_count <= 10 ) {
				$this->out ('0 <= miss <= 10 : ' . $miss_count . ' ~ ' . $name);
				return true;
			} elseif ($miss_count >= -10 && $miss_count < 0) {
				$this->out ('-10 <= miss < 0 : ' . $miss_count . ' ~ ' . $name);
				// remove data is over
				for ($i = 0; $i < 10; $i++) {
					$current_line = json_decode($fp[$i]);
					if (intval($current_line->created_time) > $timeMediaFirst) {
						unset($fp[$i]);
					}
				}
				file_put_contents($filename, implode("", $fp));
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	private function __reGetMedia($name, $date) {
		$max_id = null;
		$myfile = fopen(APP . "Vendor/Data/" . $date . "." . $name . ".media.json", "w+") or die("Unable to open file!");
		do {
			$data = $this->__getMedia($name, $max_id);
			// write data into json file
			if (isset($data->items) && !empty($data->items)) {
				foreach ($data->items as $val) {
					fwrite($myfile, json_encode($val)."\n");
				}
				$max_id = end($data->items)->id;
			} else {
				echo "Re-get data of: " . $name . PHP_EOL;
				print_r($data);
				break;
			}
		}
		while (isset ($data->more_available) && ($data->more_available == true || $data->more_available == 1));
		fclose($myfile);
		// re-check if media of this account is not missing anymore
		return $this->__checkMedia($name);
	}
	
	private function __reGetMediaPrivate($name, $date) {
		$m = new MongoClient;
		$db = $m->instagram_account_info;
		$collections = $db->account_username;
		
		$result = $collections->find(array('username' => $name));
		foreach ($result as $acc_info) {
			$id = $acc_info['id'];
			if (isset($acc_info['access_token'])) {
				$this->_insta->setAccessToken($acc_info['access_token']);
				$max_id = null;
				// write data into json file
				$myfile = fopen(APP."Vendor/Data/".$date.".".$name.".media.json", "w+") or die("Unable to open file!");
				do {
					$media = $this->_insta->getUserMedia($id, 2, $max_id);
					// if get media successfully and user has number of media > 0
					if (isset($media->data)) {
						foreach ($media->data as $val) {
							fwrite($myfile, json_encode($val)."\n");
						}
						if (isset($media->pagination) && !empty($media->pagination->next_max_id)) {
							$max_id = $media->pagination->next_max_id;
						} else {
							$max_id = null;
							break;
						}
					} else {
						// get media unsuccessfully or user has no media
						print_r($media);
						break;
					}
				} while ($max_id != null);
			} else {
				$this->out($acc_info['username'] . " does not have access token.");
				break;
			}
			fclose($myfile);
			// re-check if media of this account is not missing anymore
			return $this->__checkMedia($name);
		}
	}
	
	private function __saveIntoDb($name, $collection, $date) {
		$filename = APP . "Vendor/Data/" . $date . "." . $name . ".media.json";
		$all_lines = file($filename);
		if ($all_lines == 0) {
			echo $name . "has no media!" . PHP_EOL;
			return;
		}
		$part = (int)(count($all_lines)/1000)+1;
		$start =0;
		if($part==1){
			$count_get = count($all_lines)%1000;
		}
		else{
			$count_get = 1000;
		}
		$data = array();
		for ($i=0; $i <$part  ; $i++) {
			$my[$i] = array_slice($all_lines, $start, $count_get );
			if($i<$part-1){
				$start = $start +1000;
			}
			else{
				$start = $start +1000;
				$count_get = count($all_lines)%1000;
			}
		}
		
		for ($i=0; $i <$part ; $i++) {
			foreach ($my[$i] as $value) {
				$data[] = json_decode($value);
			}
			$collection->batchInsert($data, array('timeout' => -1));
			unset($data);
		}
	}
	
	private function __getMissingMedia($collection, $date, $private) {
		$missing_account = file(APP."Vendor/Data/tmp_missing_acc.json");
		foreach ($missing_account as $name) {
			$name = trim(preg_replace('/\s\s+/', ' ', $name));
			echo "Account " . $name . " has missing mediaaaaaaaaaaa" . PHP_EOL;
			$check_count = 0;
			$checkMedia = false;
			while (!$checkMedia && $check_count < 5) {
				if ($private) {
					$checkMedia = $this->__reGetMediaPrivate($name, $date);
				} else {
					$checkMedia = $this->__reGetMedia($name, $date);
				}
				$check_count ++;
			}
			if (!$checkMedia) {
				echo "Re-get media of " . $name . " failed!!!!!!!!!!" . PHP_EOL;
			} else {
				echo "Re-get media of " . $name . " successfully!" . PHP_EOL;
			}
			// write data into database
			$this->__saveIntoDb($name, $collection, $date);
		}
	}
	
	private function __createIndex($collection) {
		echo "Indexing media ..." . PHP_EOL;
		$collection->createIndex(array('user.id' => 1, 'created_time' => 1), array('dropDups' => true, 'timeout' => -1, 'background' => true));
		echo "Indexing media completed!" . PHP_EOL;
		echo "Total documents: " . $collection->count() . PHP_EOL;
	}
	
	private function __saveDataPublic($public_account, $collection, $date) {
		// we get data of 34 accounts at a time
		$account_chunks = array_chunk($public_account, 34);
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
					$myfile = fopen(APP."Vendor/Data/".$date.".".$name.".media.json", "w+") or die("Unable to open file!");
					do {
						$data = $this->__getMedia($name, $max_id);
						// write data into json file
						if (isset($data->items) && !empty($data->items)) {
							foreach ($data->items as $val) {
								fwrite($myfile, json_encode($val)."\n");
							}
							$max_id = end($data->items)->id;
						} else {
							$this->out("Error: data is null");
							break;
						}
					}
					while (isset ($data->more_available) && ($data->more_available == true || $data->more_available == 1));
					fclose($myfile);
		
					// check if account's media is missing or not
					$checkMedia = $this->__checkMedia($name);
					if ($checkMedia) {
						// write data from json file to database
						$this->__saveIntoDb($name, $collection, $date);
						echo "Get media of " . $name . " completed!" . PHP_EOL;
					} else {
						file_put_contents(APP."Vendor/Data/tmp_missing_acc.json", $name . "\n", FILE_APPEND | LOCK_EX);
						echo "Media of " . $name . " is missing (Public account) !!!!!!!" . PHP_EOL;
					}
					// Jump out of loop in this child. Parent will continue.
					exit;
				}
			}
			foreach ($pids as $pid) {
				pcntl_waitpid($pid, $status);
				unset($pids[$pid]);
			}
		}	
	}
	
	private function __saveDataPrivate($private_account, $collection, $date) {
		echo "List of private account: " . PHP_EOL;
		print_r($private_account) . PHP_EOL;
		
		$m = new MongoClient;
		$db = $m->instagram_account_info;
		$collections = $db->account_username;
		
		foreach ($private_account as $name) {
			$result = $collections->find(array('username' => $name));
			foreach ($result as $acc_info) {
				$id = $acc_info['id'];
				if (isset($acc_info['access_token'])) {
					$this->_insta->setAccessToken($acc_info['access_token']);
					$max_id = null;
					// write data into json file
					$myfile = fopen(APP."Vendor/Data/".$date.".".$name.".media.json", "w+") or die("Unable to open file!");
					do {
						$media = $this->_insta->getUserMedia($id, 2, $max_id);
						// if get media successfully and user has number of media > 0
						if (isset($media->data)) {
							foreach ($media->data as $val) {
								fwrite($myfile, json_encode($val)."\n");
							}
							if (isset($media->pagination) && !empty($media->pagination->next_max_id)) {
								$max_id = $media->pagination->next_max_id;
							} else {
								$max_id = null;
								break;
							}	
						} else {
							// get media unsuccessfully or user has no media
							print_r($media);
							break;
						}
					} while ($max_id != null);
				} else {
					$this->out($acc_info['username'] . " does not have access token");
					break;
				}
				fclose($myfile);
				// check if account's media is missing or not
				$checkMedia = $this->__checkMedia($name);
				if ($checkMedia) {
					// write data from json file to database
					$this->__saveIntoDb($name, $collection, $date);
					echo "Get media of " . $name . " completed!" . PHP_EOL;
				} else {
					file_put_contents(APP."Vendor/Data/tmp_missing_acc.json", $name . "\n", FILE_APPEND | LOCK_EX);
					echo "Media of " . $name . " is missing (Private account) !!!!!!!" . PHP_EOL;
				}
			}
		}
	}
}