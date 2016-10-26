<?php
class HashtagShell extends AppShell {
	const TMHTEST_ACCESS_TOKEN = '4025731782.6d34b43.b723850c5e0548bfa4863dea62b98630';
	
	public function getPosttop ($tag){
		$results_array = $this->cURLInstagram('https://www.instagram.com/explore/tags/'.$tag.'/?__a=1');
		if(isset($results_array)&&!empty($results_array)){
			$total_media = $results_array->tag->media->count;
			$media=$results_array->tag->top_posts->nodes;
			$myfile = fopen(APP . "Vendor/Hashtag/" . date('dmY') . "." . $tag . ".media.hashtag.json", "w+") or die("Unable to open file!");
			$mediaArray = array();
			foreach ($media as  $value) {
				$value->tag_name = $tag;
				fwrite($myfile, json_encode($value)."\n");
				array_push($mediaArray, $value);
			}
			$mediaArray['total_media']=$total_media;
			fclose($myfile);
			return $mediaArray;
		}
		
	}
	public function calculator($tag){
		$m = new MongoClient();
		$db = $m->hashtag;
		$collection = $db->media;
		$condition = array(
				array('$match' => array('tag_name' => $tag)),
				array(
						'$group' => array(
								'_id' => 'null',
								'total_likes' => array('$sum' => '$likes.count'),
								'total_comments' => array('$sum' => '$comments.count')
						)
				)
		);
		$a = $this->getPosttop($tag);
		$data = $collection->aggregate($condition);
		$result = array();
		$result['total_likes'] = $data['result'][0]['total_likes'];
		$result['total_comments'] = $data['result'][0]['total_comments'];
		$result['total_media'] = $a['total_media'];
		$result['hashtag'] = $tag;
		$db->ranking->insert($result);
	}
	public function main() {
// 		$this->__getMediaApi();

		$m = new MongoClient();
		$db = $m->hashtag;
		$c_media = $db->media;

		$date = date('dmY');
		$hashtag = $this->__getHashtag();
		foreach ($hashtag as $tag) {
			// create 2 processes here
			$pid = pcntl_fork();
			if ($pid == -1) {
				die('could not fork');
			} else if ($pid) {
				// we are the parent
				// collect process id to know when children complete
				$pids[] = $pid;
			} else {
				// we are childs
				echo "Get hashtag of " . $tag . '...' . PHP_EOL;
				$max_id = null;
				// write data into json file
				$count = 0;
				$tmp = array ();
				$myfile = fopen(APP . "Vendor/Hashtag/" . $date . "." . $tag . ".media_hashtag.json", "w+") or die("Unable to open file!");
				do {
					$media = $this->__getMediaHttp($tag, $max_id);
					
					if (!isset($media->tag->media->nodes) || empty($media->tag->media->nodes)) {
						print_r($media);
						echo "Last media of " . $tag . ": " . PHP_EOL;
						print_r(end($tmp));
						break;
					}
					$data = $media->tag->media->nodes;
					$tmp = $data;
					foreach ($data as $value) {
						$count ++;
						// do not get media of October
						if (isset($value->created_time) && intval(date('m', $value->created_time)) > 9 || isset($value->date) && intval(date('m', $value->date)) > 9) {
							continue;
						} else if (isset($value->created_time) && intval(date('m', $value->created_time)) < 9 || isset($value->date) && intval(date('m', $value->date)) < 9) {
							// do not get media of month before September
							break;
						} else {
							if (isset($value->created_time)) {
								$value->created_time = date('d-m-Y', $value->created_time);
							} else if (isset($value->date)) {
								$value->date = date('d-m-Y', $value->date);
							}
							fwrite($myfile, json_encode($value) . "\n");
						}
					}
					// get next page of data
					$max_id = isset($media->tag->media->page_info->end_cursor) ? $media->tag->media->page_info->end_cursor : null;
				} while ($media->tag->media->page_info->has_next_page == 1);
				
				echo "Total media of " . $tag . ": " . $count . PHP_EOL;
				
				echo "Get HASHTAG of " . $tag . " completed!" . PHP_EOL;
				
				$this->__saveIntoDb($tag, $c_media, $date);
				
				echo "Save data of #" . $tag . " into DB completed!" . PHP_EOL;
				
				// Jump out of loop in this child. Parent will continue.
				exit;
			}
		}
		foreach ($pids as $pid) {
			pcntl_waitpid($pid, $status);
			unset($pids[$pid]);
		}
	}
	
	private function __getHashtag() {
		$m = new MongoClient();
		$db = $m->hashtag;
		$c = $db->tags;
		$hashtag = $c->find(array(), array('tag' => true, '_id' => false));
		$tags = array();
		foreach ($hashtag as $value) {
			$tags[] = str_replace("#", "", $value['tag']);
		}
		return $tags;
	}
	
