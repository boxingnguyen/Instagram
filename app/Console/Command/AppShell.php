<?php
/**
 * AppShell file
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
 * @since         CakePHP(tm) v 2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Shell', 'Console');
App::import('Vendor','Package',array('file'=>'vendor/autoload.php'));
App::import('Vendor', 'instagram', array('file' => 'Instagram' . DS . 'src' . DS . 'Instagram.php'));
use MetzWeb\Instagram\Instagram;
/**
 * Application Shell
 *
 * Add your application-wide methods in the class below, your shells
 * will inherit them.
 *
 * @package       app.Console.Command
 */
class AppShell extends Shell {
	private $__username = 'tmhtest';
	private $__password = '!!tmh!!';
	protected $_instagram;
	const DEBUG = false;
	
	protected $_insta;
	private $__apiKey = '68bed720dbd14812bfb01763b433d870';
	private $__apiSecret = 'b38ff515a4d040f3abb0abedb4b8849c';
	private $__apiCallback = '';
	
	public function initialize() {
		parent::initialize();
		ini_set('memory_limit', '1G');
		$this->_instagram = new \InstagramAPI\Instagram($this->__username,$this->__password,self::DEBUG);
		
		$this->_insta = new Instagram(array(
				'apiKey'      => $this->__apiKey,
				'apiSecret'   => $this->__apiSecret,
				'apiCallback' => $this->__apiCallback,
		));
	}
	
	public function cURLInstagram($url) {
		$headerData = array('Accept: application/json');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, true);

		$i = 0;
		do {
			if ($i >= 1) {
				$this->out($i . ': ' . $url);
			}
			if ($i > 10) {
				$this->out('Stop get data of ' . $url);
				break;
			}
			$jsonData = curl_exec($ch);
			list($headerContent, $jsonData) = array_pad(explode("\r\n\r\n", $jsonData, 2), 2, null);
				
			// convert header content into an array
			$headers = $this->__processHeaders($headerContent);
			$i ++;
		} while (!$this->isJSON($jsonData));

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
	public function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
}
