<?php
class GoogleChartShell extends AppShell {	
	public function main() {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->reaction;
		$dbChart = $m->chart;
		$chart = $dbChart->selectCollection(date('Y-m'));
		
		$currentDate = (new DateTime())->modify('-1 day')->format('Y-m-d');// date('Y-m-d');
		$data = $collection->find(array(), array('_id' => 1, 'followers' => 1,'likes'=>1,'comments'=>1));
	
		if(isset($data) && $data->count() > 0) {
			foreach($data as $val) {
				
				$getChart = $chart->find(array('id' => $val['_id'], 'time' => $currentDate));
				if($getChart->count() > 0){
					$chart->update(
							array(), 
							array(
									'$set' => array(
											'follows' => $val['followers'],
											'likes'=>$val['likes'],
											'comments'=> $val['comments']
									)
							)
							);
				} else {
					$newChart = array(
							'id' => $val['_id'],
							'follows'=>$val['followers'],
							'likes'=>$val['likes'],
							'comments'=>$val['comments'],
							'time'=>$currentDate
					);
					$chart->insert($newChart);
				}
			}
		}
		echo "Complete";
	}
	
}