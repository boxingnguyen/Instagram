 <?php
App::uses('Controller', 'Controller');
class RankingController extends AppController {
	
	public function index() {
		
	}
	public function ajax() {
		$this->layout=false;
		$this->autoRender=false;
		$m = new MongoClient;
		$db = $m->follow;
		$beforeTime = (new DateTime())->modify('-1 day')->format('Y-m-d');
		$time = date('Y-m', strtotime($beforeTime));
		$userFollow = $db->selectCollection('username'.$time);
		$loginFollow = $db->selectCollection('login'.$time);
// 		$beforeTime = (new DateTime())->modify('-1 day')->format('Y-m-d');
		$id = $_POST['id'];
		$currentPage = (int)$_POST['currentPage'];
		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$start = $page*$currentPage + 1;
		$data = $userFollow->find(array($id => array('$exists' => 1), 'time' => $beforeTime), array($id => array('$slice' => [$start,$currentPage]))  );
		$dataLogin = $loginFollow->find(array($id => array('$exists' => 1), 'time' => $beforeTime), array($id => array('$slice' => [$start,$currentPage]))  );
		if($data->count() > 0) {
			foreach($data as $val) {
				$arr = $val[$id];
			}
			return json_encode($arr);
		} elseif ($dataLogin->count() > 0) {
			foreach($dataLogin as $valLogin) {
				$arr = $valLogin[$id];
			}
			return json_encode($arr);
		} else {
			$error = 404;
			return $error;
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