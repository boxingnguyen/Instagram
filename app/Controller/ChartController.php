<?php
App::uses('AppController', 'Controller');
class ChartController extends AppController {
	public function getReaction () {
		$this->layout = false;
		$this->autoRender = false;
		
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->data;
		$condition = array(
				'$group' => array(
						'_id' => '$user.id',
						'username' => array('$first' => '$user.username'),
						'total_likes' => array('$sum' => '$likes.count'),
						'total_comments' => array('$sum' => '$comments.count'),
						'reaction' => array('$sum' => array('$add' => array('$likes.count', '$comments.count')))
				)
		);
		$data = $collection->aggregate($condition);
		echo json_encode($data['result']);
	}
	
	public function getPostByMonth () {
		$this->layout = false;
		$this->autoRender = false;
	
		$m = new MongoClient();
		$db = $m->instagram;
		$collection = $db->data;
		$condition = array(
				'created_time' => array (
						'$gte' => '1467331200',
						'$lte' => '1473465600'
// 						'$group' => array(
// 								'_id' => array (
// 										'year' => array('$year' => '2016'),
// 										'month' => array('$month' => '08'),
// 								),
// 								'count' => array('$sum' => 1)
// 						)
				)
		);
		$data = $collection->find($condition);
		$data = $collection->count();
		echo $data; die;
		echo "<pre>";
		foreach ($data as $value) {
			print_r($value);
		}
// 		echo json_encode($data['result']);
	}
}