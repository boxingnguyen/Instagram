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

}















