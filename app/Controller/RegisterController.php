<?php
App::uses('Controller', 'Controller');
class RegisterController extends AppController {
	public function login() {
		$url = $this->_instagram->getLoginUrl();
		$this->set('instagrams', $url);
	}
	public function detail() {
		$m = new MongoClient;
		$db = $m->instagram_account_info;
		$collections = $db->account_username;
		$date = date("dmY");
		
		$code = $_GET['code'];
		$data = $this->_instagram->getOAuthToken($code);
		$id = $data->user->id;
		$username = $data->user->username;
		
		$setId = $collections->find(array('id' => $id))->count();
		if ($setId > 0) {
			$collections->remove(array('id' => $id));
		} 
		$collections->insert(array(
				'access_token' => $data->access_token,
				'id' => $id,
				'username' => $username
		));
		
		// get account info
		$acc_info = $this->__getAccountInfo($username);
		// save account info into db
		$this->__saveAccountIntoDb($acc_info->user);
		// get media
		$media = $this->__getMedia($id, $data->access_token, $date);
		// save account info into db
		$this->__saveMediaIntoDb($media);
		// calculate reaction for this account
		$this->__calculateReaction($username);
		
		// after get data successful, redirect to Top page
		$this->redirect(array('controller' => 'top', 'action' => 'index'));
	}
	
	private function __getAccountInfo($username) {
		$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/?__a=1');
		return $data;
	}
	
	private function __getMedia($id, $access_token, $date) {
		$this->_instagram->setAccessToken($access_token);
		$max_id = null;
		$data = array();
		do {
			$media = $this->_instagram->getUserMedia($id, 10, $max_id);
			foreach ($media->data as $val) {
				$data[] = $val;
			}
			if (isset($media->pagination) && !empty($media->pagination->next_max_id)) {
				$max_id = $media->pagination->next_max_id;
			} else {
				$max_id = null;
				break;
			}
		} while ($max_id != null);
		return $data;
	}
	
	private function __saveAccountIntoDb($acc_info) {
		$m = new MongoClient;
		$db = $m->instagram_account_info;
		$collection = $db->account_info;
		// insert new data
		$data = $collection->find(array('username' => $acc_info->username))->count();
		if($data > 0) {
			$collection->remove(
				array('username' => $acc_info->username)		
			);
		}
		$collection->insert($acc_info, array('timeout' => -1));
	}
	
	private function __saveMediaIntoDb($media) {
		$m = new MongoClient;
		$db = $m->instagram;
		$collection = $db->media;
		if(isset($media) && count($media) > 0) {
			$collection->remove(array());
		}
		$collection->batchInsert($media, array('timeout' => -1));
	}
	
	private function __calculateReaction($username) {
		$m = new MongoClient;
		$db = $m->instagram;
		$collection = $db->media;
		//get data to media
		$condition = array(
				array('$match' => array('user.username' => $username)),
				array(
						'$group' => array(
								'_id' => '$user.id',
								'total_likes' => array('$sum' => '$likes.count'),
								'total_comments' => array('$sum' => '$comments.count'),
								'media_get' => array('$sum' => 1)
						)
				)
		);
		$data = $collection->aggregate($condition, array('maxTimeMS' => 3*60*1000));
		$result['id'] = isset($data['result'][0]['_id']) ? $data['result'][0]['_id'] : 0;
		$result['likesTop'] = isset($data['result'][0]['total_likes']) ? $data['result'][0]['total_likes'] : 0;
		$result['commentsTop'] = isset($data['result'][0]['total_comments']) ? $data['result'][0]['total_comments'] : 0;
		$result['media_get'] = isset($data['result'][0]['media_get']) ? $data['result'][0]['media_get'] : 0;
		
		//get data to account_info
		$dbAccount = $m->instagram_account_info;
		$collectionInfo = $dbAccount->account_info;
		$conditionInfo = array(
				array('$match' => array('username' => $username)),
				array(
						'$group' => array(
							'_id' => '$id',
							'username' => array('$first' => '$username'),
							'fullname' => array('$first' => '$full_name'),
							'followers' => array('$first' => '$followed_by.count'),
							'media_count' => array('$first' => '$media.count'),
							'is_private' => array('$first' => '$is_private')
						)
				)
		);
		$dataInfo = $collectionInfo->aggregate($conditionInfo);
// 		print_r($dataInfo);
		$result['username'] = isset($dataInfo['result'][0]['username']) ? $dataInfo['result'][0]['username'] : '';
		$result['fullname'] = isset($dataInfo['result'][0]['fullname']) ? $dataInfo['result'][0]['fullname'] : '';
		$result['media_count'] = isset($dataInfo['result'][0]['media_count']) ? $dataInfo['result'][0]['media_count'] : 0;
		$result['followers'] = isset($dataInfo['result'][0]['followers']) ? $dataInfo['result'][0]['followers'] : 0;
		
		$result['likesAnalytic'] = 0;
		$result['commentsAnalytic'] = 0;
		
		
		
		// if the day is 1th of month we'll get data of the last day of previous month and save to db
		if (date('d') == '01') {
			$month = (new DateTime())->modify('-1 month')->format('m');
			$day = cal_days_in_month(CAL_GREGORIAN,$month,date('Y'));
			$currentTime = date('Y')."-".$month."-".$day;
		} else {
			$currentTime = (new DateTime())->modify('-1 day')->format('Y-m-d');
		}
		$time = date('Y-m', strtotime($currentTime));
		$collectionCaculate = $dbAccount->selectCollection($time);
		
		$dateCurrent = $collectionCaculate->find(array('username' => $username, 'time' => $currentTime));
		if($dateCurrent->count() > 0){
			$collectionCaculate->remove(array(
					'username' => $username,
					'time' => $currentTime
			));
		}
		$result['time'] = $currentTime;
		$collectionCaculate->insert($result);
		
		
	}

}