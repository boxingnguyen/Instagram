<?php
App::uses('AppController', 'Controller');
class ChartController extends AppController {
	public function getHashtags () {
		$this->layout = false;
		$this->autoRender = false;
		
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->data;
		$data = $collection->find();
		$result = array();
		foreach ($data as $value) {
			if (count($value['tags']) > 0) {
				$result[] = $value;
			}
		}
		echo json_encode($result);
	}
}