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
						'followers' => array('$first' => '$followed_by.count'),
						'media_count' => array('$first' => '$media.count')
					)
				)
				
		);
		$data = $collection->aggregate($condition);
		// split accounts into chunks
		$chunks = array_chunk($data['result'], 5);
				
		foreach ($chunks as $account_list) {
			foreach ($account_list as $key => $value) {
				// create 2 processes here
				$pid = pcntl_fork();
		
				if ($pid == -1) {
					die('could not fork');
				} else if ($pid) {
					// we are the parent
					// collect process id to know when children complete
					$pids[] = $pid;
				} else {
					// we are the child
					$data['result'][$key]['likes'] = $this->__calculateReaction($value['_id'])['likes'];
					$data['result'][$key]['comments'] = $this->__calculateReaction($value['_id'])['comments'];
					exit;
				}
			}
			foreach ($pids as $pid) {
				pcntl_waitpid($pid, $status);
				unset($pids[$pid]);
			}
		}
		print_r($data['result']);
	}
	
 	private function __calculateReaction($account_id) {
		$condition = array(
				array('$match' => array('user.id' => $account_id)),
				array(
						'$group' => array(
								'_id' => '$user.id',
								'username' => array('$first' => '$user.username'),
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