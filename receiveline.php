<?php
	$json_str = file_get_contents('php://input'); //接收request的body
	$json_obj = json_decode($json_str); //轉成json格式
	
	$myfile = fopen("log.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
	fwrite($myfile, "\xEF\xBB\xBF".$json_str); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
	
	$sender_userid = $json_obj->events[0]->source->userId; //取得訊息發送者的id
	$sender_txt = $json_obj->events[0]->message->text; //取得訊息內容
	$sender_replyToken = $json_obj->events[0]->replyToken; //取得訊息的replyToken
	$sender_type = $json_obj->events[0]->type; //取得訊息的type
	
	if($sender_type == "postback"){ //訊息的type為postback(選單)
		$postback_data = $json_obj->events[0]->postback->data; //取得postback的data
		if(explode("&",$postback_data)[0] == "introCourse"){ 
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		introCourse(explode("&",$postback_data)[1])
			    	)
			); 
		} else if(explode("&",$postback_data)[0] == "leaveCourse"){ 
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		leaveCourse(explode("&",$postback_data)[1], $sender_userid)
			    	)
			);
		} else if(explode("&",$postback_data)[0] == "leaveCourseDate"){ 
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		leaveCourseDate(explode("&",$postback_data)[1], $sender_userid)
			    	)
			);
		} else if(explode("&",$postback_data)[0] == "outCourse"){ 
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		outCourse(explode("&",$postback_data)[1], $sender_userid)
			    	)
			);
		}
	} else if($sender_type == "message"){
		if($sender_txt == "每日簽到"){
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		sign($sender_userid)
			    	)
			);
		} else if($sender_txt == "查看任務"){
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		apply($sender_userid)
			    	)
			);
		} else if($sender_txt == "操作秘笈"){
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		apply($sender_userid)
			    	)
			);
		} else if($sender_txt == "進行簽到"){
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		action_sign($sender_userid)
			    	)
			);
		} else if($sender_txt == "查看成就"){
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		see_achievement($sender_userid)
			    	)
			);
		}
		
	}
	fwrite($myfile, "\xEF\xBB\xBF".json_encode($response)); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
	$header[] = "Content-Type: application/json";
	//輸入line 的 Channel access token
	$header[] = "Authorization: Bearer HJbK1gpGuMd1ZHEgUjVlo8U0PXoe8tuXUy3EN+FONnbQ8lHZAWgbpVcZPKs12a6o1C5tu9Ym1hdKUApJa8sNb1KeXMgjEax7hMascOKrFsNfMciHKCNIsptA6eSPLIFUgaDt8UFoQ0Ldgj7fRs2vHgdB04t89/1O/w1cDnyilFU=";
	$ch = curl_init("https://api.line.me/v2/bot/message/reply");
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
	$result = curl_exec($ch);
	curl_close($ch);
	
	function apply($sender_userid){
		$json_str = '{
  			"type": "template",
  			"altText": "this is a carousel template",
  			"template": {
				"type": "carousel",
				"columns": []
  			}
		}';
		$json = json_decode($json_str);
		$sql = "SELECT * 
				FROM `course` 
				WHERE course_startdate > CURDATE() and id not in 
					(
						SELECT course_id 
						FROM course_student 
						WHERE line_id = '".$sender_userid."'
					)
				";
		$result = sql_select_fetchALL($sql);
		$rcount = $result->num_rows;
		$course_name = "";
		$i = 0;
		foreach($result as $a){
			$applyCourseUri = "https://sporzfy.com/chtChatBot/ninoiii0507/applyCourse.html?course_id=".$a['id']."&line_id=".$sender_userid;
			fwrite($myfile, "\xEF\xBB\xBF".$applyCourseUri); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
			if($i < 4){
				$course_obj = array (
					"title" => $a['course_name'],
					"text" => $a['course_name'],
					"actions" => array (
						array (
							"type" => "uri",
							"label" => "課程報名",
							"uri" => $applyCourseUri
						),
						array (
							"type" => "postback",
							"label" => "課程說明",
							"data" => "introCourse&".$a['id']
						),
						array (
							"type" => "uri",
							"label" => "課程連結",
							"uri" => $a['course_url']
						)
					)
				);
			} else {
			}
			
			$json -> template -> columns[] = $course_obj;
		}
		return $json;
	}
	
	function sign($sender_userid){
		$sql = "SELECT * FROM line_user WHERE line_id ='".$sender_userid."'";
		$result = sql_select_fetchALL($sql);
		if($result->num_rows == 0){
			$json_str = '{
				"type": "template",
				"altText": "this is a buttons template",
				"template": {
				  "type": "buttons",
				  "actions": [
					{
					  "type": "uri",
					  "label": "註冊",
					  "uri": "https://sporzfy.com/chtChatBot/ninoiii0507/applyCourse.html"
					}
				  ],
				  "title": "歡迎使用本服務",
				  "text": "請註冊並審核後即可進行服務"
				}
			}';
			$json = json_decode($json_str);
			return $json;
		} else {
			$status = "";
			foreach($result as $a){
				$status = $a['status']; 
			}
			if($status == 3) {
				$json_str = '{
					"type": "text",
					"text": "註冊審核中，請耐心等待"
				}';
				$json = json_decode($json_str);
				return $json;
			} else if($status == 1) {
				$json_str = '{
					"type": "template",
					"altText": "this is a buttons template",
					"template": {
					  "type": "buttons",
					  "actions": [
						{
						  "type": "message",
						  "label": "進行簽到",
						  "text": "進行簽到"
						},
						{
						  "type": "message",
						  "label": "查看成就",
						  "text": "查看成就"
						},
						{
						  "type": "message",
						  "label": "編輯資料",
						  "text": "編輯資料"
						}
					  ],
					  "title": "每日簽到",
					  "text": "'.date("Y-m-d").'"
					}
				}';
				$json = json_decode($json_str);
				return $json;
			} else {
				$json_str = '{
					"type": "template",
					"altText": "this is a buttons template",
					"template": {
					  "type": "buttons",
					  "actions": [
						{
						  "type": "uri",
						  "label": "註冊",
						  "uri": "https://sporzfy.com/chtChatBot/ninoiii0507/applyCourse.html"
						}
					  ],
					  "title": "歡迎使用本服務",
					  "text": "前次註冊審核失敗，若仍要使用本服務請重新註冊"
					}
				}';
				$json = json_decode($json_str);
				return $json;
			}
		}
	}
	
	function action_sign($sender_userid){
		$sql = "SELECT * FROM line_user_sign WHERE line_id ='".$sender_userid."' AND sign_date = '". date("Y-m-d")."'";
		$myfile = fopen("log2.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
		fwrite($myfile, "\xEF\xBB\xBF".$sql); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
		$result = sql_select_fetchALL($sql);
		fwrite($myfile, "\xEF\xBB\xBF rr".$result->num_rows);
		if($result->num_rows == 0){
			$sql = "INSERT INTO line_user_sign (line_id, sign_date) VALUES ('".$sender_userid."', '".date("Y-m-d")."')";
			fwrite($myfile, "\xEF\xBB\xBF".$sql); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
			$result = sql_select_fetchALL($sql);
			$json_str = '{
				"type": "text",
				"text": "簽到成功"
			}';
			$json = json_decode($json_str);
			return $json;
		} else {
			$json_str = '{
				"type": "text",
				"text": "本日已完成簽到"
			}';
			$json = json_decode($json_str);
			return $json;
		}
	}

	function see_achievement($sender_userid){
		$sql = "SELECT * FROM line_user_sign WHERE line_id ='".$sender_userid."'";
		$result = sql_select_fetchALL($sql);
		$signed_count = $result->num_rows;
		$sql = "SELECT user_created_date FROM line_user";
		$result = sql_select_fetchALL($sql);
		$myfile = fopen("log2.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
			fwrite($myfile, "\xEF\xBB\xBF abc".$sql); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
		if($result->num_rows == 0){
			$user_created_date = "2018-07-25";
			foreach($result as $a){
				$user_created_date = $a['user_created_date'];
			}
			$myfile = fopen("log2.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
			fwrite($myfile, "\xEF\xBB\xBF abc".$$user_created_date); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
			$date1=date_create($user_created_date);
			$date2=date_create("2018-11-24");
			$diff=date_diff($date1,$date2);
			$havesign_days = 500;
			$sign_persent = 0;
			if($diff->format("%R") == "+"){
				$havesign_days = $diff->format("%a");
				$sign_persent = round($signed_count/$havesign_days*100);
				$date1=date_create(date("Y-m-d"));
				$diff=date_diff($date1,$date2);
				if($diff->format("%R") == "+"){
					$json_str = '{
						"type": "text",
						"text": "距離三合一選舉還有'. $diff->format("%a").'\n已參戰: '.$havesign_days.'天 \n簽到率:'.$sign_persent.'%"
					}';
					$json = json_decode($json_str);
					return $json;
				}
			}
		}
	}
	function introCourse($course_id){
		$sql = "SELECT * FROM `course` WHERE id = '".$course_id."'";
		
		$result = sql_select_fetchALL($sql);
		$text = "";
		foreach($result as $a){
			$text .= $a['course_name'] ."\n"; 
			$text .= "日期:".$a['course_startdate']."~".$a['course_enddate']."\n";
			$text .= "時間:".$a['course_week']." ".$a['course_time']."\n";
			$text .= "地點:".$a['course_location']."\n";
			$text .= "老師:".$a['course_teacher']."\n";
			$text .= "價格:".$a['course_price']."\n";
		}
		$json_str = '{
			"type": "text",
			"text": ""
		}';
		$json = json_decode($json_str);
		$json -> text = $text;
		return $json;
	}
	
	function leaveCourse($course_id, $sender_userid){
		$json_str = '{
  			"type": "template",
  			"altText": "this is a carousel template",
  			"template": {
				"type": "carousel",
				"columns": []
  			}
		}';
		$json = json_decode($json_str);
		//列出該課程的日期
		$sql = "SELECT cd.*, c.course_name 
				FROM course_date cd, course c 
				WHERE 
					c.id = '".$course_id."' AND 
					c.id = course_id AND 
					course_date > CURDATE() AND 
					cd.id NOT IN (SELECT course_date_id FROM `course_leave` WHERE line_id = '".$sender_userid."' )";
		$result = sql_select_fetchALL($sql);
		if($result->num_rows == 0){
			$json_str = '{
				"type": "text",
				"text": "目前無日期可以請假"
			}';
			$json = json_decode($json_str);
			return $json;
		} else {
			$course_name = "";
			$i = 1;
			$array = [];
			foreach($result as $a){
				$value = array (
					"type" => "postback",
					"label" => $a['course_date'],
					"data" => "leaveCourseDate&".$a['id']
				);
				array_push($array, $value);
				if($i % 3 == 0 || $i == ($result->num_rows)) {
					if($i == ($result->num_rows) && ($result->num_rows) % 3 != 0){
						$value = array (
							"type" => "postback",
							"label" => "-",
							"data" => "-"
						);
						if(($result->num_rows) % 3 == 1){
							array_push($array, $value);
							array_push($array, $value);
						} else if(($result->num_rows) % 3 == 2){
							array_push($array, $value);
						}
					} 
					$course_obj = array (
						"title" => $a['course_name'],
						"text" => "請選擇要請假的日期",
						"actions" => $array
					);
					$json -> template -> columns[] = $course_obj;
					$array = [];
				}
				$i++;
			}
			return $json;
		}
		
	}
	
	function leaveCourseDate($date_id, $sender_userid){
		$sql = "Insert into course_leave (course_date_id, line_id) 
				VALUES ('".$date_id."', '".$sender_userid."')";
		$myfile = fopen("log2.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
		fwrite($myfile, "\xEF\xBB\xBF".$sql); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
		$json_str = '{
			"type": "text",
			"text": "請假成功"
		}';
		$json = json_decode($json_str);
		return $json;
	}
	function outCourse($course_id, $sender_userid){
		$sql = "Insert into course_out (course_id, line_id) 
				VALUES ('".$course_id."', '".$sender_userid."')";
		sql_select_fetchALL($sql);
		$myfile = fopen("log2.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
		fwrite($myfile, "\xEF\xBB\xBF".$sql); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
		$json_str = '{
			"type": "text",
			"text": "退出成功"
		}';
		$json = json_decode($json_str);
		return $json;
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