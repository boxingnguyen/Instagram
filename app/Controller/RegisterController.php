<?php
App::uses('Controller', 'Controller');
class RegisterController extends AppController {
	public function login() {
		$scope = array('basic','followers_list');
		$url = $this->_instagram->getLoginUrl($scope);
		$this->set('instagrams', $url);
	}
	
	public function logout() {
		$this->layout= false;
		$this->autoRender= false;

		if($this->Session->check('username')){
			$usename = $this->Session->read('username');

			$m = new MongoClient;
			$db = $m->instagram_account_info;
			$collections = $db->account_username;
			$testUsername = $collections->find(array('username' => $usename));
			if($testUsername->count() == 0) {
				//if username not collections account_username => delete username in caculalor
				if (date('d') == '01') {
					$month = (new DateTime())->modify('-1 month')->format('m');
					$day = cal_days_in_month(CAL_GREGORIAN,$month,date('Y'));
					$currentTime = date('Y')."-".$month."-".$day;
				} else {
					$currentTime = (new DateTime())->modify('-1 day')->format('Y-m-d');
				}
					
				$time = date('Y-m', strtotime($currentTime));
				$dbAccount = $m->instagram_account_info;
				$collectionCaculate = $dbAccount->selectCollection($time);
				$collectionCaculate->remove(array('username' => $usename));
			}
			$this->Session->delete('username');
			return true;
		}
	}
	
	public function register(){
		$this->layout= false;
		$this->autoRender= false;
		if(isset($_POST['username'])){
			$username = $_POST['username'];
			$m = new MongoClient();
			$db = $m->instagram_account_info;
			$collection = $db->account_username;
			$exist = $collection->find(array('username'=>$username))->count();
			if(!$exist == 0){
				return json_encode("The account had added before!");
			}
			else{
				$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/?__a=1');
				if(isset($data)){
					$id = $data->user->id;
					// save to mongo db
					$collection->insert(array('username'=>$username,'id'=>$id));
					return json_encode("The account is added successfully!");
				}
				else{
					// alert username doesn't exist
					return json_encode("The account doesn't exist, please fill again!");
				}
			}
		}else{
			return false;
		}
	}
	
	public function detail() {
		if (isset($_GET['code'])) {
			$m = new MongoClient;
			$db = $m->instagram_account_info;
			$collections = $db->account_login;
			$date = date("dmY");
			
			$code = $_GET['code'];
			$data = $this->_instagram->getOAuthToken($code);
			$id = $data->user->id;
			$username = $data->user->username;
			
			//write username into session
			if($this->Session->check('username')){
				$this->Session->delete('username');
			}
			$this->Session->write('username', $username);
			
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
			$this->__saveMediaIntoDb($media, $username);
			$totalAccountInfo = $this->__totalAccountInfo($username);
			$totalMediaTop = $this->__totalMedia($username);
			$mediaTop = array('id' => $totalMediaTop['id'], 'likesTop' => $totalMediaTop['likes'], 'commentsTop' => $totalMediaTop['comments'], 'media_get' => $totalMediaTop['media_get']);
			$date = (new DateTime())->format('Y-m-d 00:00:00');
			$date = (string)strtotime($date);
			$totalMediaAnalytic = $this->__totalMedia($username, $date);
			$mediaAnalytic = array('likesAnalytic' => $totalMediaAnalytic['likes'], 'commentsAnalytic' => $totalMediaAnalytic['comments']);
			$this->__calculateReaction($username,$totalAccountInfo, $mediaTop, $mediaAnalytic);

			// after get data successful, redirect to Top page
			$this->redirect(array('controller' => 'top', 'action' => 'index'));
		} else {
			$this->redirect( array('controller' => 'register','action' => 'login' ));
		}	
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
	
	private function __saveMediaIntoDb($media, $username) {
		$m = new MongoClient;
		$db = $m->instagram;
		$collection = $db->media;
		if(isset($media) && count($media) > 0) {
			$collection->remove(array("user.username" => $username));
		}
		$collection->batchInsert($media, array('timeout' => -1));
	}
	private function __totalAccountInfo($username) {
		//get data to account_info
		$m = new MongoClient;
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
		$result['is_private'] = isset($dataInfo['result'][0]['is_private']) ? $dataInfo['result'][0]['is_private'] : '';
		$result['username'] = isset($dataInfo['result'][0]['username']) ? $dataInfo['result'][0]['username'] : '';
		$result['fullname'] = isset($dataInfo['result'][0]['fullname']) ? $dataInfo['result'][0]['fullname'] : '';
		$result['media_count'] = isset($dataInfo['result'][0]['media_count']) ? $dataInfo['result'][0]['media_count'] : 0;
		$result['followers'] = isset($dataInfo['result'][0]['followers']) ? $dataInfo['result'][0]['followers'] : 0;
		return $result;
	}
	
	private function __totalMedia($username, $date = null) {
		$m = new MongoClient;
		$db = $m->instagram;
		$collection = $db->media;
		//get data to media
		if($date == null) {
			// data in top page
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
		} else {
			// data in analysis pages
			$condition = array(
					array('$match' => array('user.username' => $username, 'created_time' => array('$lt' => $date))),
					array(
							'$group' => array(
									'_id' => '$user.id',
									'total_likes' => array('$sum' => '$likes.count'),
									'total_comments' => array('$sum' => '$comments.count'),
							)
					)
			);
		}
		$data = $collection->aggregate($condition, array('maxTimeMS' => 3*60*1000));
		$result['id'] = isset($data['result'][0]['_id']) ? $data['result'][0]['_id'] : 0;
		$result['likes'] = isset($data['result'][0]['total_likes']) ? $data['result'][0]['total_likes'] : 0;
		$result['comments'] = isset($data['result'][0]['total_comments']) ? $data['result'][0]['total_comments'] : 0;
		$result['media_get'] = isset($data['result'][0]['media_get']) ? $data['result'][0]['media_get'] : 0;
		return $result;
	}
	private function __calculateReaction($username, $totalAccountInfo, $mediaTop, $mediaAnalytic) {
		// if the day is 1th of month we'll get data of the last day of previous month and save to db
		if (date('d') == '01') {
			$month = (new DateTime())->modify('-1 month')->format('m');
			$day = cal_days_in_month(CAL_GREGORIAN,$month,date('Y'));
			$currentTime = date('Y')."-".$month."-".$day;
		} else {
			$currentTime = (new DateTime())->modify('-1 day')->format('Y-m-d');
		}
		
		$m = new MongoClient;
		$dbAccount = $m->instagram_account_info;
		$time = date('Y-m', strtotime($currentTime));
		$collectionCaculate = $dbAccount->selectCollection($time);
		
		$dateCurrent = $collectionCaculate->find(array('username' => $username, 'time' => $currentTime));
		if($dateCurrent->count() > 0){
			$collectionCaculate->remove(array(
					'username' => $username,
					'time' => $currentTime
			));
		}
		$date['time'] = $currentTime;
		$result = array_merge($totalAccountInfo, $mediaTop, $mediaAnalytic, $date);
		$collectionCaculate->insert($result);
		
		
	}
	
	public function register_hashtag($tags) {
		$tags = 'cat';
		$max_id = null;
		do {
			$data = $this->cURLInstagram('https://www.instagram.com/explore/tags/' . $tags . '/?__a=1&');
			print_r($data); break;
		} while (true);
	}

}