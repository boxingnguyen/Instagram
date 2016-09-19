<?php

class InfoController extends AppController {
	public $instagram;
	public function beforeFilter() {
		$this->instagram = new Instagram(array(
			'apiKey'      => 'f31c3725215449c6bde2871932e7bc15',
			'apiSecret'   => '0a64babe62df4bba919dcd685e85eead',
			'apiCallback' => 'http://192.168.33.20/PHPInstagram/Info/getUseNotAPI',
			'scope'       => array( 'likes', 'comments', 'relationships','basic','public_content','follower_list' )
		));
	}
	public function index() {
		$this->set('urlLogin',$this->instagram->getLoginUrl());
	}
	
	public function getUseNotAPI() {
		$this->layout = false;
		$this->autoRender = false;
		
		$m = new MongoClient();
		$db = $m->Instagram;
		$collection = $db->accountNotAPI;
		
		$nameAccount = array();
		$file = "../Vendor/username.txt";
		$fl = fopen($file,'r');
		
		while (!feof($fl)) {
			$nameAccount[] = trim(preg_replace('/\s\s+/', ' ', fgets($fl)));;
		}
		
// 		$nameAccount = array('smsaruae','kicks4sale');
		if (isset($nameAccount) && !empty($nameAccount)) {
			foreach ($nameAccount as $name) {
				$dataAccount = $this->instagram->getUserNotAPI($name);
				if(isset($dataAccount) && !empty($dataAccount)) {
					$collection->insert($dataAccount->user);
				}
			}
		}
	}
}

















