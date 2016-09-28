<?php
class CalculateReactionShell extends AppShell {
	public $mongoCursor;
	
	public function initialize() {
		$m = new MongoClient();
		$db = $m->instagram;
		$this->mongoCursor = $db->media;
	}
	public function main() {
		$start_time = microtime(true);
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->account_info;
		
		$condition = array(
				'$group' => array(
					'_id' => '$id',
					'username' => array('$first' => '$username'),
					'fullname' => array('$first' => '$full_name'),
					'followers' => array('$first' => '$followed_by.count'),
					'media_count' => array('$first' => '$media.count')
				)
		);
		$data = $collection->aggregate($condition);
		$count = 1;
		$result = $data['result'];
		foreach ($data['result'] as $key => $value) {
			$reaction = $this->__calculateReaction($value['_id']);
			$result[$key]['likes'] = $reaction['likes'];
			$result[$key]['comments'] = $reaction['comments'];
			$result[$key]['media_get'] = $reaction['media_get'];
			echo $count . ". Reaction of " . $value['username'] . " completed!" . PHP_EOL;
			$count ++;
		}
		// write result (after calculating reaction) into database
		$db = $m->instagram_account_info;
		$collection = $db->reaction;
		$collection->drop();
		$collection->batchInsert($result);
		
		$end_time = microtime(true);
		echo "Time to calculate reaction: " . ($end_time - $start_time) . " seconds" . PHP_EOL;
	}
	
 	private function __calculateReaction($account_id) {
		$condition = array(
				array('$match' => array('user.id' => $account_id)),
				array(
						'$group' => array(
								'_id' => '$user.id',
								'total_likes' => array('$sum' => '$likes.count'),
								'total_comments' => array('$sum' => '$comments.count'),
								'media_get' => array('$sum' => 1)
						)
				)
		);
		$data = $this->mongoCursor->aggregate($condition, array('maxTimeMS' => 3*60*1000));
		$result = array();
		$result['likes'] = isset($data['result'][0]['total_likes']) ? $data['result'][0]['total_likes'] : 0;
		$result['comments'] = isset($data['result'][0]['total_comments']) ? $data['result'][0]['total_comments'] : 0;
		$result['media_get'] = isset($data['result'][0]['media_get']) ? $data['result'][0]['media_get'] : 0;
		return $result;
	}
}