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
        $f_comment_id = $json_obj->entry[0]->changes[0]->value->comment_id;
        //檢查fb_page是否有紀錄
        $sql = "SELECT * FROM fb_page WHERE page_id ='".$f_page_id ."'";
        $result = sql_select_fetchALL($sql);
        if($result->num_rows == 0){
            $sql = "INSERT INTO fb_page (page_id, page_name) VALUES ('".$f_page_id."', '".$f_page_name."')";
            sql_select_fetchALL($sql);
        }
        if($f_comment_id == ""){
            //新增/修改/刪除貼文
            if($f_verb == 'add'){
                $post_url = "https://www.facebook.com/permalink.php?story_fbid=".explode('_',$f_post_id)[1]."&id=".$f_page_id;
                $sql = "INSERT INTO fb_post (page_id, post_id, post_created_time, post_message, post_url) 
                        VALUES ('".$f_page_id."', '".$f_post_id."', '".$f_created_time."', '".$f_message."', '".$post_url."')";
                $result = sql_select_fetchALL($sql);
                send_message($f_page_id, $post_url);
            } else if($f_verb == 'edited') {
                $sql = "UPDATE fb_post SET post_message = '".$f_message."', lastest_update_time = UNIX_TIMESTAMP() WHERE post_id = '".$f_post_id."'";
                $result = sql_select_fetchALL($sql);
            } else if($f_verb == 'remove') {
                $sql = "UPDATE fb_post SET post_status = '2', lastest_update_time = UNIX_TIMESTAMP() WHERE post_id = '".$f_post_id."'";
                $result = sql_select_fetchALL($sql);
            }
        } else {
            //新增/修改/刪除留言
            if($f_verb == 'add'){
                $sql = "SELECT '0' as 'parent_comment_id' FROM `fb_post` WHERE post_id = '".$f_parent_id."'
                        UNION 
                        SELECT comment_id as 'parent_comment_id' FROM `fb_comment` WHERE comment_id = '".$f_parent_id."'";
                $result = sql_select_fetchALL($sql);
                if($result->num_rows > 0){
                    $parent_comment_id = "";
                    foreach($result as $a){
                        $parent_comment_id = $a['parent_comment_id'];
                    }
                    $sql = "INSERT INTO fb_comment (page_id, post_id, comment_id, parent_comment_id, comment_created_time, comment_message) 
                        VALUES ('".$f_page_id."', '".$f_post_id."', '".$f_comment_id."', '".$parent_comment_id."', '".$f_created_time."', '".$f_message."')";
                    $result = sql_select_fetchALL($sql);
                }
            } else if($f_verb == 'edited') {
                $sql = "UPDATE fb_comment SET comment_message = '".$f_message."', lastest_update_time = UNIX_TIMESTAMP() WHERE comment_id = '".$f_comment_id."'";
                $result = sql_select_fetchALL($sql);
            } else if($f_verb == 'remove') {
                $sql = "UPDATE fb_comment SET comment_status = '2', lastest_update_time = UNIX_TIMESTAMP() WHERE comment_id = '".$f_comment_id."'";
                $result = sql_select_fetchALL($sql);
            }
        }
    }
} catch(Exception $e) {
    fwrite($myfile, 'Caught exception: ',  $e->getMessage(), "\n"); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
}

function send_message($f_page_id, $send_message){
    $sql = "SELECT line_id FROM fb_page_subscribe WHERE page_id = '".$f_page_id."'";
    $result = sql_select_fetchALL($sql);
    if($result->num_rows > 0) {
        $user_list = array();
        foreach($result as $a){
            array_push($user_list,  $a['line_id']);
        }
        $response = array (
            "to" => $user_list,
            "messages" => array (
                array (
                    "type" => "text",
                    "text" => $send_message
                )
            )
        );

        $myfile = fopen("log2.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
        fwrite($myfile, "\xEF\xBB\xBF".json_encode($response)); //在字串前面加上\xEF\xBB\xBF轉成utf8格式

        $header[] = "Content-Type: application/json";
        //輸入line 的 Channel access token
        $header[] = "Authorization: Bearer HJbK1gpGuMd1ZHEgUjVlo8U0PXoe8tuXUy3EN+FONnbQ8lHZAWgbpVcZPKs12a6o1C5tu9Ym1hdKUApJa8sNb1KeXMgjEax7hMascOKrFsNfMciHKCNIsptA6eSPLIFUgaDt8UFoQ0Ldgj7fRs2vHgdB04t89/1O/w1cDnyilFU=";
        $ch = curl_init("https://api.line.me/v2/bot/message/push");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
        $result = curl_exec($ch);
        curl_close($ch);
    }
    
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
