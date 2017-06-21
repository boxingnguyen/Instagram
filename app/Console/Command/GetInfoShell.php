<?php

class GetInfoShell extends AppShell {
    public function main() {
        $m = new MongoClient();
        $db = $m->in_follower;
        $collections = $db->info;
        $collections->drop();
        $filename = "qA.json";
        if (file_exists($filename)) {
            $totalLines = file($filename);
            if (empty($totalLines)) {
                echo $name . "has no media or something wrong!" . PHP_EOL;
                return;
            }

            $part = (int)(count($totalLines) / 1000) + 1;
            $start = 0;
            if ($part == 1) {
                $count_get = count($totalLines) % 1000;
            } else {
                $count_get = 1000;
            }
            for ($i = 0; $i < $part; $i++) {
                $my[$i] = array_slice($totalLines, $start, $count_get);
                if ($i < $part - 1) {
                    $start = $start + 1000;
                } else {
                    $start = $start + 1000;
                    $count_get = count($totalLines) % 1000;
                }
            } //xx
            $listUsername = array();
            for ($i = 0; $i < $part; $i++) {
                foreach ($my[$i] as $value) {
                    $tmp = json_decode($value);
                    $listUsername[] = $tmp->username;
                }

            }
            $count = 0;
            $myfile = fopen("jal2.json", "w+") or die("Unable to open file!");
            $accountChunks = array_chunk($listUsername, 35);
            $pids = array();
            foreach ($accountChunks as $account) {
                foreach ($account as $username) {
                    $pid = pcntl_fork();
                    if ($pid == -1) {
                        $this->out('Could not fork');
                        exit();
                    } else if ($pid) {
                        $pids[] = $pid;
                    } else {
                        echo "Start get media of " . $username . PHP_EOL;
                        $result = __getUserInfo($username);;
                        $collections->insert($result);
                        exit;
                    }
                }
                echo "account of " . $count . PHP_EOL;
                foreach ($pids as $pid) {
                    pcntl_waitpid($pid, $status);
                    unset($pids[$pid]);
                }
                $count++;
            }
            $a = $collections->find();
            foreach ($a as $val) {
                fwrite($myfile, json_encode($val) . "\n");
            }
        }
        fclose($myfile);
    }
    function __getUserInfo($username) {


		$url = 'https://www.instagram.com/'.$username.'/?__a=1';
		$getAccountInfo = cURLInstagram($url);

		return $getAccountInfo;
	}
    function cURLInstagram($url) {
		$headerData = array('Accept: application/json');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, true);

		$jsonData = curl_exec($ch);
		// split header from JSON data
		// and assign each to a variable
		list($headerContent, $jsonData) = explode("\r\n\r\n", $jsonData, 2);

		// convert header content into an array
		$headers = processHeaders($headerContent);

		if (!$jsonData) {
			throw new Exception('Error: _makeCall() - cURL error: ' . curl_error($ch));
		}

		curl_close($ch);

		return json_decode($jsonData);
	}
	function processHeaders($headerContent) {
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
