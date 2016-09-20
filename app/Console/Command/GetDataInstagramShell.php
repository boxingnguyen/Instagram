<?php
class GetDataInstagramShell extends Shell {
	public function initialize() {
		
	}
	private function __getAccount() {
		$nameAccount = array();
		$file = "./Vendor/username.txt";
		$fl = fopen($file,'r');
		
		while (!feof($fl)) {
			$nameAccount[] = trim(preg_replace('/\s\s+/', ' ', fgets($fl)));;
		}
		return $nameAccount;
	}
	public function getMediaCall() {
		$m = new MongoClient();
		$db = $m->Instagram;
		$collection = $db->getMedia;
		
		$nameAccount = $this->__getAccount();
		if (isset($nameAccount) && !empty($nameAccount)) {
			foreach ($nameAccount as $name) {
				$maxId = null;
				$this->out('Account :');
				print_r($name);
				$i = 0;
				$this->out('Count request .....');
				do {					
					if ($maxId == null) {
						$apiCall = 'https://www.instagram.com/'.$name.'/media/';
					} else {
						$apiCall = 'https://www.instagram.com/'.$name.'/media/?max_id='.$maxId;
					}
					
					$notAPI = $this->__cURLInstagram($apiCall);
					$i++;
					// insert to mongo
					if(isset($notAPI) && !empty($notAPI) && !empty($notAPI->items)) {
						$collection->batchInsert($notAPI->items);
					}
					$maxId = end($notAPI->items);
					$maxId = $maxId->id;
				}
				while (($notAPI->more_available == true) || ($notAPI->more_available == 1));
				
				$this->out($i);
			}
		}
	}
	
	public function getUse() {		
		$m = new MongoClient();
		$db = $m->Instagram;
		$collection = $db->getUse;
		
		$nameAccount = $this->__getAccount();
// 		print_r($nameAccount);
		// 		$nameAccount = array('smsaruae','kicks4sale');
		
		if (isset($nameAccount) && !empty($nameAccount)) {
			foreach ($nameAccount as $name) {
				$this->out($name);
				$apiCall = 'https://www.instagram.com/'.$name.'/?__a=1';
				$dataAccount = $this->__cURLInstagram($apiCall);
				if(isset($dataAccount) && !empty($dataAccount)) {
					$collection->insert($dataAccount->user);
				}
			}
		}
		
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
		
		// get the 'X-Ratelimit-Remaining' header value
		//         $this->_xRateLimitRemaining = $headers['X-Ratelimit-Remaining'];
		
		if (!$jsonData) {
			throw new InstagramException('Error: _makeCall() - cURL error: ' . curl_error($ch));
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
