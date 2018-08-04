
<?php

header('Content-Type:text/html;charset= UTF-8');

$sql = "SELECT * FROM alert_rss";
$result = sql_select_fetchALL($sql);
foreach($result as $a){
    $alert_id = $a['id'];
    $alert_rss = $a['alert_rss'];
    $buff = "";
    $rss_str="";
    
    $fp = fopen($alert_rss,"r") or die("can not open $alert_rss");  //打開rss地址，並讀取，讀取失敗則中止
    while ( !feof($fp) ) {
        $buff .= fgets($fp,4096);
    }
    fclose($fp);    //關閉文件打開

    $parser = xml_parser_create();  //建立一個 XML 解析器
    xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1); //xml_parser_set_option -- 為指定 XML 解析進行選項設置
    xml_parse_into_struct($parser,$buff,$values,$idx);  //xml_parse_into_struct -- 將 XML 數據解析到數組$values中
    xml_parser_free($parser);   //xml_parser_free -- 釋放指定的 XML 解析器

    //print_r($values);
    $title = "";
    $link = "";
    $time = "";
    $id = "";
    foreach ($values as $val) {
        $tag = $val["tag"];
        $type = $val["type"];

        $tag = strtolower($tag);     //標籤統一轉為小寫
        if ($tag == "link"){
            $link = $val["attributes"]["HREF"];
        } else if ($tag == "published") {
            $time = $val["value"];
        } else if ($tag == "id") {
            $id = $val["value"];
        } else if ($tag == "title") {
            $title = $val["value"];
            if(mb_strlen($title) >= 35){
                $title = mb_substr($title, 0, 35, "utf-8");
            } 
        } 
        //僅讀取item標籤中的內容
        if($id != "" ){
            $sql = "SELECT * FROM alert_rss_post WHERE post_id ='".$id."'";
		    $result = sql_select_fetchALL($sql);
            if($result->num_rows == 0 && $time != ""){
                $date = new DateTime($time, new DateTimeZone("Asia/Taipei"));
                $sql = "INSERT INTO 
                    alert_rss_post (alert_id, post_id, post_url, post_published, post_title) 
                    VALUES 
                    ('".$alert_id."', '".$id."', '".$link."', '".($date->getTimestamp())."', '".$title."')";
                sql_select_fetchALL($sql);
                send_message($alert_id, $link);
            }
            $link = "";
            $time = "";
            $id = "";
        }
    }
}

function send_message($alert_id, $send_message){
    $sql = "SELECT ars.line_id, c.name 
            FROM `alert_rss_subscribe` ars, candidate c 
            WHERE ars.alert_id = c.alert_id AND ars.alert_id = '".$alert_id."'";
    $result = sql_select_fetchALL($sql);
    if($result->num_rows > 0) {
        $candidate_name;
        $user_list = array();
        foreach($result as $a){
            $candidate_name = $a['name'];
            array_push($user_list,  $a['line_id']);
        }
        $response = array (
            "to" => $user_list,
            "messages" => array (
                array (
                    "type" => "text",
                    "text" => $candidate_name."有新的新聞囉!!"
                ),
                array (
                    "type" => "text",
                    "text" => $send_message
                )
            )
        );

        $header[] = "Content-Type: application/json";
        //輸入line 的 Channel access token
        $header[] = "Authorization: Bearer HJbK1gpGuMd1ZHEgUjVlo8U0PXoe8tuXUy3EN+FONnbQ8lHZAWgbpVcZPKs12a6o1C5tu9Ym1hdKUApJa8sNb1KeXMgjEax7hMascOKrFsNfMciHKCNIsptA6eSPLIFUgaDt8UFoQ0Ldgj7fRs2vHgdB04t89/1O/w1cDnyilFU=";
        $ch = curl_init("https://api.line.me/v2/bot/message/multicast");
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
