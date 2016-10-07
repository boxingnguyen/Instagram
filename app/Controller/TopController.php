<?php
class TopController extends AppController {
	public function index () {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->selectCollection(date('Y-m'));
		$currentDate = (new DateTime())->modify('-1 day')->format('Y-m-d');
		$data = $collection->find(array('time' => $currentDate))->sort(array('followers' => -1));
		$this->set(compact('data'));
	}
}