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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
	
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<script src="js/lib/jquery.loadTemplate.js"></script>
	<script src="js/taskList.js"></script>

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
		
		.ellipsis{
			display: inline-block;
			width: 250px;
			overflow: hidden;
			white-space: nowrap;
			text-overflow: ellipsis;
			
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
			<div class="col-md-1"></div>
			<div class="col-md-10 divBackgorWhite">
			
				<div class="row">
					<div class="col-md-8 nav">
						<a href="taskList.php" class="btn btn-light" ><i class="fas fa-arrow-alt-circle-left"></i> 任務清單</a>
						<a href="userList.php" class="btn btn-light" ><i class="fas fa-arrow-alt-circle-left"></i> 用戶清單</a>
					</div>
					<div class="col-md-4" style="padding: 15px;">
						<a href="index.php" class="btn btn-secondary" style="float:right"><i class="fas fa-sign-out-alt"></i></i> Logout</a>
					</div>
				</div>
			
				<div class="alert alert-info" role="alert">
					<i class="fas fa-tasks"></i> 任務清單
				</div>
				<nav class="navbar navbar-light bg-light">
				  <div class="form-inline">
					
					<label style="margin-right: 8px;">來源 : </label>
					<select id="filterOrig" class="form-control" style="height:35px; margin-right: 8px;">
						<option value="">全部</option>
						<option value="fb">Facebook</option>
						<option value="news">新聞</option>
					</select>
					<label style="margin-right: 8px;">候選人: </label>
					
					
					<input id="filterCandidate" style="margin-right: 8px;" class="form-control" ></input>
					
					<button class="btn btn-primary" id="filterQuery">Search</button>
				  </div>
				</nav>
				<div class="row" id="aliasList" style="padding:20px">
					<table class="table table-hover">
						<thead>
							<tr>
								<th scope="col"></th>
								<th scope="col">來源</th>
								<th scope="col">訊息</th>
								<th scope="col">URL</th>
								<th scope="col">候選人</th>
								<th scope="col">補充訊息</th>
								<th scope="col" style="text-align:right">建立日期</th>
								<th scope="col" style="text-align:right">是否隱藏</th>
							</tr>
						</thead>
						<tbody id="taskTableList">
							
						</tbody>
					</table>
					
					<div id="page-selection" class="pageSelection"></div>
				</div>
			</div>
			<div class="col-md-1"></div>
		</div>
	</div>
	<div id="loaderMask" class="hideView">
		<div id="loader"></div>
	</div>
  </body>
</html>

<script type="texteditAlias/html" id="taskTableData">
	<tr data-id="listId">
		<td><span data-content="index"></span></td>
		<td><span data-content="typeText" ></span></td>
		<td><span class="ellipsis"  data-content="title" data-template-bind='[{"attribute": "title", "value": "title"}]'></span></td>
		<td>
			<a class="btn btn-light" data-href="url" target="_blank"><i class="fas fa-link"></i></a><span data-content=""></span>
		</td>
		<td><span data-content="candidate"></span></td>
		<td>
			<div style="white-space: nowrap;">
				<span class="ellipsis postRemarkText" data-content="postRemark" data-template-bind='[{"attribute": "title", "value": "postRemark"}]'></span>
				<input  style="width:250px" class="form-control hideView editRemarkInput" data-value="postRemark" data-template-bind='[{"attribute": "data-type", "value": "type"},{"attribute": "data-type_id", "value": "id"}]'></input>
				
				<button class="btn btn-light editRemarkBtn"><i class="fas fa-edit"></i></button >
				<button class="btn btn-danger hideView remarkSaveBtn" ><i class="fas fa-check-circle"></i></button >
				<button class="btn btn-secondary hideView remarkCancelBtn" ><i class="fas fa-times"></i></button >
			</div>
		</td>
		<td style="text-align:right"><span data-content="post_created_time"></span></td>
		<td style="text-align:right">
			<div id="post_hide" data-content="postHideSelect" data-template-bind='[{"attribute": "data-type", "value": "type"},{"attribute": "data-type_id", "value": "id"}]'>
			</div>
		</td>
	</tr>
</script>


<script type="texteditAlias/html" id="taskTableNoData">
	<tr>
		<td colspan="8" align="center">No Data</data>
	</tr>
</script>