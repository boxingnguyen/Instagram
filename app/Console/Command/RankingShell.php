<?php
class RankingShell extends AppShell {
	private $__collection;
	private $__collectionLogin;
	public function initialize() {
		parent::initialize();
		$m = new MongoClient;
		$db = $m->follow;
		
		$currentDate = (new DateTime())->modify('-1 day')->format('Y-m-d');
		$time = date('Y-m', strtotime($currentDate));
		$this->__collection = $db->selectCollection('username'.$time);
		$this->__collectionLogin = $db->selectCollection('login'.$time);
	}
	public function main() {
		$mLogin = new MongoClient;		
		$dbLogin = $mLogin->instagram_account_info;
		$colLogin = $dbLogin->account_login;
		$colUsername = $dbLogin->account_username;
		$beforeTime = (new DateTime())->modify('-1 day')->format('Y-m-d');
		//collection account_username
		$listAccount = $colUsername->find(array('access_token' => array('$exists' => true)));
		if($listAccount->count() > 0) {
			$this->__userFollow($listAccount, $this->__collection);
			//collection account_login
			foreach ($listAccount as $val) {
				//check account exitst account_username ? "delete account_login" : "get info of account"
				$data = $colLogin->find(array('id' => $val['id']));
				if ($data->count() > 0) {
					$colLogin->remove(array('id' => $val['id']));
				}
				//check account exitst loginDate ? "delete record" : "....."
				$checkColLoginData = $this->__collectionLogin->find(array($val['id'] => array('$exists' => 1), 'time' => $beforeTime));
				if ($checkColLoginData->count() > 0) {
					$this->__collectionLogin->remove(array($val['id'] => array('$exists' => 1), 'time' => $beforeTime));
				}
			}
			echo PHP_EOL.'Finish account_username'.PHP_EOL;
		}
		
// 		get daily collection login other account
		$colLogin->remove(array('username' => null));
		$listLogin = $colLogin->find();
		if($listLogin->count() > 0) {
			$this->__userFollow($listLogin, $this->__collectionLogin);
			echo PHP_EOL.'Finish account_login'.PHP_EOL;
		}
	}
	//$listAccount: danh sach account
	private function __userFollow($listAccount, $collection) {
		foreach ($listAccount as $valAccount) {
			$arr = array();
			$cursor = null;$i = 1;
			$this->_insta->setToken($valAccount['access_token']);
			do {
				//danh sach nhung nguoi follow account
				if($cursor == null) {
					$infoFollowsBy = $this->_insta->getUserFollower();
				} else {
					$infoFollowsBy = $this->_insta->getUserFollower($cursor);
				}
				if(isset($infoFollowsBy) && !empty($infoFollowsBy->data)) {
						
					//get total follow each account
					$dataFollow = $infoFollowsBy->data;
					foreach ($dataFollow as $valFollow) {
						//tong follow cua 1 tai khoan
						$follow = $this->_insta->getUserFollow($valFollow->id);//total follow account

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
						// usort($arr, function($a, $b) { return $a['totalFollow'] < $b['totalFollow'] ? 1 : -1 ; } );
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

			$a();

			$this->__saveFollow('19752', $arr, $collection);
		}
	}
	
	private function __saveFollow($accountId, $arr, $collection) {
		$beforeTime = (new DateTime())->modify('-1 day')->format('Y-m-d');
		$userId = $collection->find(array($accountId => array('$exists' => 1), 'time' => $beforeTime));
		if ($userId->count() > 0) {
			$collection->remove(array($accountId => array('$exists' => 1), 'time' => $beforeTime));
		}
		$collection->insert(array('19752' => $arr,'time' => $beforeTime));
	}

	private function __saveData(){
		
	}
}
