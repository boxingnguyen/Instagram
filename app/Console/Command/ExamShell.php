<?php
/**
* 
*/
class ExamShell extends AppShell
{
	public function main(){
		$this->__getAccount();
	}
	public function __getAccount(){
             $m= new MongoClient();
             $db = $m->instagram_account_info;
             $collections= $db->account_username;
             $data= $collections->find();
             foreach ($data as $username) {
                 $result[]=$username;
             }
         // print_r($result[1]['_id']->id);	

         }
     public function __getMedia(){
     	
     }
}