	private function __getMediaHttp($tag, $max_id) {
		if ($max_id != null) {
			$media = $this->cURLInstagram('https://www.instagram.com/explore/tags/'.$tag.'/?__a=1&max_id=' . $max_id);
		} else {
			$media = $this->cURLInstagram('https://www.instagram.com/explore/tags/'.$tag.'/?__a=1');
		}
		return $media;
	}
	
	private function __saveIntoDb($tag, $collection, $date) {
		$filename = APP . "Vendor/Hashtag/" . $date . "." . $tag . ".media_hashtag.json";
		$all_lines = file($filename);
		if (empty($all_lines)) {
			echo "#" . $tag . " has no media or something wrong!" . PHP_EOL;
			return;
		}
		$part = (int)(count($all_lines) / 1000) + 1;
		$start = 0;
		if ($part == 1) {
			$count_get = count($all_lines) % 1000;
		} else {
			$count_get = 1000;
		}
		$data = array();
		for ($i = 0; $i < $part; $i++) {
			$my[$i] = array_slice($all_lines, $start, $count_get );
			if ($i < $part - 1) {
				$start = $start + 1000;
			} else {
				$start = $start + 1000;
				$count_get = count($all_lines) % 1000;
			}
		}
	
		for ($i = 0; $i < $part ; $i++) {
			foreach ($my[$i] as $value) {
				$data[] = json_decode($value);
			}
			$collection->batchInsert($data, array('timeout' => -1));
			unset($data);
		}
	}
	
	private function __getMedia($tag) {
		$this->_insta->setAccessToken(self::TMHTEST_ACCESS_TOKEN);
		$media = $this->_insta->getTagMedia($tag);
		return $media;
	}
	
	private function __getMediaApi() {
		$m = new MongoClient();
		$db = $m->hashtag;
		$c_media = $db->media;
		$c_media->drop();
		
		$hashtag = $this->__getHashtag();
		
		$hashtag_chunk = array_chunk($hashtag, 15);
		
		foreach ($hashtag_chunk as $hashtag) {
			foreach ($hashtag as $tag) {
				// create 2 processes here
				$pid = pcntl_fork();
				if ($pid == -1) {
					die('could not fork');
				} else if ($pid) {
					// we are the parent
					// collect process id to know when children complete
					$pids[] = $pid;
				} else {
					// we are childs
					echo "Get hashtag of " . $tag . '...' . PHP_EOL;
					$media = $this->__getMedia($tag);
					$media_insert = array();
					do {
						if (isset($media->data)) {
							$data = (array)$media->data;
						} else {
							print_r($media);
							break;
						}
							
						foreach ($data as $value) {
							// do not get media of October
							if (intval(date('m', $value->created_time)) > 9) {
								continue;
							} else if (intval(date('m', $value->created_time)) < 9) {
								// do not get media of month before September
								break;
							} else {
								$media_insert[] = $value;
							}
						}
						// batch insert if data is large enough
						if (count($media_insert) > 1000) {
							$c_media->batchInsert($media_insert);
							unset($media_insert);
						}
						// get next page of data
						$media = $this->_insta->pagination($media, 100);
					} while (true);
					// insert the remaining media
					if (!empty($media_insert)) {
						$c_media->batchInsert($media_insert);
					}
					echo "Get HASHTAG of " . $tag . " completed!" . PHP_EOL;
					// Jump out of loop in this child. Parent will continue.
					exit;
				}
			}
			foreach ($pids as $pid) {
				pcntl_waitpid($pid, $status);
				unset($pids[$pid]);
			}
		}
		
		// 		$tagArray=$db->tags->find();
		// 		if (isset($tagArray) && !empty($tagArray)) {
		// 			$listTag = array();
		// 			foreach ($tagArray as $value) {
		// 				$listTag[] = str_replace("#","",$value['tag']);
		// 			}
		// 			$collection->drop();
		// 			$db->ranking->drop();
		// 			foreach ($listTag as $tag) {
		// 				$mediaArray=$this->getPosttop($tag);
		// 				$collection->batchInsert($mediaArray);
		// 				$this->calculator($tag);
		// 			}
		// 			$statisticArray= array();
		// 			$statistic = array();
		// 			foreach ($listTag as $tag) {
		// 				$statistic['hashtag'] = $tag;
		// 				$statistic['date']=date("d-m-Y");
		// 				foreach ($db->ranking->find(array('hashtag' =>$tag)) as $value) {
		// 					$total_media = $value['total_media'] ;
		// 				}
		// 				$statistic['total_media']=$total_media;
		// 				$statisticArray[$tag]=$statistic;
		// 			}
		// 			if(!empty($statisticArray)){
		// 				$db->statistic->batchInsert($statisticArray);
		// 			}
			
		// 		}
	}
}