 <?php
class GetMediaShell extends AppShell {	
	public function main() {
		$file = fopen(APP."Vendor/username.txt", "r");
		while(!feof($file)){
			$line = fgets($file);
			$all_account[] = trim(preg_replace('/\s\s+/', ' ', $line));;
		}
		fclose($file);
		//name file
		date_default_timezone_set('UTC');
		$nameFile = date('dmY');
		$myfile = fopen("/www/htdocs/PHPInstagram/app/Vendor/Data/".$nameFile.".media.json", "w") or die("Unable to open file!");
		//find date 3 month ago
		$fineStamp = date('Y-m-d 00:00:00');
		$d = new DateTime($fineStamp);
		$d = $d->modify('-3 month');
		$time = strtotime($d->format('Y-m-d 00:00:00'));
		$this->out($time);
		$arrData = array();
		
		if(isset($all_account) && !empty($all_account)) {
			foreach ($all_account as $username) {
				$i = 0;
		
				$max_id = null;
				do {
					if(isset($max_id) && $max_id != null) {
						$data = $this->cURLInstagram('https://www.instagram.com/'.$username.'/media/?max_id='.$max_id);
					} else {
						$data = $this->cURLInstagram('https://www.instagram.com/'.$username.'/media/');
					}
					if(isset($data->items) && !empty($data->items)) {
						$max_id = end($data->items)->id;
						$maxTime = end($data->items)->created_time;
						echo "Max id: ".$i.' '.$max_id.PHP_EOL;
						$i++;
						if($maxTime < $time) {
							$this->out($maxTime);
							foreach ($data->items as $val) {
								if($val->created_time >= $time) {
									fwrite($myfile, json_encode($val).PHP_EOL);
								}
							}
							$data->more_available = false;
						} else {
							foreach($data->items as $val){
								fwrite($myfile, json_encode($val).PHP_EOL);
							}
						}
		
					}
				} while (isset($data->more_available) && ($data->more_available == true || $data->more_available == 1));
				echo "Complete username: ".$username.PHP_EOL;
			}
			fclose($myfile);
		}
		
	}
}