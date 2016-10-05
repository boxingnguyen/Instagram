<?php
class CalculateReactionShell extends AppShell {
	const FIRSTDAY = "01";
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
		
		if (date('d') == self::FIRSTDAY) {
			$month = (new DateTime())->modify('-1 month')->format('m');
			$day = cal_days_in_month(CAL_GREGORIAN,$month,date('Y'));
			$currentTime = date('Y')."-".$month."-".$day;
		} else {
			$currentTime = (new DateTime())->modify('-1 day')->format('Y-m-d');
		}
		
		$date = (new DateTime())->format('Y-m-d 00:00:00');
		$date = (string)strtotime($date);
		
		
		foreach ($data['result'] as $key => $value) {
			
			$reactionTop = $this->__calculateReaction($value['_id']);//display top
			$reactionAnalytic = $this->__calculateReaction($value['_id'], $date);//display analytic
			
			
			$result[$key]['time'] = $currentTime;
			$result[$key]['id'] = $value['_id'];
			$result[$key]['likesTop'] = $reactionTop['likes'];
			$result[$key]['commentsTop'] = $reactionTop['comments'];
			$result[$key]['media_get'] = $reactionTop['media_get'];
			
			$result[$key]['likesAnalytic'] = $reactionAnalytic['likes'];
			$result[$key]['commentsAnalytic'] = $reactionAnalytic['comments'];
			
			unset($result[$key]['_id']);
			echo $count . ". Reaction of " . $value['username'] . " completed!" . PHP_EOL;
			$count ++;
		}
		$this->__insertCaculate($result, $currentTime);
		$end_time = microtime(true);
		echo "Time to calculate reaction: " . ($end_time - $start_time) . " seconds" . PHP_EOL;
	}
	private function __insertCaculate($result, $currentTime) {
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		
		$time = date('Y-m', strtotime($currentTime));
		$collection = $db->selectCollection($time);
		
		if(isset($result) && count($result) > 0) {
			$dateCurrent = $collection->find(array('time' => $currentTime));
			if($dateCurrent->count() > 0){
				$collection->update(
						array(),
						array()
						);
			} else {
				$collection->batchInsert($result);
			}
		}		
	}
	private function __calculateReaction($account_id, $date = null) {
		if($date == null) {
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
		} else {
			$condition = array(
					array('$match' => array('user.id' => $account_id, 'created_time' => array('$lt' => $date))),
					array(
							'$group' => array(
									'_id' => '$user.id',
									'total_likes' => array('$sum' => '$likes.count'),
									'total_comments' => array('$sum' => '$comments.count'),
							)
					)
			);
		}
		$data = $this->mongoCursor->aggregate($condition, array('maxTimeMS' => 3*60*1000));
		$result = array();
		$result['likes'] = isset($data['result'][0]['total_likes']) ? $data['result'][0]['total_likes'] : 0;
		$result['comments'] = isset($data['result'][0]['total_comments']) ? $data['result'][0]['total_comments'] : 0;
		$result['media_get'] = isset($data['result'][0]['media_get']) ? $data['result'][0]['media_get'] : 0;
		return $result;
	}
	public function test() {
		$m = new MongoClient();
		$dbChart = $m->chart;
		$cl = $dbChart->selectCollection('2016-10');
		$data  = $cl->find(array('time'=>'2016-10-03'));
		$arr = array();
		foreach($data as $v) {
			$arr['followers'] = $v['follows'];
			$arr['id'] = $v['id'];
			$arr['time'] = $v['time'];
			$arr['likesAnalytic'] = $v['likes'];
			$arr['commentsAnalytic'] = $v['comments'];
			$nhi[] = $arr;
		}
		$dbCacul = $m->instagram_account_info;
		$collection = $dbCacul->selectCollection('2016-10');
		$collection->batchInsert($nhi);
		
		echo "Complete!!!!";
		
	}
}