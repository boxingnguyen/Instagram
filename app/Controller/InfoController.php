<?php

class InfoController extends AppController {
	public $instagram;
	public function beforeFilter() {
		$this->instagram = new Instagram(array(
			'apiKey'      => '9a0eb7b3e06949b98980256fccf93599',
			'apiSecret'   => 'eeaeda3bc5774eb196e53d064e41c7b5',
			'apiCallback' => 'http://192.168.33.30/Info/getUseNotAPI',
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

















