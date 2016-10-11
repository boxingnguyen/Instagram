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
		
		$code = $_GET['code'];
		$data = $this->_instagram->getOAuthToken($code);
		$id = $data->user->id;
		print_r($data);
		$setId = $collections->find(array('id' => $id))->count();
		if ($setId > 0) {
			$collections->remove(array('id' => $id));
		} 
		$collections->insert(array(
				'access_token' => $data->access_token,
				'id' => $id,
				'username' => $data->user->username
		));
		$this->redirect( array('controller' => 'top','action' => 'index' ));
		
	}
	public function test() {
		$m = new MongoClient;
		$db = $m->instagram_account_info;
		$collections = $db->account_username;
		$result = $collections->find(array('username' => 'ylangincenseaa'));
		foreach ($result as $v) {
			if(isset($v['access_token'])){
				$id = $v['id'];
				$this->_instagram->setAccessToken($v['access_token']);
				$max_id = null;
				$arr = array();
				do {
					$media = $this->_instagram->getUserMedia($id, 2, $max_id);
// 					if(isset($media) && !empty($media->data)) {
// 						$arr[] = $media->data;
// 					}
					if(isset($media->pagination) && !empty($media->pagination->next_max_id)) {
						$max_id = $media->pagination->next_max_id;
					} else {
						$max_id = null;
						break;
					}
					echo "nhi .....";
					print_r($max_id);
				} while ($max_id != null);
				
				
				
				echo "<pre>";
				print_r($arr);
			}
		}
	}
}















