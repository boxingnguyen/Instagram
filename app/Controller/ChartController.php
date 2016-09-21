<?php
class ChartController extends AppController {
	public function follower() {
// 		$this->layout = false;
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->account_info;
		
		$id = $this->request->query['id'];
		$data = $collection->find( array('id' => $id));
		$currentDate = date('Y/m/d');
		$arr = array();
		foreach ($data as $val) {
			$follow = $val['followed_by']['count'];
			$timedb = $currentDate;
			$arr[$timedb] = $follow;
		}		
		$this->set('data', $arr);
	}
	
	public function comment() {
		$m = new MongoClient();
		$dbChart = $m->chart;
		$cllChart = $dbChart->selectCollection(date('Y_m'));
		$id = $this->request->query['id'];
		$arrComment = array();
		if($id) {
			$data = $cllChart->find(array('accuntID' => $id));
			if (isset($data) && $data->count() > 0) {
				foreach ($data as $vComment) {
					$arrComment[$vComment['time']] = $vComment['comments'];
				}
			}
		}
		$this->set('dataComments', $arrComment);
	}
// 	public function like() {
// 		$m = new MongoClient();
// 		$dbChart = $m->chart;
// 		$cllChart = $dbChart->selectCollection(date('Y_m'));
// 		$id = $this->request->query['id'];
// 		$arrLikes = array();
// 		if($id) {
// 			$data = $cllChart->find(array('accuntID' => $id));
// 			if (isset($data) && $data->count() > 0) {
// 				foreach ($data as $vlike) {
// 					$arrLikes[$vlike['time']] = $vlike['likes'];
// 				}
// 			}
// 		}
// 		$this->set('dataLikes', $arrLikes);
// 	}
	public function like() {
		$this->layout = false;
		$this->autoRender = false;
		$m = new MongoClient();
		$dbChart = $m->instagram;
		$cllChart = $dbChart->media;
		
		
		
		$month = 9;
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->media;
		
		$id = $this->params->query['id'];
// 		$id = '18428658';
		date_default_timezone_set("UTC");
		
		$numberDaysPerMonth = cal_days_in_month(CAL_GREGORIAN, $month, 2016);
		$analytic = array();
		$fileds = array('created_time'=>1,'user.username'=>1,'likes.count'=>1,'comments.count'=>1);
		for ($day = 1 ; $day <= $numberDaysPerMonth ; $day++){
			$start = strtotime("2016-$month-$day 00:00:00");
			$end = strtotime("2016-$month-$day 23:59:59");
			$query = array(
					'created_time' => array(
							'$gte' => "$start",
							'$lte' => "$end"
					),
					'user.id' => $id
			);
			$cursor = $collection->find($query, $fileds);
			$count = 0;
			if(!empty($cursor)){
				foreach ($cursor as $v){
					$count += $v['likes']['count'];
					// 					$count += $v['comments']['count'];
				}
			}
			$analytic[$month.'/'.$day]= $count;
			
		}
		$this->set('dataLikes', $analytic);
// 		echo "<pre>";
// 		print_r($analytic);
		
		
	}
}

















