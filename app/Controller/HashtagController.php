<?php
class HashtagController extends AppController {
	public function index () {
		$db = $this->m->hashtag;
		$c = $db->ranking;
		$data = $c->find();
		$this->set('data', $data);
	}
	public function register(){
		$this->layout= false;
		$this->autoRender= false;
		if ($this->request->is('post')) {
			$tag = $this->request->data['hashtag'];
			
			// connect to mongo
			$db = $this->m->hashtag;
			$c = $db->tags;
			
			$insert = $c->insert(array('tag' => $tag));
			if ($insert) {
				return true;
			} else {
				return false;
			}
		}
	}
	public function detail() {
		$tag = $_GET['hashtag'];
		$date = date("d-m-Y");
		$db = $this->m->hashtag;
		$c = $db->statistic;
 		$statistic = $c->find(array('hashtag' =>$tag));
 		$data = array();
 		$i=0;
 		foreach ($statistic as $val){
 			if($i==0){
 				$data[]= array("date"=>$val['date'],"total_media"=>0);
 			}
 			else{
 				$total = $val['total_media'] - $tam;
 				$data[]= array("date"=>$val['date'],"total_media"=>$total);
 			}
 			$tam = $val['total_media']; 
 			$i++;
 			
 		}
 		
		$this->set('data', $data);
	}
}