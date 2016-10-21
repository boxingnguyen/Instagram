<?php
class HashtagController extends AppController {
	public function index () {
	}
	public function register(){
		$this->layout= false;
		$this->autoRender= false;
		if(isset($_POST['hashtag'])){
			return true;
		}else{
			return false;
		}
	}
}