<?php
App::uses('AppController', 'Controller');
class MediaController extends AppController {
	public function index () {
		$this->layout = false;
		
		$id = '3579361643';
		
		$m = new MongoClient();
		$db = $m->instagram;
		$collections = $db->accounts;
		
		$query = array('user.id' => $id);
		$cursor = $collections->find($query,array());
		$acc = array();
		foreach ($cursor as $value){
			$acc = $value;
		}
		$this->set('inforAcc', $acc);
	}
	
	public function more(){
		$this->layout = false;
		$this->autoRender = false;
		
		$id = '3579361643';
		
		$m = new MongoClient();
		$db = $m->instagram;
		$collections = $db->media;

		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$limit = 20;
		$start= ($page*$limit)-$limit;
		
		$query = array('user.id' => $id);
		$cursor = $collections->find($query,array())->sort(array('created_time'=>-1))->skip($start)->limit($limit);
		
		$data= array();
		foreach ($cursor as $value){
			$data[]=$value;
		}
		return json_encode($data);
	}
	public function total(){
		$this->layout = false;
		$this->autoRender = false;
		
		$id = '3579361643';
		
		$m = new MongoClient();
		$db = $m->instagram;
		$collections = $db->accounts;
		
		$query = array('user.id' => $id);
		$cursor = $collections->find($query,array());
		$total = 0;
		foreach ($cursor as $value){
			$total = $value['user']['media']['count'];
		}
		return $total;
	}
}