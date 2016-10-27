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
		$userFollow = $db->selectCollection('username'.date('Y-m'));
		$loginFollow = $db->selectCollection('login'.date('Y-m'));
		
		$id = $_POST['id'];
		$currentPage = (int)$_POST['currentPage'];
		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$start = $page*$currentPage + 1;
		$data = $userFollow->find(array($id => array('$exists' => 1)),  array($id => array('$slice' => [$start,$currentPage]))  );
		$dataLogin = $loginFollow->find(array($id => array('$exists' => 1)), array($id => array('$slice' => [$start,$currentPage]))  );
		if($data->count() > 0) {
			foreach($data as $val) {
				usort($val[$id], function($a, $b) { return $a['totalFollow'] < $b['totalFollow'] ? 1 : -1 ; } );
				$arr = $val[$id];
			}
			return json_encode($arr);
		} elseif ($dataLogin->count() > 0) {
			foreach($dataLogin as $valLogin) {
				usort($valLogin[$id], function($a, $b) { return $a['totalFollow'] < $b['totalFollow'] ? 1 : -1 ; } );
				$arr = $valLogin[$id];
			}
			return json_encode($arr);
		} else {
			$error = "Not AccessToken";
			return $error;
		}
		
	}
	public function hashtag () {
	}
}