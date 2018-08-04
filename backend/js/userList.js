
$( document ).ready(function() {
	
	
		$(document).on("click","#filterQuery",function() {
		
			getUserList(1);
		});
	
	
		$(document).on("click",".passUser",function() {
			var postData = {
				userId : $(this).data('userid'),
				status : 1,
 			};
			
			updateUserStatus(postData);
		});
	
		$(document).on("click",".rejectUser",function() {
			var postData = {
				userId : $(this).data('userid'),
				status : 2,
 			};
			
			updateUserStatus(postData);
		});
	
		const initPageSelection = (page,total,size)=> {
			
			$('#page-selection').html('');
			
			
			$('#page-selection').bootpag({
				maxVisible  : 5,
				total       : Math.ceil(total/size),
				page        : page,
				firstLastUse: true,
				first       : '首頁',
				last        : '未頁',
			}).on("page", function(event, num){
			
				getUserList(num);
			 
			});
		}
	
		const updateUserStatus = (postData)=> {
			
			$.post( "controllers/updateUserStatus.php",postData,function( data ) {
	
				var result = jQuery.parseJSON(data);
				
				if(result.status == false){
					
					alert('something error');
					return;
				}
				
				var data = result.response.data;
				
				$('#userTableList').find('#list_' +  data.userId).find('.statusText').text(data.statusText);
				$('#userTableList').find('#list_' +  data.userId).find('.operate').hide();;
				
			});
			
		}
	
		const getUserList = (page)=> {
			
			var postData = {
				page  : page,
				status : $('#filterStatus').val(),
			};

			$.post( "controllers/getUserList.php",postData,function( data ) {
	
				var result = jQuery.parseJSON(data);
				
				if(result.status == false){
					
					alert('something error');
					return;
				}
				
				if(result.response.data.length <=0){
					
					$("#userTableList").loadTemplate($("#userTableNoData"));
					$('#page-selection').html('');
					return;
				}
				
				result.response.data = result.response.data.map(function(element) {
					
					element['operate'] = '';
					var id = element['userId'];
					if(element['userStatus'] == 3){
						
						var passBtn   = '<button class="btn btn-success passUser" data-userid="'+ id +'"><i class="fas fa-check"></i> 通過</button> ';
						var rejectBtn = '<button class="btn btn-danger rejectUser" data-userid="'+ id +'"><i class="fas fa-times"></i> 拒絕</button>';
						
						element['operate'] = passBtn + rejectBtn
						
					}
					
					element['listId'] = 'list_' + id;
					return element;
				});
				
				$("#userTableList").loadTemplate($("#userTableData"),result.response.data);
				
				var pageIndex  = result.response.pageInfo.pageIndex;
				var totalCount = result.response.pageInfo.totalCount;
				var pageCount  = result.response.pageInfo.pageCount;
			
				initPageSelection(pageIndex,totalCount,pageCount);
			});
			
		}
	
		getUserList(1);

});
