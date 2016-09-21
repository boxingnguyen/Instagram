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
		$collection->batchInsert($result);
		// insert monthly reaction into database
		$this->__insertMonthlyReaction();
		
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
	
	private function insertMonthlyReaction() {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collection = $db->reaction;
		$dbChart = $m->chart;
		$cllChart = $dbChart->selectCollection(date('Y_m'));
		
		//total comment in collection media
		$currentDate = date('Y/m/d');
		$total = 0;
		
		$data = $collection->find(array(), array('_id' => 1, 'username' => 1, 'likes' => 1, 'comments' => 1));
		if(isset($data) && $data->count() > 0) {
			foreach ($data as $val) {
				$searchTime = $cllChart->find(array('time' => $currentDate, 'username' => $val['username']));
				if(isset($searchTime) && $searchTime->count() > 0) {
					foreach ($searchTime as $valChart) {
						$col = array('$set' => array('likes' => $valChart['likes'], 'comments' => $valChart['comments']));
						$cllChart->update(array('time' => $valChart['time']), $col);
					}
				} else {
					$col = array('accuntID' => $val['_id'], 'username' => $val['username'], 'likes' => $val['likes'], 'comments' => $val['comments'], 'time' => $currentDate);
					$cllChart->insert($col);
				}
			}
		}
	}
}