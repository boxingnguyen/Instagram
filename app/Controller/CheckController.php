<?php
	class CheckController extends AppController {
		public function index(){
			$this->layout= false;
			$this->autoRender= false;
			if(isset($_POST['username'])){
				$username = $_POST['username'];
				$m = new MongoClient();
				$db = $m->instagram_account_info;
				$collection = $db->account_username;
				$exist = $collection->find(array('username'=>$username))->count();
					if(!$exist == 0){
						return json_encode("The account had added before!");
					}
					else{
						$data = $this->cURLInstagram('https://www.instagram.com/' . $username . '/?__a=1');
						if(isset($data)){
							$id = $data->user->id;
							// save to mongo db
							$collection->insert(array('username'=>$username,'id'=>$id));
							return json_encode("The account is added successfully!");
						}
						else{
							// alert username doesn't exist
							return json_encode("The account doesn't exist, please fill again!");
						}
					}
			}else{
				return false;
			}
		}
	}
?>