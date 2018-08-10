<?php

	session_start();
	
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
	
	
	if(empty($_POST['typeId'])){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => '請選擇任務'
			
			)
		);
		return;
	}
	
	if(empty($_POST['type'])){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => '請選擇任務'
			
			)
		);
		return;
	}
	
	if(!isset($_POST['updateVale'])){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => '請填入補充訊息'
			
			)
		);
		return;
	}
	
	
	$typeId     = $_POST['typeId'];
	$type       = $_POST['type'];
	$updateVale = $_POST['updateVale'];
	
	if($type == 'news'){
		$sql    = "UPDATE alert_rss_post SET post_remark='". $updateVale ."' WHERE id  ='". $typeId ."'";
	} else if ($type == 'custom'){
		
		$editTopMessage = '';
		$editTopUrl     = '';
		
		if(isset($_POST['editTopMessage'])){
			$editTopMessage = $_POST['editTopMessage'];
		}
		
		if(isset($_POST['editTopUrl'])){
			$editTopUrl = $_POST['editTopUrl'];
		}
		
		$sql    = "UPDATE cus_post SET post_title='". $editTopMessage ."' , post_url='". $editTopUrl ."' , post_remark='". $updateVale ."'  WHERE id  ='". $typeId ."'";
	}
	 else {
		$sql    = "UPDATE fb_post SET SET post_remark='". $updateVale ."' WHERE id  ='". $typeId ."'";
	}

	$result = sqlSelect($sql,$dbConfig);

	if ($result === TRUE) {
		
		echo json_encode (
			array (
				'status'   => true,
				'response' => [
					'data'     => []
				],
				'error'    => '',
				'sql'      => $sql,
			
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