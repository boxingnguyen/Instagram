<?php
App::import('Vendor', 'Instagram', array('file' => 'Instagram/src/Instagram.php'));
use MetzWeb\Instagram\Instagram;

class InfoController extends AppController {
	public $instagram;
	public function beforeFilter() {
		$this->instagram = new Instagram(array(
			'apiKey'      => 'f31c3725215449c6bde2871932e7bc15',
			'apiSecret'   => '0a64babe62df4bba919dcd685e85eead',
			'apiCallback' => 'http://192.168.0.145/PHPInstagram/Info/mediaRecent',
			'scope'       => array( 'likes', 'comments', 'relationships','basic','public_content','follower_list' )
		));
	}
	public function index() {
		$this->set('urlLogin',$this->instagram->getLoginUrl());
	}
	public function mediaRecent() {
		$this->layout = false;
		$this->autoRender = false;
		
		$m = new MongoClient();
		$db = $m->Instagram;
		$collection = $db->mediaAccount;
		
		$code = $_GET['code'];
		$data = $this->instagram->getOAuthToken($code);
		$this->instagram->setAccessToken($data);
// 		$mediaId = $this->instagram->getUserMedia('2124049456');
		
		$account = array('2124049456','3579361643','2996660725');
		$arrMedia = array();
		foreach ($account as $arrId) {
			$mediaId = $this->instagram->getUserMedia($arrId);
			$arrMedia[$arrId] = $mediaId->data;
		}
		
		$collection->batchInsert($arrMedia);
		
		echo "<pre>";
		print_r($arrMedia);
		echo "</pre>";
	}
}