<?php
class TopController extends AppController {
	public function index () {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->reaction;
		$data = $collection->find()->sort(array('followers' => -1));
		$this->set(compact('data'));
	}
}