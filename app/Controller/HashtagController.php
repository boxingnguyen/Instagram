<?php
class HashtagController extends AppController {
	public function index () {
		$db = $this->m->hashtag;
		$c = $db->ranking;
		if (isset($this->params['url']['sort'])) {
			$option = $this->params['url']['sort'];
			if (strtolower($option) == 'like') {
				$data = $c->find()->sort(array('total_likes' => -1));
			} else if (strtolower($option) == 'comment') {
				$data = $c->find()->sort(array('total_comments' => -1));
			} else {
				$data = $c->find()->sort(array('total_media' => -1));
			}
		} else {
			$data = $c->find()->sort(array('total_media' => -1));
		}
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
	public function media() {
		$tag = $_GET['hashtag'];
		$db = $this->m->hashtag;
		$c = $db->statistic;
 		$statistic = $c->find(array('hashtag' =>$tag));
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
	public function comment() {
		$tag = $_GET['hashtag'];
		$date = date("d-m-Y");
		$db = $this->m->hashtag;
		$c = $db->statistic;
		$statistic = $c->find(array('hashtag' =>$tag));
		$data = array();
		$i=0;
		foreach ($statistic as $val) {
			if($i==0) {
				$data[]= array("date"=>$val['date'],"total_comments"=>0);
			} else {
				$total = $val['total_comments'] - $tam;
				$data[]= array("date"=>$val['date'],"total_comments"=>$total);
			}
			$tam = $val['total_comments'];
			$i++;
		}
		$this->set('data', $data);
	}
	public function like() {
		$tag = $_GET['hashtag'];
		$db = $this->m->hashtag;
		$c = $db->statistic;
		$like = $c->find(array('hashtag' =>$tag));
		$like->sort(array('date' => 1));
		$data = array();
		$i=0;
		foreach ($like as $val) {
			if($i==0) {
				$data[]= array("date"=>$val['date'],"total_likes"=>0);
			} else {
				$total = $val['total_likes'] - $tam;
				$data[]= array("date"=>$val['date'],"total_likes"=>$total);
			}
			$tam = $val['total_likes'];
			$i++;
		}
		$this->set('data', $data);
	}
}