<?php
class HashtagController extends AppController {
	public function index () {
		$db = $this->m->hashtag;
		$c = $db->media_daily;
		
		$data = $c->find()->sort(array('total_media' => -1));
		
		$this->set('data', $data);
	}
	public function register() {
		$this->layout= false;
		$this->autoRender= false;
		if ($this->request->is('post')) {
			$tag = $this->request->data['hashtag'];
			
			// connect to mongo
			$db = $this->m->hashtag;
			$c = $db->tags;
			
			if ($c->count(array('tag' => $tag)) > 0) {
				return json_encode('This tag has been registered before!');
			} else {
				$insert = $c->insert(array('tag' => $tag));
				if ($insert) {
					return true;
				} else {
					return false;
				}	
			}
		}
	}
	
	public function detail() {	
	}
	
	public function media() {
		$tag = $_GET['hashtag'];
		$db = $this->m->hashtag;
		$c = $db->media_daily;
		$statistic = $c->find(array('tag' =>$tag))->sort(array('date' => 1));
		$data = array();
		$i=0;
		foreach ($statistic as $val) {
			if($i==0) {
				$data[]= array("date"=>$val['date'],"total_media"=>0);
			} else {
				$total = $val['total_media'] - $tam;
				$data[]= array("date"=>$val['date'],"total_media"=>$total);
			}
			$tam = $val['total_media'];
			$i++;
		}
		$this->set('data', $data);
	}
	
	
	public function more() {
		$this->layout = false;
		$this->autoRender = false;
		
		if (isset($_POST['tag'])){
			$tag = $_POST['tag'];
		}else {
			return false;
		}
		
		$db = $this->m->hashtag;
		$c = $db->media;
		
		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$limit = 20;
		$start= ($page*$limit)-$limit;
		
		if ($_POST['sort'] === 'like') {
			$sort = 'likes.count';
		}elseif ($_POST['sort'] === 'comment') {
			$sort = 'comments.count';
		}else {
			$sort = 'likes.count';
		}
 		
		$query = array('tag_name' => $tag);
		$cursor = $c->find($query,array())->sort(array($sort=>-1))->skip($start)->limit($limit);
		
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
		
		if (isset($_POST['tag'])){
			$tag = $_POST['tag'];
		}else {
			return false;
		}
		
		$db = $this->m->hashtag;
		$c = $db->media;
		
		$query = array('tag_name' => $tag);
		$total = $c->find($query,array())->count();
		return $total;
	}
}