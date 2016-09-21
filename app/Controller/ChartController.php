<?php
class ChartController extends AppController {
	public function follower() {
		$this->layout = false;
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->account_info;
		
		$id = $this->request->query['id'];
		$data = $collection->find( array('id' => $id));
		$currentDate = strtotime(date('Y/m/d'));
		$arr = array();
		foreach ($data as $val) {
			$follow = $val['followed_by']['count'];
			$timedb = $val['date_add_to_mongo'];
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
	public function like() {
		$m = new MongoClient();
		$dbChart = $m->chart;
		$cllChart = $dbChart->selectCollection(date('Y_m'));
		$id = $this->request->query['id'];
		$arrLikes = array();
		if($id) {
			$data = $cllChart->find(array('accuntID' => $id));
			if (isset($data) && $data->count() > 0) {
				foreach ($data as $vlike) {
					$arrLikes[$vlike['time']] = $vlike['likes'];
				}
			}
		}
		$this->set('dataLikes', $arrLikes);
	}
	public function chart() {
		$this->layout = false;
		$this->autoRender = false;
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->reaction;
		$dbChart = $m->chart;
		$cllChart = $dbChart->selectCollection(date('Y_m'));
		
		//total comment in collection media
		$currentDate = date('Y/m/d');
		$total = 0;
		
		$data = $collection->find(array(), array('_id' => 1, 'username' => 1, 'likes' => 1, 'comments' => 1));
		if(isset($data) && $data->count() > 0) {
			foreach ($data as $val) {
				$searchTime = $cllChart->find(array('time' => $currentDate, 'username' => $val['username']));
				if(isset($searchTime) && $searchTime->count() > 0) {
					foreach ($searchTime as $valChart) {
						$col = array('$set' => array('likes' => $valChart['likes'], 'comments' => $valChart['comments']));
						$cllChart->update(array('time' => $valChart['time']), $col);
					}
				} else {
					$col = array('accuntID' => $val['_id'], 'username' => $val['username'], 'likes' => $val['likes'], 'comments' => $val['comments'], 'time' => $currentDate);
					$cllChart->insert($col);
				}
			}
		}
	}
}

















