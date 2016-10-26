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
	}
	public function hashtag () {
	}
}