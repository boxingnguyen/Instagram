<?php
/**
* 
*/

class TestController extends AppController
{
    public function detail(){
        $this->layout=false;
        $this->autoRender=false;
        $code=$_GET['code'];
        $token= $this->_instagram->getOAuthToken($code);
        $aaa=$this->_instagram->setAccessToken($token);
        $data= $this->_instagram->getUser();
        echo "<pre";
        print_r($token);
 
    }
    public function getFollower(){
        //// Hang dt test
    }
    public function typeFor(){
	$this->layout=false;
	$this->autoRender=false;
    ini_set('memory_limit','1G');
    $start=microtime(true);
    for($i=0; $i<1000000;$i++)
           $arr[]=[
                'user'=>'hang'.rand(1,200),
                'media'=>rand(1,200),
                'likes'=>rand(1,200),
                'comments'=>rand(1,200),
            ];
     
     
    
    $check[]="aaa";
    for ($i=0; $i <sizeof($arr) ; $i++) { 

     	if(in_array($arr[$i]['user'],$check)){
            continue;
     	}
     	else {
     		$check[sizeof($check)]=$arr[$i]['user'];
     		$media=0;
     		$likes=0;
     		$comments=0;
     		for($j=$i; $j<sizeof($arr);$j++){
     			$media=$arr[$j]['media']+$media;
     			$likes=$arr[$j]['likes']+$likes;
     			$comments=$arr[$j]['comments']+$comments;
     		}
     		echo "user".$arr[$i]['user'];
     		echo 'media'.$media;
     		echo 'likes'.$likes;
     		echo 'comments'.$comments;
     		echo "<br>";
     		echo "<br>";
     	}
    }

     $end=microtime(true);
     $total = $end-$start;
     echo "Time:".$total;

}

public function typeDB(){
    $this->layout=false;
    $this->autoRender=false;
    $m = new MongoClient();
    $db = $m->check;
	$collection = $db->users;
    $collection->drop();
    $check[]="hang1";

     //tao dua lieu
    $start=microtime(true);
    for($i=0; $i<1000000;$i++){
        $arr[] = [
            'user'=>'hang'.rand(1,200),
            'media'=>rand(1,200),
            'likes'=>rand(1,200),
            'comments'=>rand(1,200),
        ];
        if (count($arr == 10000)) {
            $collection->batchInsert($arr);
            unset ($arr);
        }
    }
    // $collection->batchInsert($arr);
    $end=microtime(true);
    $total=$end-$start;
    echo 'Time:'.$total;
     die;      //tim trong db
        $conditions=[ '$group' => [
                                 '_id'=>'$user',
                                  'Media'=>['$sum'=>'$media'],
                                  'Likes'=>['$sum'=>'$likes'],
                                  'Comments'=>['$sum'=>'$comments']
                                   ]];
     	$data=$collection->aggregate($conditions);
        foreach ($data['result'] as $item) {
           echo 'Name'.$item['_id'];
           echo 'Media'.$item['Media'];
           echo 'Likes'.$item['Likes'];
           echo 'Comments'.$item['Comments'];
           echo "<br>";
            echo "<br>";

        }
        $end=microtime(true);
        $total=$end-$start;
        echo 'Time:'.$total;

       

}
}