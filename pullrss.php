<?php
 
    $rss="https://www.google.com.tw/alerts/feeds/10688482321941126506/10267095873014584322";//rss 網址
 
    $rssfeed =array();
    array_push($rssfeed,$rss);
    //設置編碼為UTF-8  
    header('Content-Type:text/html;charset= UTF-8'); 
 
    for ($i = 0; $i < sizeof($rssfeed); $i++) {//分解開始  
        $buff = ""; 
        $rss_str = ""; 
        //打開rss地址，並讀取，讀取失敗則中止  
        $fp = fopen($rssfeed[$i], "r") or die("can not open $rssfeed"); 
        while (!feof($fp)) { 
            $buff .= fgets($fp, 4096); 
        } 
        //關閉文件打開  
        fclose($fp); 
 
        //建立一個 XML 解析器  
        $parser = xml_parser_create(); 
        //xml_parser_set_option -- 為指定 XML 解析進行選項設置  
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
        //xml_parse_into_struct -- 將 XML 數據解析到數組$values中  
        xml_parse_into_struct($parser, $buff, $values, $idx); 
        //xml_parser_free -- 釋放指定的 XML 解析器  
        xml_parser_free($parser); 
 
        foreach ($values as $val) { 
            $tag = $val["tag"]; 
            $type = $val["type"]; 
            $value = $val["value"]; 
            //標籤統一轉為小寫  
            $tag = strtolower($tag); 
 
            if ($tag == "item" && $type == "open") { 
                $is_item = 1; 
            } else if ($tag == "item" && $type == "close") { 
            //構造輸出字符串  
                $is_item = 0;
                $finish.=$title.'&nbsp;';//將結果串接起來 以 &nbsp; 做分割
            } 
            //僅讀取item標籤中的內容  
            if ($is_item == 1) { 
                if ($tag == "title") { 
                    $title = $value; 
                } 
                if ($tag == "link") { 
                    $link = $value; 
                } 
            } 
        } 
    }
     
    $output=array();
    $output = explode("&nbsp;", $finish);//依 &nbsp; 來分割字串 將分割結果存入陣列
    array_pop($output);//移除最後一個陣列元素
    print_r($output);//顯示陣列
?>