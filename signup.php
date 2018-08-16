<?php
    date_default_timezone_set("Asia/Taipei");
    
    $type = $_GET['type'];
    $line_id = $_GET['line_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    if($type == 'add'){
        $sql = "INSERT INTO line_user 
                (line_id, name, phone, address, user_created_date) 
            VALUES 
                ('".$line_id."', '".$name."', '".$address."', '".$phone."', '".date("Y-m-d")."')";
        sql_select_fetchALL($sql);

        $sql = "SELECT * FROM candidate WHERE type = 1";
        $result = sql_select_fetchALL($sql);
        foreach($result as $a){
            $sql = "INSERT INTO alert_rss_subscribe 
						(alert_id, line_id) 
					VALUES 
						('".$a['alert_id']."', '".$line_id."')";
			sql_select_fetchALL($sql);
			$sql = "INSERT INTO fb_page_subscribe 
						(page_id, line_id) 
					VALUES 
						('".$a['page_id']."', '".$line_id."')";
            sql_select_fetchALL($sql);
        }

        header('Location: signupSuccessful.html');

    } else if($type == 'update'){
        $sql = "UPDATE line_user SET name = '".$name."', phone = '".$phone."', address = '".$address."' 
                WHERE line_id = '".$line_id."'";
        sql_select_fetchALL($sql);
        header('Location: updateSuccessful.html');

    } else {
        $sql = "UPDATE line_user SET 
                    name = '".$name."', 
                    phone = '".$phone."', 
                    address = '".$address."', 
                    user_created_date = '".date("Y-m-d")."',
                    status = '3' 
                WHERE line_id = '".$line_id."'";
        sql_select_fetchALL($sql);
        header('Location: signupSuccessful.html');

    }

	function sql_select_fetchALL($sql)
	{   
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
