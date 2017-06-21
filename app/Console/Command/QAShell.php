<?php
class QAShell extends AppShell {

    public function main() {
        $arr = array();
        $num = 0;
        $username = $this->__readJsonFile();
        foreach ($username as $key => $value){
            $url = 'https://www.instagram.com/'.$value.'/?__a=1';
            $infoFollower = $this->cURLInstagram($url)  ;
            $arr[$key] = $infoFollower;
            ++ $num;
            echo $key . ' ' . $value . PHP_EOL;
        }
        $this->__saveJson($arr[$key]); //xx
        echo "Number of follower get: " . $num . PHP_EOL;
    }

    private function __saveJson($info){
        $filename = fopen(APP."Vendor/Followers/"."QAjapanairlines_jal.json", "w+");
        foreach ($info as $value) {
            fwrite($filename, json_encode($value)."\n");
        }
        fclose($filename);
    }
    private function __getInfo(){
        $myfile = fopen(APP."Vendor/Followers/"."qA.json", "r");
        return $myfile;
    }

    private function __readJsonFile() {
        $filename = APP."Vendor/Followers/"."qA.json";
        if (file_exists($filename)) {
            $totalLines = file($filename);
            if (empty($totalLines)) {
                echo $name . "has no media or something wrong!" . PHP_EOL;
                return;
            }
            $part = (int)(count($totalLines) / 1000) + 1;
            $start = 0;
            if ($part == 1) {
                $count_get = count($totalLines) % 1000;
            } else {
                $count_get = 1000;
            }
            $data = array();
            for ($i = 0; $i < $part; $i++) {
                $my[$i] = array_slice($totalLines, $start, $count_get );
                if ($i < $part - 1) {
                    $start = $start + 1000;
                } else {
                    $start = $start + 1000;
                    $count_get = count($totalLines) % 1000;
                }
            }
            $user = array();
            for ($i = 0; $i < $part ; $i++) {
                foreach ($my[$i] as $value) {
                    $tmp = json_decode($value);
                    $username = $tmp->username;/////////lấy username de get info o day nhe
                    $user[] = $username;
                }

            }
        }
        return $user;
    }
}
