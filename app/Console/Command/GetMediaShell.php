 <?php
class GetMediaShell extends Shell {
	public function main() {
		
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->media;
		$collection->drop();
		
		$all_account = array();

		$file = fopen("/www/html/instagram/app/Vendor/account.txt", "r");

		while(!feof($file)){
		    $line = fgets($file);
		    $all_account[] = trim(preg_replace('/\s\s+/', ' ', $line));;
		}
		fclose($file);
		
		$count = 0;
		if (!empty($all_account)) {
			// we get data of 20 accounts at a time
			$account_chunks = array_chunk($all_account, 34);
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
						echo "Get data of " . $name . PHP_EOL;
						$data = $this->getMedia($name, $max_id);
						do {
							$data = $this->getMedia($name, $max_id);
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
						echo "Get data of " . $name . " completed!" . PHP_EOL;
						exit;
					}
				}
				foreach ($pids as $pid) {
					pcntl_waitpid($pid, $status);
					unset($pids[$pid]);
				}
				echo "Total documents: " . $collection->count();
			}
		}
	}
	
	public function getMedia($username, $max_id = null) {
		if ($max_id != null) {
			$data = $this->__cURLInstagram('https://www.instagram.com/' . $username . '/media/?max_id=' . $max_id);
		} else {
			$data = $this->__cURLInstagram('https://www.instagram.com/' . $username . '/media/');
		}
		return $data;
	}
	
	private function __cURLInstagram($apiCall) {
		$headerData = array('Accept: application/json');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apiCall);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, true);
	
		$jsonData = curl_exec($ch);
		// split header from JSON data
		// and assign each to a variable
		list($headerContent, $jsonData) = explode("\r\n\r\n", $jsonData, 2);
	
		// convert header content into an array
		$headers = $this->__processHeaders($headerContent);
	
		if (!$jsonData) {
			throw new Exception('Error: _makeCall() - cURL error: ' . curl_error($ch));
		}
	
		curl_close($ch);
	
		return json_decode($jsonData);
	}
	private function __processHeaders($headerContent)
	{
		$headers = array();
	
		foreach (explode("\r\n", $headerContent) as $i => $line) {
			if ($i === 0) {
				$headers['http_code'] = $line;
				continue;
			}
	
			list($key, $value) = explode(':', $line);
			$headers[$key] = $value;
		}
	
		return $headers;
	}
}