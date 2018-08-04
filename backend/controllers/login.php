<?php

	session_start();

	if(empty($_POST['account'])){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => '請輸入帳號'
			
			)
		);	
		return;
	}
	
	if(empty($_POST['password'])){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => '請輸入密碼'
			
			)
		);
		return;
	}
		

	$account  = $_POST['account'];
	$password = $_POST['password'];

	$configs = include('../config/password.php');
	
	if($account != $configs['account']){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => '帳號或密碼錯誤'
			
			)
		);	
		return;
	}
	
	if($password != $configs['password']){
		echo json_encode (
			array (
				'status' => false,
				'data'   => [],
				'error'  => '帳號或密碼錯誤'
			
			)
		);	
		return;
	}
	
	$_SESSION['login'] = 1;
	
	echo json_encode (
			array (
				'status' => true,
				'data'   => [
					'link' => '',
				],
				'error'  => ''
			
			)
		);	
	return;

?>