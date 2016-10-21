<?php
class HashtagShell extends AppShell {
	public function getData($tag,$date) {
		$end_cursor=null;
		$mediaArray= array();
		$get_next=true;
		$myfile = fopen(APP . "Vendor/Data/" . date('dmY') . "." . $tag . ".media.hashtag.json", "w+") or die("Unable to open file!");
		do {
			if($end_cursor==null){
				$results_array = $this->cURLInstagram('https://www.instagram.com/explore/tags/'.$tag.'/?__a=1');
			}
			else{
				$results_array = $this->cURLInstagram('https://www.instagram.com/explore/tags/'.$tag.'/?__a=1&max_id='.$end_cursor);
			}
			if(isset($results_array)&&!empty($results_array)){
				$end_cursor=$results_array->tag->media->page_info->end_cursor;
				$media=$results_array->tag->media->nodes;
				foreach ($media as  $value) {
					if(date("d m Y", $value->date)==$date ){
						$value->tag_name = $tag;
						fwrite($myfile, json_encode($value)."\n");
						array_push($mediaArray, $value);
					}
					else{
						$get_next=false;
						break;
					}
				}
			}
			//get data fail
			else{
				break;
			}
			
		} while ($get_next);
		
		return $mediaArray;
	}
	public function caculator($tag){
		$m = new MongoClient();
		$db = $m->hashtag;
		$collection = $db->mediaHashtag;
		$condition = array(
				array('$match' => array('tag_name' => $tag)),
				array(
						'$group' => array(
								'_id' => 'null',
								'total_likes' => array('$sum' => '$likes.count'),
								'total_comments' => array('$sum' => '$comments.count'),
								'total_media' => array('$sum' => 1)
						)
				)
		);
		$data = $collection->aggregate($condition);
		$result = array();
		$result['total_likes']=$data['result'][0]['total_likes'];
		$result['total_comments']=$data['result'][0]['total_comments'];
		$result['total_media']=$data['result'][0]['total_media'];
		$result['hashtag']=$tag;
		$db->ranking->insert($result);
	}
	public function main() {
		$date  = date("d m Y");
		$m = new MongoClient();
		$db = $m->hashtag;
		$collection = $db->mediaHashtag;
		$tagArray=$db->tags->find();
		$listTag = array();
		foreach ($tagArray as $value){
			$listTag[] = str_replace("#","",$value['tag']);
		}
		foreach ($listTag as $tag){
			$mediaArray=$this->getData($tag,$date);
			$collection->batchInsert($mediaArray);
			$this->caculator($tag);
		}
	}
}