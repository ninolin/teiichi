<?php

	session_start();
	
	function _getUserStatus($status = ''){
	
		if($status == 1){
			return '通過';
		}
		
		if($status == 2){
			return '拒絕';
		}
		
		return '待審';
	}
	
	function sqlSelect($sql = '' , $dbConfig=[]){
		$servername = $dbConfig['ip'];
		$username   = $dbConfig['account'];
		$password   = $dbConfig['password'];
		$dbname     = $dbConfig['DBname'];
		
		$con = mysqli_connect($servername, $username, $password) or die("資料庫登入錯誤");
		
		if(mysqli_connect_errno($con)){
			echo "ERROR1";
		}
			
		mysqli_query($con,"SET NAMES utf8");
		mysqli_select_db($con,$dbname) or die("資料庫連結錯誤");
		
		$result = mysqli_query($con,$sql);
		mysqli_close($con);
		
		return $result ;
	}
	
	if(empty($_SESSION['login']) || $_SESSION['login'] != 1){
			header('Location: ../index.php', true, $permanent ? 301 : 302);
			exit();
	}

	$dbConfig = include('../config/dbConfig.php');
	
	if(empty($dbConfig['ip'])){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => '查無此Database'
			
			)
		);
		return;
	}
	
	if(empty($dbConfig['account'])){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => '查無此Database'
			
			)
		);
		return;
	}
	
	if(!isset($dbConfig['password'])){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => '查無此Database'
			
			)
		);
		return;
	}
	
	if(empty($dbConfig['DBname'])){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => '查無此Database'
			
			)
		);
		return;
	}
	
	
	if(empty($_POST['userId'])){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => '請選擇使用者'
			
			)
		);
		return;
	}
	
	if(empty($_POST['status'])){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => 'something error'
			
			)
		);
		return;
	}
	
	
	$userId = $_POST['userId'];
	
	$status = $_POST['status'];
	
	$sql    = "UPDATE line_user SET status='". $status ."' WHERE id  ='". $userId ."'";
	$result = sqlSelect($sql,$dbConfig);

	if ($result === TRUE) {
		
		
		$sql        = "SELECT * FROM line_user WHERE id  ='". $userId ."'";
		$resultInfo = sqlSelect($sql,$dbConfig);
		
		$resultData  = [];
		while($row = $resultInfo->fetch_assoc()) {
			
			$value = [
				'userId'     => $row['id'],
				'userName'   => $row['name'],
				'userPhone'  => $row['phone'],
				'userStatus' => $row['status'],
				'statusText' => _getUserStatus($row['status']),
			];
			
			array_push($resultData, $value);
		}
	
		
		echo json_encode (
			array (
				'status'   => true,
				'response' => [
					'data'     => $resultData[0],
				],
				'error'    => ''
			
			)
		);	
	
	} else {
		echo json_encode (
			array (
				'status' => false,
				'response'   => [],
				'error'  => "Error: " . $sql 
			
			)
		);	
	}
	
?>