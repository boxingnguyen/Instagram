<?php
class GetFollowController extends AppController {
	private $__collection;
	public function beforeFilter() {
		parent::beforeFilter();
		$m = new MongoClient;
		$db = $m->follow;
		$this->__collection = $db->selectCollection(date('Y-m'));
	}
	public function index() {
		$this->layout = false;
		$this->autoRender = false;
		$code = $_GET['code'];
		$data = $this->_instagram->getOAuthToken($code);
		
		$id = $data->user->id;// '2124049456'; // = user->id;
		$this->_instagram->setAccessToken($data);
		
		$infoFollowsBy = $this->_instagram->getUserFollower();
		echo "<pre>";
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
		$this->redirect ( array('controller' => 'GetFollow', 'action' => 'ranking'));
	}
	public function ranking() {
		$id = '2124049456'; //$id = $this->request->query['id'];
		$data = $this->__collection->find(array($id => array('$exists' => 1)))->sort(array('totalFollow' => -1));
		foreach ($data as $val) {
			$arr[] = $val; 
		}
		echo "<pre>";
		print_r($arr);
	}
}