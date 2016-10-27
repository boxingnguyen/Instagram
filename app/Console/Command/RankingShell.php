<?php
class RankingShell extends AppShell {
	private $__collection;
	private $__collectionLogin;
	public function initialize() {
		parent::initialize();
		$m = new MongoClient;
		$db = $m->follow;
		$this->__collection = $db->selectCollection('username'.date('Y-m'));
		$this->__collectionLogin = $db->selectCollection('login'.date('Y-m'));
	}
	public function main() {
		$mLogin = new MongoClient;
		
		
		$dbLogin = $mLogin->instagram_account_info;
		$colLogin = $dbLogin->account_login;
		$colUsername = $dbLogin->account_username;
		//collection account_username
		$listAccount = $colUsername->find(array('access_token' => array('$exists' => true)));
		$this->__userFollow($listAccount, $this->__collection);
		print_r('Finish account_username');
		
		//collection account_login
// 		kiem tra nhung user ma ton tai acess_token thi xoa trong bang login di
		foreach ($listAccount as $val) {
			$data = $colLogin->find(array('id' => $val['id']));
			if ($data->count() > 0) {
				$colLogin->remove(array('id' => $val['id']));
			}
		}
// 		get daily collection login other account
		$listLogin = $colLogin->find();
		$this->__userFollow($listLogin, $this->__collectionLogin);
		print_r('Finish account_login');
	}
	private function __userFollow($listAccount, $collection) {
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
						
					//get total follow each account
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
				echo PHP_EOL."lan ......".$i.PHP_EOL;
				$i++;
			} while (isset($infoFollowsBy->pagination->next_cursor) && !empty($infoFollowsBy->pagination->next_cursor));
			echo 'Complete'.PHP_EOL.$valAccount['username'].PHP_EOL;
			$this->__saveFollow($valAccount['id'], $arr, $collection);
		}
	}
	
	private function __saveFollow($accountId, $arr, $collection) {
		$beforeTime = (new DateTime())->modify('-1 day')->format('Y-m-d');
		$userId = $collection->find(array($accountId => array('$exists' => 1), 'time' => $beforeTime));
		if ($userId->count() > 0) {
			$collection->remove(array($accountId => array('$exists' => 1), 'time' => $beforeTime));
		}
		$collection->insert(array($accountId => $arr,'time' => $beforeTime));
	}
}