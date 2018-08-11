<?php
	date_default_timezone_set("Asia/Taipei");
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
	
	$page = 1;
	
	if(isset($_POST['page'])){
		$page = $_POST['page'];
	}
	
	
	$pageInfo = [];
	
	$sql    = "SELECT count(*) as total FROM line_user ";
	
	if(!empty($_POST['status'])){
		$sql = $sql . "where status =" . $_POST['status'];
	}
	
	$resultCount = sqlSelect($sql,$dbConfig);
	
	while($row = $resultCount->fetch_assoc()) {
		
		$pageInfo = [
			'pageIndex'  => $page, 
			'totalCount' => $row['total'],
			'pageCount'  => 10,
		];
    }
	
	$startIndex = ( $pageInfo['pageIndex'] -1) * $pageInfo['pageCount'];
	
	$sql = "SELECT * FROM line_user ";
	
	if(!empty($_POST['status'])){
		$sql = $sql . "where status =" . $_POST['status'];
	}
	
	$sql = $sql . " LIMIT " .$startIndex . ", " . $pageInfo['pageCount']  ;
	$result = sqlSelect($sql,$dbConfig);

	$resultData  = [];
	$resultCount = 1;
	while($row = $result->fetch_assoc()) {
		$sql = "SELECT count(*) as total FROM line_user_sign WHERE line_id = '".$row['line_id']."'";
		$resultSignCount = sqlSelect($sql,$dbConfig);
		$total = 0;
		while($rowSignCount = $resultSignCount->fetch_assoc()) {
			$total = $rowSignCount['total'];
		}
		$joinDate = 1;
		$date1=date_create($row['user_created_date']);
		$date2=date_create(date("Y-m-d"));
		$diff=date_diff($date1,$date2);
		if($diff->format("%R") == "+"){
			$joinDate = $diff->format("%a")+1;
		}
		$signRate = round($total/$joinDate*100);
		$value = [
			'index'      	  => (( $pageInfo['pageIndex'] -1) * $pageInfo['pageCount']) + $resultCount,
			'userId'     	  => $row['id'],
			'userName'   	  => $row['name'],
			'userPhone'  	  => $row['phone'],
			'userStatus' 	  => $row['status'],
			'userCreatedDate' => $row['user_created_date'],
			'userSignCount'	  => $total,
			'userSignRate'	  => $signRate."%",
			'statusText' 	  => _getUserStatus($row['status']),
		];
		//print_r($value);
		array_push($resultData, $value);
		
		$resultCount = $resultCount+1;
    }
	
	
	echo json_encode (
			array (
				'status'   => true,
				'response' => [
					'data'     => $resultData,
					'pageInfo' => $pageInfo,
				],
				'sql'      => $sql,
				'error'    => ''
			
			)
		);	
	return;
	
	
?>