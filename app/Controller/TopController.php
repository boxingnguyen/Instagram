<?php
class TopController extends AppController {
	public function index () {
// 		$m = new MongoClient();
// 		$db = $m->instagram_account_info;
// 		$collection = $db->account_info;
		
// 		$date_now = date('d M Y');
// 		$condition = array(
// 				array('$match' => array('date_add_to_mongo' => $date_now)),
// 				array(
// 					'$group' => array(
// 						'_id' => '$id',
// 						'username' => array('$first' => '$username'),
// 						'followers' => array('$first' => '$followed_by.count'),
// 						'media_count' => array('$first' => '$media.count')
// 					)
// 				)
				
// 		);
// 		$data = $collection->aggregate($condition);
// 		$result = array();
		
// 		foreach ($data['result'] as $key => $value) {
// 			$data['result'][$key]['likes'] = $this->__calculateReaction($value['_id']);
// 		}
	}
	
	private function __calculateReaction($account_id) {
		$this->layout = false;
		$this->autoRender = false;
		
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->data;
		$condition = array(
				'$group' => array(
						'_id' => '$user.id',
						'username' => array('$first' => '$user.username'),
						'total_likes' => array('$sum' => '$likes.count'),
						'total_comments' => array('$sum' => '$comments.count')
				)
		);
		$data = $collection->aggregate($condition);
		$result = array();
		$result['likes'] = $data['result']['total_likes'];
		$result['comments'] = $data['result']['total_comments'];
		return $result;
	}
}