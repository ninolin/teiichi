<?php
$challenge = $_REQUEST["hub_challenge"]; //取得fb webhook的hub.challenge get參數
echo $challenge; //回傳hub.challenge get參數

$json_str = file_get_contents('php://input'); //接收request的body
$json_obj = json_decode($json_str); //轉成json格式

$myfile = fopen("log.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
fwrite($myfile, "\xEF\xBB\xBF".$json_str); //在字串前面加上\xEF\xBB\xBF轉成utf8格式

try {
    $f_object = $json_obj->object;
    if($f_object === "page"){
        $f_receive_time = $json_obj->entry[0]->time;
        $f_page_id = $json_obj->entry[0]->changes[0]->value->from->id;
        $f_page_name = $json_obj->entry[0]->changes[0]->value->from->name;
        $f_post_id = $json_obj->entry[0]->changes[0]->value->post_id;
        $f_verb = $json_obj->entry[0]->changes[0]->value->verb;
        $f_created_time = $json_obj->entry[0]->changes[0]->value->created_time;
        $f_message = $json_obj->entry[0]->changes[0]->value->message;
        $f_parent_id = $json_obj->entry[0]->changes[0]->value->parent_id;
        //檢查fb_page是否有紀錄
        $sql = "SELECT * FROM fb_page WHERE page_id ='".$f_page_id ."'";
        $result = sql_select_fetchALL($sql);
        if($result->num_rows == 0){
            $sql = "INSERT INTO fb_page (page_id, page_name) VALUES ('".$f_page_id."', '".$f_page_name."')";
            sql_select_fetchALL($sql);
        }
        if($f_parent_id == ""){
            //新增/修改/刪除貼文
            if($f_verb == 'add'){
                $post_url = "https://www.facebook.com/permalink.php?story_fbid=".$f_post_id."&id=".$f_page_id;
                $sql = "INSERT INTO fb_post (page_id, post_id, post_verb, post_created_time, post_message, post_url) 
                        VALUES ('".$f_page_id."', '".$f_post_id."', '".$f_verb."', '".$f_created_time."', '".$f_message."', '".$post_url."')";
                $result = sql_select_fetchALL($sql);
            } else if($f_verb == 'edited') {
                $sql = "UPDATE fb_post SET post_message = '".$f_message."'";
                $result = sql_select_fetchALL($sql);
            } else if($f_verb == 'edited') {
                $sql = "UPDATE fb_post SET post_status = '2'";
                $result = sql_select_fetchALL($sql);
            }
        } else {
            
        }
    }
} catch(Exception $e) {
    fwrite($myfile, 'Caught exception: ',  $e->getMessage(), "\n"); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
}


function sql_select_fetchALL($sql){   
    $db_server = "localhost";
    $db_name = "teiichi";
    $db_user = "root";
    $db_passwd = "fdd396906f5054060122311cf8b0eb2da0cfe7a437501152";
    
    $con=mysqli_connect($db_server, $db_user, $db_passwd) or die("資料庫登入錯誤");
    if(mysqli_connect_errno($con)){
        echo "ERROR1";
    }

    mysqli_query($con,"SET NAMES utf8");
    mysqli_select_db($con,$db_name) or die("資料庫連結錯誤");
    
    $row = mysqli_query($con,$sql);
    mysqli_close($con);
    return $row;
}
?>
