<?php 
	session_start();
	session_destroy(); 
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<script src="js/lib/jquery-3.3.1.min.js"></script>
	
	<!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
	

	<script src="js/login.js"></script>

	
	<style>
		body {
			background-color: #f8f8f8;
		}
		
		.divBackgorWhite {
			border-radius: 5px;
			background-color: #ffffff;
			padding: 20px;
		}
	</style>
   
    <title></title>
  </head>
  <body>
	<div class="container-fluid " style="margin-top:5%">
		<div class="row">
			<div class="col-md-4"></div>
			<div class="col-md-4 divBackgorWhite">
			
				<div class="alert alert-primary" role="alert">
				  青出於藍 青工會 line@backend
				</div>
				<form role="form" >
					<div class="form-group has-error">
						<!--<label for="account inputError">帳號</label>-->
						<input type="text" class="form-control" id="account" placeholder="請輸入帳號">
					</div>
					<div class="form-group has-warning">
						<!--<label for="password inputWarning">密碼</label>-->
						<input type="password" class="form-control" id="password" placeholder="請輸入密碼">
					</div>
					<button type="button" id="btn_login" class="form-control btn btn-default btn-primary float-right" style="padding: 10px">Login</button>
				</form>
			</div>
			<div class="col-md-4"></div>
		</div>
	</div>
   
  </body>
</html>