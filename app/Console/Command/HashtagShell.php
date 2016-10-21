<?php
class HashtagShell extends AppShell {
	public function getData($tag,$date){
		$end_cursor=null;
		do {
			if($end_cursor==null){
				$data = $this->cURLInstagram('https://www.instagram.com/explore/tags/'.$tag.'/?__a=1');
			
			}
			else{
				$data = $this->cURLInstagram('https://www.instagram.com/explore/tags/'.$tag.'/?__a=1&max_id='.$end_cursor);
			
			}
			$end_cursor=$results_array->tag->media->page_info->end_cursor;
			
		
		} while (1);
		
		
	}
}