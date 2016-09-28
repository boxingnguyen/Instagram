 <?php
class GetMediaThreeMonthShell extends AppShell {	
	public function main() {
		$this->getData();
		$this->saveToDb();
	}
	public function getData(){
		$file = fopen(APP."Vendor/username.txt", "r");
		while(!feof($file)){
			$line = fgets($file);
			$all_account[] = trim(preg_replace('/\s\s+/', ' ', $line));;
		}
		fclose($file);
		//name file
		date_default_timezone_set('UTC');
		$nameFile = date('dmY');
		$myfile = fopen(APP."Vendor/Data/".$nameFile.".media.json", "w+") or die("Unable to open file!");
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
						if($maxTime < $time) {
							$this->out('Time post: '.$maxTime);
							foreach ($data->items as $val) {
								if($val->created_time >= $time) {
									fwrite($myfile, json_encode($val).PHP_EOL);
								}
							}
							break;
						} else {
							foreach($data->items as $val){
								fwrite($myfile, json_encode($val).PHP_EOL);
							}
						}
						echo "Max id: ".$i.' '.$max_id.PHP_EOL;
						$i++;
					}
				} while (isset($data->more_available) && ($data->more_available == true || $data->more_available == 1));
				echo "Complete username: ".$username.PHP_EOL;
			}
			fclose($myfile);
		}
	}
	
	public function saveToDb() {
		date_default_timezone_set("UTC");
		ini_set('memory_limit', '-1');
		$month = date('m');
		$m = new MongoClient();
		$db = $m->instagram_media;
		$collections = $db->selectCollection('2016_'.$month);
	
		//get time in 3months from now
		$start = strtotime(date("Y-m-d 00:00:00",strtotime("-3 Months")));
		//query
		$query = array('created_time' => array('$gte' => "$start"));
		//name of file json which contains data of today
		$nameFile = date('dmY').".media.json";
	
		//check file and update data to db
		if(file_exists(APP."Vendor/Data/".$nameFile)){
			//remove the same data in db
			$collections->remove($query);
			$data = file_get_contents(APP."Vendor/Data/".$nameFile); //read the file
			$convert = explode("\n", $data); //create array separate by new line
			$media = array();
			for ($i=0;$i<count($convert);$i++){
				if($convert[$i] != null || !empty($convert[$i])){
					//insert new data
					$collections->insert(json_decode($convert[$i]));
				}
			}
			//create index of "created_time"
			echo "Indexing media ..." . PHP_EOL;
			$collections->createIndex(array('created_time' => 1));
			echo "Indexing media completed!" . PHP_EOL;
			echo "Total documents: " . $collections->count() . PHP_EOL;
		}else{
			$this->out('File '.$nameFile.' Not Found');
		}
	}
}