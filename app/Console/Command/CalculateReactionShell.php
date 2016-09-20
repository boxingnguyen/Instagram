<?php
class CalculateReactionShell extends AppShell {
	public $mongoCursor;
	
	public function initialize() {
		$m = new MongoClient();
		$db = $m->instagram;
		$this->mongoCursor = $db->media;
	}
	
	public function main() {
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
						'fullname' => array('$first' => '$full_name'),
						'followers' => array('$first' => '$followed_by.count'),
						'media_count' => array('$first' => '$media.count')
					)
				)
		);
		$data = $collection->aggregate($condition);
		if (empty($data['result'])) {
			$newdate = date('d M Y', strtotime('-1 day', strtotime($date_now)));
			$condition = array(
					array('$match' => array('date_add_to_mongo' => $newdate)),
					array(
							'$group' => array(
									'_id' => '$id',
									'username' => array('$first' => '$username'),
									'fullname' => array('$first' => '$full_name'),
									'followers' => array('$first' => '$followed_by.count'),
									'media_count' => array('$first' => '$media.count')
							)
					)
			);
			$data = $collection->aggregate($condition);
		}
		$time1 = microtime(true);
		$count = 1;
		$result = $data['result'];
		foreach ($data['result'] as $key => $value) {
			$reaction = $this->__calculateReaction($value['_id']);
			$result[$key]['likes'] = $reaction['likes'];
			$result[$key]['comments'] = $reaction['comments'];
			echo $count . ". " . $value['username'] . " completed!" . PHP_EOL;
			$count ++;
		}
		
		// write result (after calculating reaction) into database
		$db = $m->instagram_account_info;
		$collection = $db->reaction;
		$collection->drop();
		print_r($result);
		$collection->batchInsert($result);
		
		$time2 = microtime(true);
		echo "Took: " . ($time2 - $time1) . PHP_EOL;
	}
	
 	private function __calculateReaction($account_id) {
		$condition = array(
				array('$match' => array('user.id' => $account_id)),
				array(
						'$group' => array(
								'_id' => '$user.id',
								'total_likes' => array('$sum' => '$likes.count'),
								'total_comments' => array('$sum' => '$comments.count'),
						)
				)
		);
		$data = $this->mongoCursor->aggregate($condition);
		$result = array();
		$result['likes'] = $data['result'][0]['total_likes'];
		$result['comments'] = $data['result'][0]['total_comments'];
		return $result;
	}
}