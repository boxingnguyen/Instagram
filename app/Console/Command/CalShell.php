<?php
class CalculateReactionShell extends AppShell {
	public $db;
	public $collection_acc;
	public $collection_media;
	public $collection_reaction;

	public function initialize() {
		$m = new MongoClient();
		$this->db = $m->instagram;
		$this->collection_media = $this->db->media;
		$this->db = $m->instagram_account_info;
		$this->collection_acc = $this->db->account_info;
		$this->collection_reaction = $this->db->reaction;
	}
	public function main() {
		$start_time = microtime(true);

		$condition = array(
				'$group' => array(
					'_id' => '$id',
					'username' => array('$first' => '$username'),
					'fullname' => array('$first' => '$full_name'),
					'followers' => array('$first' => '$followed_by.count'),
					'media_count' => array('$first' => '$media.count')
				)
		);
		$data = $this->collection_acc->aggregate($condition);
		$count = 1;
		$result = $data['result'];
		$account_missing = array();
		foreach ($data['result'] as $key => $value) {
			$acc_id = $value['_id'];
			$reaction = $this->__calculateReaction($acc_id);
			if ($reaction['media_get'] < $value['media_count']) {
				$account_missing[$acc_id] = $value['username'];
				echo "Account has media missed: " . $value['username'] . ". Total: " . $value['media_count'] . ". Get: " . $reaction['media_get'] . PHP_EOL;
			}
			$result[$acc_id]['likes'] = $reaction['likes'];
			$result[$acc_id]['comments'] = $reaction['comments'];
			$result[$acc_id]['media_get'] = $reaction['media_get'];
			unset($result[$key]);
			echo $count . ". Reaction of " . $value['username'] . " completed!" . PHP_EOL;
			$count ++;
		}
		// if media of an account is missed, we re-get media
		while (!empty($account_missing)) {
			$account_missing = $this->__reGetMedia($account_missing);
			$result = $this->__updateReaction($account_missing, $result);
		}
		// write result (after calculating reaction) into database
		$this->collection_reaction->drop();
		$this->collection_reaction->batchInsert($result);

		$end_time = microtime(true);
		echo "Time to calculate reaction: " . ($end_time - $start_time) . " seconds" . PHP_EOL;
	}

/**
 * Calculate reaction of an account
 * @param unknown $account_id
 * @return array result
 */
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
		$data = $this->collection_media->aggregate($condition, array('maxTimeMS' => 3*60*1000));
		$result = array();
		$result['likes'] = isset($data['result'][0]['total_likes']) ? $data['result'][0]['total_likes'] : 0;
		$result['comments'] = isset($data['result'][0]['total_comments']) ? $data['result'][0]['total_comments'] : 0;
		$result['media_get'] = isset($data['result'][0]['media_get']) ? $data['result'][0]['media_get'] : 0;
		return $result;
	}

/**
 * Re-get media if media is missed
 * @param array $account
 */
	private function __reGetMedia($accounts) {
		foreach ($accounts as $key => $name) {
			$this->collection_media->remove(array('user.id' => $key));
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
				$max_id = null;
				$data = $this->__getMedia($name, $max_id);
				do {
					$data = $this->__getMedia($name, $max_id);
					// insert to mongo
					if(isset($data->items) && !empty($data->items)) {
						$this->collection_media->batchInsert($data->items, array('timeout' => -1));
						$max_id = end($data->items)->id;
					} else {
						break;
					}
				}
				while (isset ($data->more_available) && ($data->more_available == true || $data->more_available == 1));
				// Jump out of loop in this child. Parent will continue.
				echo "Reeeeeeeeeeeeeeee Get media of " . $name . " completed!" . PHP_EOL;
				// Re get account info for this account
				$account_info = $this->__getAccountInfo($name);
				$this->collection_acc->update(array("id" => $account_info->user->id), (array)$account_info->user);
				exit;
			}
		}
		// wait for all is completed
		foreach ($pids as $pid) {
			pcntl_waitpid($pid, $status);
			unset($pids[$pid]);
		}
		return $this->__reCheckMedia($accounts);
	}

/**
 * Check if we get 100% media or not
 * @param array list of account which have media missed before check
 * @return list of account which have media missed after check
 */
	private function __reCheckMedia($accounts) {
		$result = array();
		print_r($accounts);
		foreach ($accounts as $key => $value) {
			$account_info = $this->collection_acc->find(array('username' => $value), array('media.count' => true));
			print_r($account_info);
			// media count (base on account_info collection)
			foreach ($account_info as $value) {
				$media_origin = $value['media']['count'];
			}
			// media count (base on what we get)
			$media_get = $this->collection_media->count(array('user.id' => $key));
			if ($media_get != $media_origin) {
				$result[$key] = $value;
			}
		}
		return $result;
	}

/**
 * Get data of instagram's account
 * @param string $username
 * @return object account's data
 */
	private function __getAccountInfo($username) {
		$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/?__a=1');
		return $data;
	}

/**
 * Get account's media
 * @param string $username username
 * @param string $max_id   media's max_id
 */
	private function __getMedia($username, $max_id = null) {
		if ($max_id != null) {
			$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/media/?max_id=' . $max_id);
		} else {
			$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/media/');
		}
		return $data;
	}

/**
 * Update reaction of account which is miss media
 * @param array $accounts
 * @return boolean
 */
	private function __updateReaction($accounts, $reaction) {
		$condition = array(
				array('$match' => array ('id' => array('$in' => array_keys($accounts)))),
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
		$data = $this->collection_acc->aggregate($condition);
		$count = 1;
		$result = $data['result'];
		foreach ($data['result'] as $value) {
			$acc_id = $value['_id'];
			$result = $this->__calculateReaction($acc_id);
			$reaction[$acc_id]['likes'] = $result['likes'];
			$reaction[$acc_id]['comments'] = $result['comments'];
			$reaction[$acc_id]['media_get'] = $result['media_get'];
			echo $count . ". Reaction of " . $value['username'] . " completed! (Updated)" . PHP_EOL;
			$count ++;
		}
		return $reaction;
	}

	public function test() {
		$acc = $this->__getAccountInfo('instagram');
		print_r($acc); die;
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$c = $db->account_info;
		$account_info = $c->find(array('username' => 'instagram'), array('media.count' => true));
		$media = $this->collection->count(array('user.id' => '25025320'));
		echo $media; die;
		foreach ($account_info as $value) {
			print_r($value);
		}
	}
}
