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
	
	$page            = 1;
	$filterCandidate = '';
	$filterTitle     = '';
	
	if(isset($_POST['page'])){
		$page = $_POST['page'];
	}
	
	if(!empty($_POST['candidate'])){
		$filterCandidate = $_POST['candidate'];
	}
	
	if(!empty($_POST['title'])){
		$filterTitle = $_POST['title'];
	}
	
	
	$pageInfo = [];
	$sql    = "SELECT count(*) as total FROM (
		SELECT 
			arp.id, c.name as candidate, arp.post_title as title, arp.post_url as url, arp.post_published as post_created_time, arp.post_remark, 'news' as type, post_hide 
		FROM alert_rss_post arp , candidate c 
		WHERE arp.alert_id = c.alert_id 
		And c.name like '%" . $filterCandidate ."%'
		And arp.post_title like '%" . $filterTitle ."%'
		UNION 
		SELECT 
			fp.id, c.name as candidate, fp.post_message as title, fp.post_url as url, fp.post_created_time, fp.post_remark, 'fb' as type, '2' as post_hide 
		FROM `fb_post` fp, candidate c 
		WHERE fp.post_status = 1 and fp.page_id = c.page_id	
		And c.name like '%" . $filterCandidate ."%'
		And fp.post_message like '%" . $filterTitle ."%'
		
		) as t

		";
		
		if(!empty($_POST['type'])){
			$sql = $sql . " WHERE type ='" . $_POST['type'] ."'";
		}
	
	/*
	if(!empty($_POST['status'])){
		$sql = $sql . "where status =" . $_POST['status'];
	}*/
	
	$resultCount = sqlSelect($sql,$dbConfig);
	
	while($row = $resultCount->fetch_assoc()) {
		
		$pageInfo = [
			'pageIndex'  => $page, 
			'totalCount' => $row['total'],
			'pageCount'  => 10,
		];
    }
	
	$startIndex = ( $pageInfo['pageIndex'] -1) * $pageInfo['pageCount'];
	
	
	
	$sql    = "SELECT * FROM (
		SELECT 
			arp.id, c.name as candidate, arp.post_title as title, arp.post_url as url, arp.post_published as post_created_time, arp.post_remark, 'news' as type, post_hide 
		FROM alert_rss_post arp , candidate c 
		WHERE arp.alert_id = c.alert_id 
		And c.name like '%" . $filterCandidate ."%'
		And arp.post_title like '%" . $filterTitle ."%'
		UNION 
		SELECT 
			fp.id, c.name as candidate, fp.post_message as title, fp.post_url as url, fp.post_created_time, fp.post_remark, 'fb' as type, '2' as post_hide 
		FROM `fb_post` fp, candidate c 
		WHERE fp.post_status = 1 and fp.page_id = c.page_id	
		And c.name like '%" . $filterCandidate ."%'
		And fp.post_message like '%" . $filterTitle ."%'
		
		) as t
		
		";
		
		if(!empty($_POST['type'])){
			$sql = $sql . " WHERE type ='" . $_POST['type'] ."'";
		}
	

	$sql    = $sql . " LIMIT " .$startIndex . ", " . $pageInfo['pageCount']  ;

	
	$result = sqlSelect($sql,$dbConfig);

	$resultData  = [];
	$resultCount = 1;
	while($row = $result->fetch_assoc()) {
		
		$value = [
			'index'             => (( $pageInfo['pageIndex'] -1) * $pageInfo['pageCount']) + $resultCount,
			'id'                => $row['id'],
			'candidate'         => $row['candidate'],
			'title'             => $row['title'],
			'type'              => $row['type'],
			'url'               => $row['url'],
			'postRemark'        => $row['post_remark'],
			'post_created_time' => date('Y/m/d H:i:s', $row['post_created_time']),
			'post_hide'         => $row['post_hide'],
		];
		
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