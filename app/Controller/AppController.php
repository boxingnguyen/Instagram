<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');
App::import('Vendor','Package',array('file'=>'vendor/autoload.php'));
App::import('Vendor', 'instagram', array('file' => 'Instagram' . DS . 'src' . DS . 'Instagram.php'));

use MetzWeb\Instagram\Instagram;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package	app.Controller
 * @link	http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $m;
	protected $_token;
	const DEBUG = false;
	
	protected $_instagram;
	
	private $__apiKey = '38c0b7dbaec9477dbf4e88bcb6899071';
	private $__apiSecret = '2cd7341fe2704279a47db32fad98b1b1'; // QuyenAnhTMH
	
	// private $__apiKey = '6d34b43b41bd42a09f0762cd23363358';
	// private $__apiSecret = '532e8a5dc85346358104046673bf5376';
	
	// private $__apiKey = 'f972dee6a6b64abb9af5ec877a73c62c';
	// private $__apiSecret = '2dc4d6f730394fbbbe9120ee21d6190c';
	
	public function beforeFilter() {
		//get accessToken
		$m = new MongoClient;
		$db = $m->instagram_account_info;
		$colLogin = $db->account_login;
		$colUsername = $db->account_username;
		$usename = $this->Session->read('username');
		$id = $this->Session->read('id');
		$listToken = $colUsername->find(array('access_token' => array('$exists' => true)));
		if($listToken->count()>0){
			$accountLogin = $colUsername->find(array('id' => $id));
			foreach ($accountLogin as $value) {
				$this->_token = $value['access_token'];
			}
		}
		else{
			$accountLogin = $colLogin->find(array('id' => $id));
			foreach ($accountLogin as $value) {
				$this->_token = $value['access_token'];
			}
		}
		$apiCallback = "http://$_SERVER[HTTP_HOST]/Register/detail";
		//$apiCallback = "http://192.168.33.110/Test/detail";
		
		$this->_instagram = new Instagram(array(
		'apiKey'      => $this->__apiKey,
		'apiSecret'   => $this->__apiSecret,
		'apiCallback' => $apiCallback
		));
		
		//get information of param id from url
		if (isset($this->request->query['id'])){
			$id = $this->request->query['id'];
			$m = new MongoClient();
			$db = $m->instagram_account_info;
			$collection = $db->account_info;
			$data = $collection->find(array('id'=>$id));
			foreach ($data as $v) {
				$acc_infor = $v;
				$this->set('acc_infor',$acc_infor);
			}
		}
	}
	public function cURLInstagram($url) {
		$headerData = array('Accept: application/json');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, true);
		
		$jsonData = curl_exec($ch);
		// if get data failed, get it until successfully
		while (!$jsonData) {
		$jsonData = curl_exec($ch);
		}
		// split header from JSON data
		// and assign each to a variable
		list($headerContent, $jsonData) = explode("\r\n\r\n", $jsonData, 2);
		
		// convert header content into an array
		$headers = $this->__processHeaders($headerContent);
		
		if (!$jsonData) {
			throw new Exception('Error: _makeCall() - cURL error: ' . curl_error($ch));
		}
		curl_close($ch);
		return json_decode($jsonData);
	}
	private function __processHeaders($headerContent) {
		$headers = array();
		foreach (explode("\r\n", $headerContent) as $i => $line) {
			if ($i === 0) {
				$headers['http_code'] = $line;
				continue;
			}
			list($key, $value) = explode(':', $line);
			$headers[$key] = $value;
		}
		return $headers;
	}
}
