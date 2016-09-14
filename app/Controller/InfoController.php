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
// 		3089104174(dat), 3723129539(t.anh), 1970242460(Quy),1576391553(Son), 3597381506(hoc),3878933194(Thao),1943948110(Duc)
		$account = array('2124049456','3579361643','2996660725','3089104174','3723129539','1970242460','1576391553','3597381506','3878933194','1943948110');
		$arrMedia = array();$i = 0;
		foreach ($account as $arrKey => $arrId) {
			$mediaId = $this->instagram->getUserMedia($arrId);
			if(isset($mediaId) && !empty($mediaId->data)){
				$collection->batchInsert($mediaId->data);
			} else {
				continue;
			}
			
		}
	}
}