<?php
class HashtagController extends AppController {
	public function index () {
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
	public function ranking() {
		
	}
}