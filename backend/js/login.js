
$( document ).ready(function() {

		$( "#btn_login" ).click(function() {
			login();
		});

		$(document).keypress(function(event) {
			if( event.charCode == 13 ) {
				login()
			}
		});
		
		function login(){
			
			var account  = $('#account').val();
			var password = $('#password').val();
			
		
			var postData = {
				account  : account,
				password : password,
			};
		
			$.post( "controllers/login.php", postData ,function( data ) {
				
				var result = jQuery.parseJSON(data);
				
				if(result.status == false){
					alert(result.error);
					return;
				}
				
				window.location = "userList.php";
				
			});
		}
});
