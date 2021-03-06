<?php
	date_default_timezone_set("Asia/Taipei");

	$json_str = file_get_contents('php://input'); //接收request的body
	$json_obj = json_decode($json_str); //轉成json格式
	
	$myfile = fopen("reciveline_log.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
	fwrite($myfile, "\xEF\xBB\xBF".$json_str); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
	
	$sender_userid = $json_obj->events[0]->source->userId; //取得訊息發送者的id
	$sender_txt = $json_obj->events[0]->message->text; //取得訊息內容
	$sender_replyToken = $json_obj->events[0]->replyToken; //取得訊息的replyToken
	$sender_type = $json_obj->events[0]->type; //取得訊息的type
	
	if($sender_type == "postback"){ //訊息的type為postback(選單)
		$postback_data = $json_obj->events[0]->postback->data; //取得postback的data
		if(explode("&",$postback_data)[0] == "nextmession"){ 
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		mission($sender_userid, explode("&",$postback_data)[1])
			    	)
			); 
		} else if(explode("&",$postback_data)[0] == "sub" || explode("&",$postback_data)[0] == "unsub"){ 
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		subscribe(explode("&",$postback_data)[0], explode("&",$postback_data)[1], explode("&",$postback_data)[2], $sender_userid)
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
			      		mission($sender_userid, 1)
			    	)
			);
		} else if($sender_txt == "操作秘笈"){
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		operation($sender_userid)
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
		} else if($sender_txt == "訂閱侯選人"){
			$response = array (
				"replyToken" => $sender_replyToken,
				"messages" => array (
			      		subscribe_candidate($sender_userid)
			    	)
			);
		}
		
	}
	
	$myfile = fopen("sendline_log.txt", "w+") or die("Unable to open file!"); //設定一個log.txt來印訊息
	fwrite($myfile, "\xEF\xBB\xBF".json_encode($response)); //在字串前面加上\xEF\xBB\xBF轉成utf8格式
	$header[] = "Content-Type: application/json";
	//輸入line 的 Channel access token
	$header[] = "Authorization: Bearer GslnfrzfEI86+MXtSdGs7CWhK5MadTwFsT8lqTSeIpPoHgZQNEiEnJ4tYrGu8aqDIRN3JXE3iqMRPO1KAZHsngbph6uxehe4+ESda044izNQ8DeIA+rabzzg9wj5BmvUHfPJ/roQs9K7f/GkgJCufAdB04t89/1O/w1cDnyilFU=";
	$ch = curl_init("https://api.line.me/v2/bot/message/reply");
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
	$result = curl_exec($ch);
	curl_close($ch);
	
	//查看任務
	function mission($sender_userid, $page){
		$sql = "SELECT * FROM line_user WHERE line_id ='".$sender_userid."' AND status = 1";
		$result = sql_select_fetchALL($sql);
		if($result->num_rows == 0){
			$json_str = '{
				"type": "text",
				"text": "請先註冊並審核後才可以使用此服務"
			}';
			$json = json_decode($json_str);
			return $json;
		}

		$json_str = '{
  			"type": "template",
  			"altText": "this is a carousel template",
  			"template": {
				"type": "carousel",
				"columns": []
  			}
		}';
		$json = json_decode($json_str);
		$sql = "SELECT * FROM (
					SELECT post_title as title, post_url as url, post_published as lastest_time, post_remark, '新聞' as type 
					FROM `alert_rss_post` 
					WHERE (post_published + 259200) > UNIX_TIMESTAMP() 
						AND alert_id IN (
							SELECT alert_id 
							FROM `alert_rss_subscribe` 
							WHERE line_id = '".$sender_userid."'
						) 
						AND post_hide = 2 
					UNION 
					SELECT post_message as title, post_url as url, lastest_update_time as lastest_time, post_remark, '臉書' as type 
					FROM `fb_post` 
					WHERE (lastest_update_time + 259200) > UNIX_TIMESTAMP()
						AND page_id IN (
							SELECT page_id 
							FROM `fb_page_subscribe` 
							WHERE line_id = '".$sender_userid."'
						) 
						AND post_hide = 2 AND post_status = 1 
					UNION 
					SELECT 
						post_title as title, post_url as url, 9999999999 as lastest_time, post_remark, '' as type  
						FROM cus_post
						WHERE post_hide = 2
					) as post WHERE title != ''
				ORDER BY lastest_time DESC
				";
		$result = sql_select_fetchALL($sql);
		$rcount = $result->num_rows;
		$course_name = "";
		$page_end = 5 * $page;
		$page_start = $page_end - 4;
		$i = 1;
		foreach($result as $a){
			
			if($i >= $page_start && $i <= $page_end){
				
				$text = "-";
				if(!is_null($a['post_remark'])){
					$text = $a['post_remark'];
				}
				if(mb_strlen($a['title']) >= 35){
					$a['title'] = mb_substr($a['title'], 0, 35, "utf-8");
				}
				if($a['url'] == ""){
					$course_obj = array (
						"title" => $a['title'],
						"text" => $text,
						"actions" => array (
							array (
								"type" => "postback",
								"label" => "-",
								"data" => "-"
							)
						)
					);
				} else {
					$course_obj = array (
						"title" => $a['title'],
						"text" => $text,
						"actions" => array (
							array (
								"type" => "uri",
								"label" => "連結".$a['type'],
								"uri" => $a['url']
							)
						)
					);
				}
				
				$json -> template -> columns[] = $course_obj;
			}
			if($i == ($page_end+1)){
				$course_obj = array (
					"title" => "下一頁還有喔",
					"text" => "-",
					"actions" => array (
						array (
							"type" 	=> "postback",
							"label"	=> "下一頁",
							"data"	=> "nextmession&".++$page
						)
					)
				);
				$json -> template -> columns[] = $course_obj;
			}
			$i++;
		}
		return $json;
	}
	
	//每日簽到
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
					  "uri": "https://sporzfy.com/fb/teiichi/signup.html?line_id='.$sender_userid.'&type=add"
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
							"type": "uri",
							"label": "更新資料",
							"uri": "https://sporzfy.com/fb/teiichi/signup.html?line_id='.$sender_userid.'&type=update"
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
						  "label": "重新註冊",
						  "uri": "https://sporzfy.com/fb/teiichi/signup.html?line_id='.$sender_userid.'&type=resignup"
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
	
	//進行簽到
	function action_sign($sender_userid){
		$sql = "SELECT * FROM line_user_sign WHERE line_id ='".$sender_userid."' AND sign_date = '". date("Y-m-d")."'";
		$result = sql_select_fetchALL($sql);
		if($result->num_rows == 0){
			$sql = "INSERT INTO line_user_sign (line_id, sign_date) VALUES ('".$sender_userid."', '".date("Y-m-d")."')";
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

	//查看成就
	function see_achievement($sender_userid){
		$sql = "SELECT * FROM line_user_sign WHERE line_id ='".$sender_userid."'";
		$result = sql_select_fetchALL($sql);
		$signed_count = $result->num_rows;
		$sql = "SELECT user_created_date FROM line_user WHERE line_id ='".$sender_userid."'";
		$result = sql_select_fetchALL($sql);
		
		if($result->num_rows > 0){
			$user_created_date = "2018-07-25";
			foreach($result as $a){
				$user_created_date = $a['user_created_date'];
			}

			//expire_date
			$expire_date = 0;
			$date1=date_create(date("Y-m-d"));
			$date2=date_create("2018-11-24");
			$diff=date_diff($date1,$date2);
			if($diff->format("%R") == "+"){
				$expire_date = $diff->format("%a");
			}
			//join_date
			$join_date = 0;
			$date1=date_create($user_created_date);
			$date2=date_create(date("Y-m-d"));
			$diff=date_diff($date1,$date2);
			if($diff->format("%R") == "+"){
				$join_date = $diff->format("%a")+1;
			}
			//sign_rate
			$sign_rate = round($signed_count/$join_date*100);
			$json_str = '{
				"type": "text",
				"text": "距離三合一選舉還有'. $expire_date.'天\n已參戰: '.$join_date.'天 \n簽到率:'.$sign_rate.'%"
			}';
			$json = json_decode($json_str);
			return $json;
		}
	}

	//操作秘笈
	function operation($sender_userid){
		$sql = "SELECT * FROM line_user WHERE line_id ='".$sender_userid."' AND status = 1";
		$result = sql_select_fetchALL($sql);
		if($result->num_rows == 0){
			$json_str = '{
				"type": "text",
				"text": "請先註冊並審核後才可以使用此服務"
			}';
			$json = json_decode($json_str);
			return $json;
		}

		$json_str = '{
			"type": "template",
			"altText": "this is a buttons template",
			"template": {
			  "type": "buttons",
			  "actions": [
				{
				  "type": "message",
				  "label": "訂閱侯選人",
				  "text": "訂閱侯選人"
				},
				{
				  "type": "message",
				  "label": "操作說明",
				  "text": "操作說明"
				}
			  ],
			  "title": "操作秘笈",
			  "text": "操作秘笈"
			}
		}';
		$json = json_decode($json_str);
		return $json;
	}
	
	function subscribe_candidate($sender_userid) {
		$json_str = '{
				"type": "template",
				"altText": "this is a carousel template",
				"template": {
				"type": "carousel",
				"columns": []
				}
		}';
		$json = json_decode($json_str);
		$sql = "SELECT * FROM candidate WHERE type = '2'";
		$result = sql_select_fetchALL($sql);
		foreach($result as $a){
			$sql = "SELECT * 
					FROM alert_rss_subscribe 
					WHERE alert_id = '".$a['alert_id']."' AND line_id = '".$sender_userid."'";
			$result2 = sql_select_fetchALL($sql);
			if($result2->num_rows == 0) {
				$course_obj = array (
					"title" => $a['name'],
					"text" => "歡迎訂閱「".$a['name']."」臉書粉絲團及新聞",
					"actions" => array (
						array (
							"type" => "postback",
							"label"=> "訂閱",
				  			"data"=> "sub&".$a['alert_id']."&".$a['page_id']
						)
					)
				);
			} else {
				$course_obj = array (
					"title" => $a['name'],
					"text" => "「".$a['name']."」臉書粉絲團及新聞訂閱中",
					"actions" => array (
						array (
							"type" => "postback",
							"label"=> "取消訂閱",
				  			"data"=> "unsub&".$a['alert_id']."&".$a['page_id']
						)
					)
				);
			}
			$json -> template -> columns[] = $course_obj;
		}
		return $json;
	}

	function subscribe($action, $alert_id, $page_id, $sender_userid) {
		if($action == "sub") {
			$sql = "SELECT * FROM alert_rss_subscribe WHERE line_id ='".$sender_userid."' AND alert_id = '".$alert_id."'";
			$result = sql_select_fetchALL($sql);
			if($result->num_rows == 0){
				$sql = "INSERT INTO alert_rss_subscribe 
							(alert_id, line_id) 
						VALUES 
							('".$alert_id."', '".$sender_userid."')";
				sql_select_fetchALL($sql);
				$sql = "INSERT INTO fb_page_subscribe 
							(page_id, line_id) 
						VALUES 
							('".$page_id."', '".$sender_userid."')";
				sql_select_fetchALL($sql);
			}
			$json_str = '{
				"type": "text",
				"text": "訂閱成功"
			}';
			$json = json_decode($json_str);
			return $json;
		} else {
			$sql = "DELETE FROM alert_rss_subscribe WHERE line_id = '".$sender_userid."' AND alert_id = '".$alert_id."'";
			sql_select_fetchALL($sql);
			$sql = "DELETE FROM fb_page_subscribe WHERE line_id = '".$sender_userid."' AND page_id = '".$page_id."'";
			sql_select_fetchALL($sql);
			$json_str = '{
				"type": "text",
				"text": "取消訂閱成功"
			}';
			$json = json_decode($json_str);
			return $json;
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
