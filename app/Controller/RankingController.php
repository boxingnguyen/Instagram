<?php
App::uses('Controller', 'Controller');
class RankingController extends AppController {
	private $__collection;
	
	public function beforeFilter() {
		parent::beforeFilter();
		$m = new MongoClient;
		$db = $m->follow;
		$this->__collection = $db->selectCollection(date('Y-m'));
		
	}
	public function index() {	
		$id = $this->request->query['id'];
		$accessToken = $this->Session->read('access_token');
		$this->_instagram->setToken($accessToken);
		$infoFollowsBy = $this->_instagram->getUserFollower();
		$getFollow = $infoFollowsBy->data;
		$arr = array();
		if (count($getFollow) > 0) {
			foreach ($getFollow as $val) {
				$username = $val->username;
				$url = 'https://www.instagram.com/'.$username.'/?__a=1';
				$getUrl = $this->cURLInstagram($url);
				$countFollow = $getUrl->user->followed_by->count;
				$arr[] = array('id' => $val->id, 'username' => $val->username, 'full_name' => $val->full_name, 'totalFollow' => $countFollow);
			}
			krsort($arr);
			$userId = $this->__collection->find(array($id => array('$exists' => 1)));
			if ($userId->count() > 0) {
				$this->__collection->remove(array($id => array('$exists' => 1)));
			}
			$this->__collection->insert(array($id => $arr));
		}
		$this->redirect (array('controller' => 'Ranking', 'action' => 'follow','?' => array('id' => $id)));
	}
	public function follow() {
		$id = $this->request->query['id'];
		$data = $this->__collection->find(array($id => array('$exists' => 1)))->sort(array('totalFollow' => -1));
		foreach($data as $val) {
			$arr = $val[$id];
		}
		$this->set('data', $arr);
	}
	
	public function hashtag () {
	}
}