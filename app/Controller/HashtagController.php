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
				$data = $c->find();
			}
		} else {
			$data = $c->find();
		}
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
		
	}
}