
<?php

header('Content-Type:text/html;charset= UTF-8');

$sql = "SELECT * FROM alert_rss";
$result = sql_select_fetchALL($sql);
foreach($result as $a){
    $alert_rss_id = $a['id'];
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
        } 
        //僅讀取item標籤中的內容
        if($id != "" ){
            $sql = "SELECT * FROM alert_rss_post WHERE post_id ='".$id."'";
		    $result = sql_select_fetchALL($sql);
            if($result->num_rows == 0 && $time != ""){
                $sql = "INSERT INTO 
                    alert_rss_post (rss_id, post_id, post_url, post_published, post_title) 
                    VALUES 
                    ('".$alert_rss_id."', '".$id."', '".$link."', '".$time."', '".$title."')";
                sql_select_fetchALL($sql);
                echo $sql;
            }
            $link = "";
            $time = "";
            $id = "";
        }
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
