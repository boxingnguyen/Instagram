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
		$id = $_POST['id'];
		$currentPage = (int)$_POST['currentPage'];
		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$start = $page*$currentPage;
		$totalPageUser = $userFollow->aggregate(array(
				array('$project' => array('count' => array('$size' => '$'.$id)))
		));
		$totalPageLogin = $loginFollow->aggregate(array(
				array('$project' => array('count' => array('$size' => '$'.$id)))
		));
		$data = $userFollow->find(array($id => array('$exists' => 1), 'time' => $beforeTime), array($id => array('$slice' => [$start,$currentPage]))  );
		$dataLogin = $loginFollow->find(array($id => array('$exists' => 1), 'time' => $beforeTime), array($id => array('$slice' => [$start,$currentPage]))  );
		if($data->count() > 0) {
			foreach($data as $val) {
				$arr = $val[$id];
			}
			return json_encode(array($totalPageUser,$arr));
		} elseif ($dataLogin->count() > 0) {
			foreach($dataLogin as $valLogin) {
				$arr = $valLogin[$id];
			}
			return json_encode(array($totalPageLogin,$arr));
		} else {
			$error = 404;
			return $error;
		}
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