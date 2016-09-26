<?php
class ChartController extends AppController {
	public function follower() {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->follows;
		$currentDate = date('Y-m-d');
		
		$id = $this->request->query['id'];
		$data = $collection->find( array('id' => $id));
		$currentDate = date('Y/m/d');
		$arr = array();
		foreach ($data as $val) {
			$follow = $val['followed_by']['count'];
			$timedb = $val['time'];
			$arr[$timedb] = $follow;
		}
		$this->set('data', $arr);
	}
	public function like() {
		$id = $this->params->query['id'];
		$data = $this->getmounths($id);
		if(isset($data) && !empty($data)) {
			$this->set('dataLikes', $data);
		}
	}
	public function comment() {
		$id = $this->params->query['id'];
		$data = $this->getmounths($id);
		if(isset($data) && !empty($data)) {
			$this->set('dataComments', $data);
		}
	}
	public function getmounths($idMounth) {
		$m = new MongoClient();
		$dbChart = $m->instagram;
		$cllChart = $dbChart->media;
		
		$month = date('m');
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->media;
		
		$id = $idMounth;
		date_default_timezone_set("UTC");
		
		$numberDaysPerMonth = cal_days_in_month(CAL_GREGORIAN, $month, 2016);
		$analytic = array();
		$fileds = array('created_time'=>1,'user.username'=>1,'likes.count'=>1,'comments.count'=>1);
		if($id) {
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
				$countLikes = 0;
				$countComments = 0;
				if(!empty($cursor)){
					foreach ($cursor as $v){
						$countLikes += $v['likes']['count'];
						$countComments += $v['comments']['count'];
					}
				}
				$analytic[$month.'/'.$day]= array('likes' => $countLikes, 'comments' => $countComments);
					
			}
			return $analytic;
		} else {
			return false;
		}
				
	}
}