<?php
class HashtagShell extends AppShell {
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
	public function caculator($tag){
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
		$a = $this->getPosttop ($tag);
		$data = $collection->aggregate($condition);
		$result = array();
		$result['total_likes']=$data['result'][0]['total_likes'];
		$result['total_comments']=$data['result'][0]['total_comments'];
		$result['total_media']=$a['total_media'];
		$result['hashtag']=$tag;
		$db->ranking->insert($result);
	}
	public function main() {
		$date  = date("d m Y");
		$m = new MongoClient();
		$db = $m->hashtag;
		$collection = $db->media;
		$tagArray=$db->tags->find();
		if(isset($tagArray)&&!empty($tagArray)){
			$listTag = array();
			foreach ($tagArray as $value){
				$listTag[] = str_replace("#","",$value['tag']);
			}
			$collection->drop();
			$db->ranking->drop();
			foreach ($listTag as $tag){
				$mediaArray=$this->getPosttop($tag);
				$collection->batchInsert($mediaArray);
				$this->caculator($tag);
			}
			$statisticArray= array();
			$statistic = array();
			foreach ($listTag as $tag){
				$statistic['hashtag'] = $tag;
				$statistic['date']=date("d-m-Y");
				foreach ($db->ranking->find(array('hashtag' =>$tag)) as $value){
					$total_media = $value['total_media'] ;
				}
				$statistic['total_media']=$total_media;
				$statisticArray[$tag]=$statistic;
			}
			$db->statistic->batchInsert($statisticArray);
		}
	}
}