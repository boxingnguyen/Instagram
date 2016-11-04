 <?php
App::uses('Controller', 'Controller');
class FollowRankingController extends AppController {
	public function index() {
// 		$m = new MongoClient;
// 		$db = $m->instagram;
// 		$collection = $db->follow;
// 		$totalPage = $collection->aggregate(array(
// 				array('$project' => array('count' => array('$size' =>  array('$ifNull' => array('$2124049456' , array())))))
// 		));
// 		echo "<pre>";
// 		print_r($totalPage['result']);
// 		foreach($totalPage['result'] as $a) {
// 			if($a['count'] > 0) {
// 				echo $a['count'];
// 			}
			
// 		}
		
	}
	public function ajax() {
		$this->layout=false;
		$this->autoRender=false;
		$m = new MongoClient;
		$db = $m->instagram;
		$collection = $db->follow;
		$id = $_POST['id'];
		$currentPage = (int)$_POST['currentPage'];
		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$start = $page*$currentPage;
		$totalPage = $collection->aggregate(array(
				array('$project' => array('count' => array('$size' =>  array('$ifNull' => array('$'.$id , array())))))
		));
		foreach($totalPage['result'] as $total) {
			if($total['count'] > 0) {
				$tt = $total['count'];
			}
		}
		$data = $collection->find(array($id => array('$exists' => 1)), array($id => array('$slice' => [$start,$currentPage]))  );
		if($data->count() > 0) {
			foreach($data as $val) {
				$arr = $val[$id];
			}
			return json_encode(array($tt,$arr));
		}
		else {
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