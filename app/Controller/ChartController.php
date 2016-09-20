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
	
	public function like() {
// 		$this->layout = false;
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->media;
	
		$dbChart = $m->likes;
		$cllChart = $dbChart->selectCollection(date('Y_m'));
		
		//total comment in collection media
		$currentDate = date('Y/m/d');
		$totalLikes = 0;
		$id = $this->request->query['id'];
		if($id) {
			$data = $collection->find( array('user.id' => $id), array('_id' => 0,'user.id' => 1, 'user.username' => 1, 'likes.count' => 1));
			if(isset($data) && $data->count() > 0) {
				foreach ($data as $val) {
					$totalLikes += $val['likes']['count'];
					$username = $val['user']['username'];
				}
			}
			
			
			//save collection comment
			$searchTime = $cllChart->find(array('time' => $currentDate,'id' => $id));
			if(isset($searchTime) && $searchTime->count() > 0) {
				foreach ($searchTime as $valChart) {
					$col = array('$set' => array('total' => $valChart['total']));
					$cllChart->update(array('time' => $valChart['time']), $col);
				}
			} else {
				$col = array('id' => $id, 'usename' => $username, 'total' => $totalLikes, 'time' => $currentDate);
				$cllChart->insert($col);
			}
			//display chart
			$arrLikes = $this->chartLikes($id);
			$this->set('dataLikes', $arrLikes);
		}
		
	}
	public function chartLikes($id) {
		$m = new MongoClient();
		$dbChart = $m->likes;
		$cllChart = $dbChart->selectCollection(date('Y_m'));
		
		$arrLikes = array();
		if($id) {
			$data = $cllChart->find(array('id' => $id));
			if (isset($data) && $data->count() > 0) {
				foreach ($data as $vLike) {
					$arrLikes[$vLike['time']] = $vLike['total'];
				}
			}
			return $arrLikes;
		}
	}
	public function comment() {
// 		$this->layout = false;
		$id = $this->request->query['id'];
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->media;
		
		$dbChart = $m->comments;
		$cllChart = $dbChart->selectCollection(date('Y_m'));
		$currentDate = date('Y/m/d');
		//total comment in collection media
		$totalComment = 0;
		if($id) {
			$data = $collection->find(array('user.id' => $id), array('_id' => 0,'user.id' => 1, 'user.username' => 1, 'comments.count' => 1));
			if(isset($data) && $data->count() > 0) {
				foreach ($data as $val) {
					$totalComment += $val['comments']['count'];
					$username = $val['user']['username'];
				}
			}
			//save collection comment
			$searchTime = $cllChart->find(array('time' => $currentDate,'id' => $id));
			if(isset($searchTime) && $searchTime->count() > 0) {
				foreach ($searchTime as $valChart) {
					$col = array('$set' => array('total' => $valChart['total']));
					$cllChart->update(array('time' => $valChart['time']), $col);
				}
			} else {
				$col = array('id' => $id,'usename' => $username, 'total' => $totalComment, 'time' => $currentDate);
				$cllChart->insert($col);
			}
			
			//display chart
			$arrComment = $this->chartComment($id);
			$this->set('dataComments', $arrComment);
		}
	}
	public function chartComment($id) {
		$m = new MongoClient();
		$dbChart = $m->comments;
		$cllChart = $dbChart->selectCollection(date('Y_m'));		
		
		$arrComment = array();
		if($id) {
			$data = $cllChart->find(array('id' => $id));
			if (isset($data) && $data->count() > 0) {
				foreach ($data as $vComment) {
					$arrComment[$vComment['time']] = $vComment['total'];
				}
			}
			return $arrComment;
		}
		
	}
}

















