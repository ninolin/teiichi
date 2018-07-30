
<?php

//RSS源地址列表數組
$rssfeed = array("https://www.google.com.tw/alerts/feeds/10688482321941126506/10267095873014584322");


//設置編碼為UTF-8
header('Content-Type:text/html;charset= UTF-8');
for($i=0;$i<sizeof($rssfeed);$i++){//分解開始

    $buff = "";
    $rss_str="";

    //打開rss地址，並讀取，讀取失敗則中止
    $fp = fopen($rssfeed[$i],"r") or die("can not open $rssfeed");
    while ( !feof($fp) ) {
        $buff .= fgets($fp,4096);
    }

    //關閉文件打開
    fclose($fp);

    //建立一個 XML 解析器
    $parser = xml_parser_create();

    //xml_parser_set_option -- 為指定 XML 解析進行選項設置
    xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
    //xml_parse_into_struct -- 將 XML 數據解析到數組$values中
    xml_parse_into_struct($parser,$buff,$values,$idx);
    //xml_parser_free -- 釋放指定的 XML 解析器
    xml_parser_free($parser);

    print_r($values);
    foreach ($values as $val) {
        $tag = $val["tag"];
        $type = $val["type"];

        $is_link = 0;
        $is_time = 0;
        //標籤統一轉為小寫
        $tag = strtolower($tag);
        if ($tag == "link"){
            $is_link = 1;
        } else if ($tag == "published") {
            $is_time = 0;
        }

        //僅讀取item標籤中的內容
        if($is_link==1){
            echo $val["attributes"]["HREF"];
            echo "</br>";
            echo "</br>";
        }
        if($is_time==1){
            echo $val["value"];
            echo "</br>";
            echo "</br>";
        }
    }

    //輸出結果
    echo $rss_str."";

}
?>
