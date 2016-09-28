<?php
class TopController extends AppController {
	public function index () {
		exec('sudo chmod -R 777 /www/app/tmp');
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->reaction;
		$data = $collection->find()->sort(array('followers' => -1));
		$this->set(compact('data'));
	}
}