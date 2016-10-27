 <?php
App::uses('Controller', 'Controller');
class RankingController extends AppController {
	
	public function index() {
		$m = new MongoClient;
		$db = $m->follow;

		$userFollow = $db->selectCollection('username'.date('Y-m'));
		$loginFollow = $db->selectCollection('login'.date('Y-m'));
		
		$id = $this->request->query['id'];
		$data = $userFollow->find(array($id => array('$exists' => 1)));
		if($data->count() > 0) {
			foreach($data as $val) {
				usort($val[$id], function($a, $b) { return $a['totalFollow'] < $b['totalFollow'] ? 1 : -1 ; } );
				$arr = $val[$id];
			}
			$this->set('data', $arr);
		} else {
			$dataLogin = $loginFollow->find(array($id => array('$exists' => 1)));
			if($dataLogin->count() > 0) {
				foreach($dataLogin as $valLogin) {
					usort($valLogin[$id], function($a, $b) { return $a['totalFollow'] < $b['totalFollow'] ? 1 : -1 ; } );
					$arrLogin = $valLogin[$id];
				}
				$this->set('data', $arrLogin);
			}
		}
		// $mLogin = new MongoClient;
		// $dbLogin = $mLogin->instagram_account_info;
		// $colLogin = $dbLogin->account_login;
		// $id = $this->request->query['id'];
		// $data = $colLogin->find(array('id' => $id), array('access_token' => true));
		// foreach($data as $access) {
		// 	$accessToken = $access['access_token'];
		// }
		// $this->_instagram->setToken($accessToken);
		// $infoFollowsBy = $this->_instagram->getUserFollower();
		// if(isset($infoFollowsBy) && !empty($infoFollowsBy->data)) {
		// 	$getFollow = $infoFollowsBy->data;
		// 	$arr = array();
		// 	if (count($getFollow) > 0) {
		// 		foreach ($getFollow as $key => $val) {
		// 			$username = $val->username;
		// 			$url = 'https://www.instagram.com/'.$username.'/?__a=1';
		// 			$getUrl = $this->cURLInstagram($url);
		// 			$countFollow = $getUrl->user->followed_by->count;
		// 			$arr[] = array('id' => $val->id, 'username' => $val->username, 'full_name' => $val->full_name, 'totalFollow' => $countFollow);
		// 		}
		// 		$userId = $this->__collection->find(array($id => array('$exists' => 1)));
		// 		if ($userId->count() > 0) {
		// 			$this->__collection->remove(array($id => array('$exists' => 1)));
		// 		}
		// 		$this->__collection->insert(array($id => $arr));
		// 		$this->redirect (array('controller' => 'Ranking', 'action' => 'follow','?' => array('id' => $id)));
		// 	}
		// } else {
		// 	echo "<pre>";
		// 	print_r($infoFollowsBy);
		// }

	}

	public function follow() {

		$id = $this->request->query['id'];
		$data = $collection->find(array($id => array('$exists' => 1)));
		if($data->count() > 0) {
			foreach($data as $val) {
				usort($val[$id], function($a, $b) { return $a['totalFollow'] < $b['totalFollow'] ? 1 : -1 ; } );
				$arr = $val[$id];
			}
			$this->set('data', $arr);
		}
	}
	public function hashtag () {
	}
}