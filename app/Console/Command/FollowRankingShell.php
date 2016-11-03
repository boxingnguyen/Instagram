<?php
class FollowRankingShell extends AppShell {
	private $__collection;
// 	private $__collectionLogin;
	public function initialize() {
		parent::initialize();
		$m = new MongoClient;
		$db = $m->instagram;
		$this->__collection = $db->follow;
	}
	public function main() {
		$mLogin = new MongoClient;		
		$dbLogin = $mLogin->instagram_account_info;
		$colLogin = $dbLogin->account_login;
		$colUsername = $dbLogin->account_username;
		$listAccount = $colUsername->find(array('access_token' => array('$exists' => true)));
		if($listAccount->count() > 0) {
			$this->__userFollow($listAccount);
			//collection account_login
			foreach ($listAccount as $val) {
				$data = $colLogin->find(array('id' => $val['id']));
				if ($data->count() > 0) {
					$colLogin->remove(array('id' => $val['id']));
				}
			}
			echo PHP_EOL.'Finish account_username'.PHP_EOL;
		}
		
// 		get daily collection login other account
		$colLogin->remove(array('username' => null));
		$listLogin = $colLogin->find();
		if($listLogin->count() > 0) {
			$this->__userFollow($listLogin);
			echo PHP_EOL.'Finish account_login'.PHP_EOL;
		}
	}

	private function __userFollow($listAccount) {
		foreach ($listAccount as $valAccount) {
			$arr = array();
			$cursor = null;$i = 1;
			$this->_insta->setToken($valAccount['access_token']);
			do {

				if($cursor == null) {
					$infoFollowsBy = $this->_insta->getUserFollower();
				} else {
					$infoFollowsBy = $this->_insta->getUserFollower($cursor);
				}
				if(isset($infoFollowsBy) && !empty($infoFollowsBy->data)) {
					$dataFollow = $infoFollowsBy->data;
					foreach ($dataFollow as $valFollow) {
						$follow = $this->_insta->getUserFollow($valFollow->id);
						if(isset($follow) && !empty($follow->meta) &&  $follow->meta->code == 400) {
							$username = $valFollow->username;
							$url = 'https://www.instagram.com/'.$username.'/?__a=1';
							$getUrl = $this->cURLInstagram($url);
							$countFollowBy = $getUrl->user->followed_by->count;
							$countFollows = $getUrl->user->follows->count;
						} else {
							$countFollowBy = $follow->data->counts->followed_by;
							$countFollows = $follow->data->counts->follows;
						}

						$arr[] = array(
								'id' => $valFollow->id, 'username' => $valFollow->username,
								'full_name' => $valFollow->full_name, 'totalFollow' => $countFollowBy,
								'follows' => $countFollows
						);
					}
						
				} else {
					echo "<pre>";
					print_r($infoFollowsBy);
					exit;
				}
				if(isset($infoFollowsBy->pagination->next_cursor) && !empty($infoFollowsBy->pagination->next_cursor)) {
					$cursor = $infoFollowsBy->pagination->next_cursor;
				}
				echo PHP_EOL." Number ......".$i.PHP_EOL;
				$i++;
			} while (isset($infoFollowsBy->pagination->next_cursor) && !empty($infoFollowsBy->pagination->next_cursor));
			echo PHP_EOL.'Complete'.PHP_EOL.$valAccount['username'].PHP_EOL;
			
			usort($arr, function($a, $b) { return $a['totalFollow'] < $b['totalFollow'] ? 1 : -1 ; } );
			$this->__saveJson($valAccount['id'],$arr,$valAccount['username']);
			$this->__saveFollow($valAccount['id'], $arr);
		}
	}
	
	private function __saveFollow($accountId, $arr) {
		$userId = $this->__collection->find(array($accountId => array('$exists' => 1)));
		if ($userId->count() > 0) {
			$this->__collection->remove(array($accountId => array('$exists' => 1)));
		}

		$this->__collection->insert(array($accountId => $arr));
	}
	private function __saveJson($accountId, $list_follow, $name){
		$date = date("dmY");
		$filename = fopen(APP."Vendor/Followers/".$date.".".$name.".follow.json", "w+");
		foreach ($list_follow as $value) {
			fwrite($filename, json_encode($value)."\n");
		}
		
		fclose($filename);
	}
}
