<?php
class ChartController extends AppController {
	public function follower() {
		$m = new MongoClient();
		$db = $m->chart;
		$collection = $db->selectCollection(date('Y-m'));
		$currentDate = date('Y-m-d');
		
		$id = $this->request->query['id'];
		$data = $collection->find( array('id' => $id));
		$currentDate = date('Y/m/d');
		$arr = array();
		if(isset($data) && ($data->count() > 0)) {
			foreach ($data as $val) {
				$follow = $val['follows'];
				$timedb = $val['time'];
				$arr[$timedb] = $follow;
			}
			$this->set('data', $arr);
		}
	}
	public function readLikeAndComment($id) {
		$m = new MongoClient();
		$dbChart = $m->chart;
		$collection = $dbChart->selectCollection(date('Y-m'));
		$currentDate = date('Y-m-d');
		
		$data = $collection->find(array('id' => $id))->sort(array('time'=>1));
		
		$dt = array();
		$arr = array();
		$likes = array(); $comments = array();
		foreach($data as $item) {
			$dt[] = $item;
		}

		if(isset($dt) && !empty($dt)) {
			$arr[$dt[0]['time']] = 0;
			for ($i = 0; $i < count($dt) - 1; $i++) {
				for ($j = $i + 1; $j < count($dt); $j++) {
					if(strtotime($dt[$i]['time']) !=  strtotime($dt[$j]['time'])) {
						$arr[$dt[$j]['time']] = array('comment' => ($dt[$j]['comments'] - $dt[$i]['comments']), 'like' => ($dt[$j]['likes'] - $dt[$i]['likes']));
					}
					break;
				}
			}
			return $arr;
		}
		return false;
	}
	public function like() {
		$id = $this->params->query['id'];
		$data = $this->readLikeAndComment($id);
		$arr = array();
		if(isset($data) && !empty($data)) {
			foreach ($data as $key => $val) {
				if(is_array($val)) {
					$arr[$key] = $val['like'];
				} else {
					$arr[$key] = $val;
				}	
			}
		}
		echo "<pre>";
		print_r($arr);
		if(isset($arr) && !empty($arr)) {
			$this->set('dataLikes', $arr);
		}
	}
	public function comment() {
		$id = $this->params->query['id'];
		$data = $this->readLikeAndComment($id);
		if(isset($data) && !empty($data)) {
			foreach ($data as $key => $val) {
				if(is_array($val)) {
					$arr[$key] = $val['comment'];
				} else {
					$arr[$key] = $val;
				}	
			}
		}
		if(isset($arr) && !empty($arr)) {
			$this->set('dataComments', $arr);
		}
	}
// 	public function getmonths($idMonth) {
// 		$month = date('m');
// 		$m = new MongoClient();
// 		$db = $m->instagram;
// 		$collection = $db->media;
		
// 		$id = $idMonth;
// 		date_default_timezone_set("UTC");
		
// 		$numberDaysPerMonth = cal_days_in_month(CAL_GREGORIAN, $month, 2016);
// 		$analytic = array();
// 		$fileds = array('created_time'=>1,'user.username'=>1,'likes.count'=>1,'comments.count'=>1);
// 		if($id) {
// 			for ($day = 1 ; $day <= $numberDaysPerMonth ; $day++){
// 				$start = strtotime("2016-$month-$day 00:00:00");
// 				$end = strtotime("2016-$month-$day 23:59:59");
// 				$query = array(
// 						'created_time' => array(
// 								'$gte' => "$start",
// 								'$lte' => "$end"
// 						),
// 						'user.id' => $id
// 				);
// 				$cursor = $collection->find($query, $fileds);
// 				$countLikes = 0;
// 				$countComments = 0;
// 				if(!empty($cursor)){
// 					foreach ($cursor as $v){
// 						$countLikes += $v['likes']['count'];
// 						$countComments += $v['comments']['count'];
// 					}
// 				}
// 				$analytic[$month.'/'.$day]= array('likes' => $countLikes, 'comments' => $countComments);
					
// 			}
// 			return $analytic;
// 		} else {
// 			return false;
// 		}
				
// 	}
}





















