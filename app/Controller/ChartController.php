<?php
class ChartController extends AppController {
	public function follower() {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->selectCollection(date('Y-m'));
		
		$id = $this->request->query['id'];
		$data = $collection->find( array('id' => $id));
		$arr = array();
		if(isset($data) && ($data->count() > 0)) {
			foreach ($data as $val) {
				$follow = $val['followers'];
				$timedb = $val['time'];
				$arr[$timedb] = $follow;
			}
			$this->set('data', $arr);
		}
	}
	public function readLikeAndComment($id) {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->selectCollection(date('Y-m'));
		
		$data = $collection->find(array('id' => $id))->sort(array('time'=>1));

		//last month
		$date = new DateTime();
		$date->modify('-1 month');
		$lastMonth =  $date->format('Y-m');
		//last day
		$d=cal_days_in_month(CAL_GREGORIAN,$date->format('m'),$date->format('Y'));
		$time = $date->format('Y').'-'.$date->format('m').'-'.$d;
		
		//last month collections
		$lastCollection = $db->selectCollection($lastMonth);
		$lastdata = $lastCollection->find(array('id' => $id,'time' => $time));

		$like = 0; $comment = 0;
		if(isset($lastdata) && $lastdata->count() > 0) {
			foreach ($lastdata as $val) {
				$like = $val['likes'];
				$comment = $val['comments'];
			}
		}
		
		$dt = array();
		$arr = array();
		$likes = array(); $comments = array();
		foreach($data as $item) {
			$dt[] = $item;
		}
		
		if(isset($dt) && !empty($dt)) {
			if($like > 0 || $comment > 0) {
				$arr[$dt[0]['time']] = array('comment' => ($dt[0]['comments'] - $comment), 'like' => ($dt[0]['likes'] - $like) );
			} else {
				$arr[$dt[0]['time']] = 0;
			}
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
}





















