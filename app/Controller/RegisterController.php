<?php
App::import(
		'Vendor',
		'instagram',
		array('file' => 'Instagram' . DS . 'src' . DS . 'Instagram.php')
		);
App::uses('Controller', 'Controller');
use MetzWeb\Instagram\Instagram;
class RegisterController extends AppController {
	private $__instagram;
	public function beforeFilter() {
		$this->__instagram = new Instagram(array(
				'apiKey'      => 'f31c3725215449c6bde2871932e7bc15',
				'apiSecret'   => '0a64babe62df4bba919dcd685e85eead',
				'apiCallback' => 'http://192.168.33.20/PHPInstagram/Register/detail',
				'scope'       => array( 'likes', 'comments', 'relationships','basic','public_content','follower_list' )
		));
	}
	public function login() {
		$url = $this->__instagram->getLoginUrl();
		$this->set('instagrams', $url);
	}
	public function detail() {
		$m = new MongoClient;
		$db = $m->instagram_account_info;
		$collections = $db->account_username;
		
		$code = $_GET['code'];
		$data = $this->__instagram->getOAuthToken($code);
		$id = $data->user->id;
		
		$setId = $collections->find(array('id' => $id))->count();
		if ($setId > 0) {
			$collections->update(array(
					array('id' => $id),
					array(
							'access_token' => $data->access_token,
							'username' => $data->user->username
					),
					array('multiple' => true)
			));
		} else {
			$collections->insert(array(
					'access_token' => $data->access_token,
					'id' => $id,
					'username' => $data->user->username
			));
		}
	}
}















