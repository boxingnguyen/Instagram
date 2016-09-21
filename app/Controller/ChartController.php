<?php
class ChartController extends AppController {
	public function follower() {
		$this->layout = false;
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
}

















