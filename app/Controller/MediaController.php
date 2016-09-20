<?php
App::uses('AppController', 'Controller');
class MediaController extends AppController {
	public function index () {
		$this->layout = false;
		if (isset($this->params->query['id']) && !empty($this->params->query['id'])){
			$id = $this->params->query['id'];
			if($this->Session->check('User.id')){
				$this->Session->delete('User.id');
				$this->Session->write('User.id',$id);
			}else{
				$this->Session->write('User.id',$id);
			}
			
			$m = new MongoClient();
			$db = $m->instagram_account_info;
			$collections = $db->account_info;
			
			$query = array('id' => $id);
			$cursor = $collections->find($query,array());
			$acc = array();
			foreach ($cursor as $value){
				$acc = $value;
			}
			$this->set('inforAcc', $acc);
		}else {
			$this->redirect(array("controller" => "top","action" => "index"));
		}
	}
	
	public function more(){
		$this->layout = false;
		$this->autoRender = false;

		if($this->Session->check('User.id')){
			$id = $this->Session->read('User.id');
		}else{
			$id = '';
		}
		
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
			$value['likes']['count'] = number_format($value['likes']['count']);
			$value['comments']['count'] = number_format($value['comments']['count']);
			$data[]=$value;
		}
		return json_encode($data);
	}
	public function total(){
		$this->layout = false;
		$this->autoRender = false;
		
		if($this->Session->check('User.id')){
			$id = $this->Session->read('User.id');
		}else{
			$id = '';
		}
		
		$m = new MongoClient();
		$db = $m->instagram_account_info;
		$collections = $db->account_info;
		
		$query = array('user.id' => $id);
		$cursor = $collections->find($query,array());
		$total = 0;
		foreach ($cursor as $value){
			$total = $value['user']['media']['count'];
		}
		return $total;
	}
}