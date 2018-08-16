<?php 
	session_start();
	
	if(empty($_SESSION['login']) || $_SESSION['login'] != 1){
			header('Location: index.php', true, $permanent ? 301 : 302);
			exit();
	}
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<script src="js/lib/jquery-3.3.1.min.js"></script>
	<script src="js/lib/jquery.bootpag.min.js"></script>
	
	<!-- Bootstrap CSS -->
    <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css">-->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
	
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<script src="js/lib/jquery.loadTemplate.js"></script>
	<script src="js/userList.js"></script>

	<style>
		.nav {
			padding: 15px;
		}
		
		.nav a {
			margin-right: 5px;
		}
	
		.hideView{
			display : none
		}
		
		body {
			background-color: #f8f8f8;
		}
		
		.divBackgorWhite {
			border-radius: 5px;
			background-color: #ffffff;
			padding: 20px;
		}
		
		
		.pageSelection {
			width: 100%;
			text-align: center;
		}
		
	</style>
	
	<style>
		/* Center the loader */
		#loader {
			position: absolute;
			left: 50%;
			top: 50%;
			z-index: 1;
			width: 150px;
			height: 150px;
			margin: -75px 0 0 -75px;
			border: 16px solid #f3f3f3;
			border-radius: 50%;
			border-top: 16px solid #3498db;
			width: 120px;
			height: 120px;
			-webkit-animation: spin 2s linear infinite;
			animation: spin 2s linear infinite;
		}

		@-webkit-keyframes spin {
			0% { -webkit-transform: rotate(0deg); }
			100% { -webkit-transform: rotate(360deg); }
		}

		@keyframes spin {
			0% { transform: rotate(0deg); }
			100% { transform: rotate(360deg); }
		}

		/* Add animation to "page content" */
		.animate-bottom {
			position: relative;
			-webkit-animation-name: animatebottom;
			-webkit-animation-duration: 1s;
			animation-name: animatebottom;
			animation-duration: 1s
		}

		@-webkit-keyframes animatebottom {
			from { bottom:-100px; opacity:0 } 
			to { bottom:0px; opacity:1 }
		}

		@keyframes animatebottom { 
			from{ bottom:-100px; opacity:0 } 
			to{ bottom:0; opacity:1 }
		}

		
		#loaderMask{
			background-color: #cc0000;
			height:100%;
			width:100%;
		}

	</style>

    <title></title>
  </head>
  <body>

	<div class="container-fluid animate-bottom " style="margin-top:20px">
		
		<div class="row">
			<div class="col-md-2"></div>
			<div class="col-md-8 divBackgorWhite">
			
				<div class="row">
					<div class="col-md-8 nav">
						<!-- <a href="userList.php" class="btn btn-light" ><i class="fas fa-arrow-alt-circle-left"></i> 用戶清單</a> -->
						<a href="taskList.php" class="btn btn-light" ><i class="fas fa-arrow-alt-circle-left"></i> 任務清單</a>
					</div>
					<div class="col-md-4" style="padding: 15px;">
						<a href="index.php" class="btn btn-secondary" style="float:right"><i class="fas fa-sign-out-alt"></i></i> Logout</a>
					</div>
				</div>
			
				<div class="alert alert-info" role="alert">
					<i class="fas fa-user-tie"></i> 用戶清單
				</div>
				
				<nav class="navbar navbar-light bg-light">
				  <div class="form-inline">
					
					<label style="margin-right: 5px;">審核狀態 : </label>
					<select id="filterStatus" class="form-control" style="height:35px; margin-right: 5px;">
						<option value="">全部</option>
						<option value="1">通過</option>
						<option value="2">拒絕</option>
						<option value="3">待審</option>
					</select>
					
					<button class="btn btn-primary" id="filterQuery">Search</button>
				  </div>
				</nav>
				
				<div class="row" id="aliasList" style="padding:20px">
					<table class="table table-hover">
						<thead>
							<tr>
								<th scope="col"></th>
								<th scope="col">姓名</th>
								<th scope="col">電話</th>
								<th scope="col">地址</th>
								<th scope="col">註冊日</th>
								<th scope="col">簽到數</th>
								<th scope="col">簽到率</th>
								<th scope="col">狀態</th>
								<th scope="col" style="text-align:right">操作</th>
							</tr>
						</thead>
						<tbody id="userTableList">
							
						</tbody>
					</table>
					
					<div id="page-selection" class="pageSelection"></div>
				</div>
			</div>
			<div class="col-md-2"></div>
		</div>
	</div>
	<div id="loaderMask" class="hideView">
		<div id="loader"></div>
	</div>
  </body>
</html>

<script type="texteditAlias/html" id="userTableData">
	<tr data-id="listId">
		<td><span data-content="index"></span></td>
		<td><span data-content="userName"></span></td>
		<td><span data-content="userPhone"></span></td>
		<td><span data-content="userAddress"></span></td>
		<td><span data-content="userCreatedDate"></span></td>
		<td><span data-content="userSignCount"></span></td>
		<td><span data-content="userSignRate"></span></td>
		<td><span data-content="statusText" class="statusText"></span></td>
		<td style="text-align:right">
			<div class="operate" data-content="operate"><div>
		</td>
	</tr>
</script>

<script type="texteditAlias/html" id="userTableNoData">
	<tr>
		<td colspan="6" align="center">No Data</data>
	</tr>
</script>


