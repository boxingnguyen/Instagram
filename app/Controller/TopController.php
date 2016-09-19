<?php
class TopController extends AppController {
	public $mongoCursor;
	
	public function beforeFilter() {
		$m = new MongoClient();
		$db = $m->instagram;
		$this->mongoCursor = $db->media;
	}
	
	public function index () {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->account_info;
		
		$date_now = date('d M Y');
		$condition = array(
				array('$match' => array('date_add_to_mongo' => $date_now)),
				array(
					'$group' => array(
						'_id' => '$id',
						'username' => array('$first' => '$username'),
						'followers' => array('$first' => '$followed_by.count'),
						'media_count' => array('$first' => '$media.count')
					)
				)
				
		);
		$data = $collection->aggregate($condition);
	}
}