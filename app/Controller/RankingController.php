 <?php
App::uses('Controller', 'Controller');
class RankingController extends AppController {
	public function index() {
		$m = new MongoClient;
		$db = $m->follow;
		$collection = $db->selectCollection(date('Y-m'));
		
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