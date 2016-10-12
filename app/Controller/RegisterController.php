<?php
App::uses('Controller', 'Controller');
class RegisterController extends AppController {
	public function login() {
		$scope = array('public_content','basic');
		$url = $this->_instagram->getLoginUrl($scope);
		$this->set('instagrams', $url);
	}
	public function detail() {
		if(isset($_GET['code'])){
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
				$collections->insert(array(
						'access_token' => $data->access_token,
						'id' => $id,
						'username' => $username
				));
			}
			// get account info
			$acc_info = $this->__getAccountInfo($username);
			// save account info into db
			$this->__saveAccountIntoDb($acc_info->user);
			// get media
			$media = $this->__getMedia($username, $data->access_token, $date);
			// save account info into db
			$this->__saveMediaIntoDb($media);
			// calculate reaction for this account
			$this->__calculateReaction($username);

			// after get data successful, redirect to Top page
			$this->redirect(array('controller' => 'top', 'action' => 'index'));
		}else {
			$this->redirect( array('controller' => 'register','action' => 'login' ));
		}	
	}
	
	private function __getAccountInfo($username) {
		$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/?__a=1');
		return $data;
	}
	
	private function __getMedia($username, $access_token, $date) {
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
		$collection->insert($acc_info, array('timeout' => -1));
	}
	
	private function __saveMediaIntoDb($media) {
		$m = new MongoClient;
		$db = $m->instagram;
		$collection = $db->media;
	
		// insert new data
		$collection->batchInsert($media, array('timeout' => -1));
	}
	
	private function __calculateReaction($username) {
		
	}

}















