
$( document ).ready(function() {
	
		$(document).on("click","#filterQuery",function() {
		
			getTaskList(1);
		});
		
		$(document).on("click",".editTopBtn",function() {
			
			var editTopMessage      = $(this).parent().parent().parent().parent().find('.postMessage').text();
			var editTopUrl          = $(this).parent().parent().parent().parent().find('.postUrl').attr('href');
			var editTopOhterMessage = $(this).parent().parent().find('.postRemarkText').text();
			var editTopTypeID       = $(this).parent().parent().find('.editRemarkInput').data('type_id');
				
			$('#editTopMessage').val(editTopMessage);
			$('#editTopUrl').val(editTopUrl);
			$('#editTopOhterMessage').val(editTopOhterMessage);
			$('#editTopTypeID').val(editTopTypeID);
			
			$('#editTopModal').modal('show');
		});
		
		
		$(document).on("click","#saveTopEdit",function() {
			

			var postData = {
				typeId         : $('#editTopTypeID').val(),
				type           : 'custom',
				editTopMessage : $('#editTopMessage').val(),
				editTopUrl     : $('#editTopUrl').val(),
				updateVale     : $('#editTopOhterMessage').val(),
 			};
			
			updateRemark(postData);
			
			
		});
		
		$(document).on("change",".postHideSelect",function() {
			
			var postData = {
				typeId     : $(this).parent().data('type_id'),
				type       : $(this).parent().data('type'),
				updateVale : $(this).val(),
 			};
		
			updatePostHide(postData);
			
		});
		
		$(document).on("click",".editRemarkBtn",function() {
			
			$(this).parent().parent().find('.postRemarkText').hide();
			$(this).parent().parent().find('.editRemarkInput').show();
			
			
			$(this).parent().find('.editRemarkBtn').hide();
			
			$(this).parent().find('.remarkSaveBtn').show();
			$(this).parent().find('.remarkCancelBtn').show();
			
		});
		
		$(document).on("click",".remarkSaveBtn",function() {
			
			var postData = {
				typeId     : $(this).parent().parent().find('.editRemarkInput').data('type_id'),
				type       : $(this).parent().parent().find('.editRemarkInput').data('type'),
				updateVale : $(this).parent().parent().find('.editRemarkInput').val(),
 			};
			
			updateRemark(postData);
		});
		
		$(document).on("click",".remarkCancelBtn",function() {
			$(this).parent().parent().find('.postRemarkText').show();
			$(this).parent().parent().find('.editRemarkInput').hide();
			
			$(this).parent().find('.editRemarkBtn').show();
			$(this).parent().find('.remarkSaveBtn').hide();
			
			$(this).parent().find('.remarkCancelBtn').hide();
			
			var origText = $(this).parent().parent().find('.postRemarkText').text();
			$(this).parent().parent().find('.editRemarkInput').val(origText);
		});
		
	
		const updateRemark = (postData)=> {
			
			console.log(postData);
			$.post( "controllers/updateRemark.php",postData,function( data ) {
	
				var result = jQuery.parseJSON(data);
				
				if(result.status == false){
					
					alert(result.error);
					return;
				}
				
				if(postData.type = 'custom'){
					location.reload();
				}
			
				var data = result.response.data;
				
				var findList = 'list_' + postData.type + "_" + postData.typeId;
				
				$('#'+findList).find('.postRemarkText').text(postData.updateVale);
				$('#'+findList).find('.postRemarkText').attr("title",postData.updateVale);

				$('#'+findList).find('.postRemarkText').show();
				$('#'+findList).find('.editRemarkBtn').show();
				$('#'+findList).find('.remarkSaveBtn').hide();
				$('#'+findList).find('.editRemarkInput').hide();
				$('#'+findList).find('.remarkCancelBtn').hide();
				
			});
			
		}
		
		const updatePostHide = (postData)=> {
			
			$.post( "controllers/updatePostHide.php",postData,function( data ) {
	
				var result = jQuery.parseJSON(data);
				
				if(result.status == false){
					
					alert(result.error);
					return;
				}
				
			});
			
		}
		
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
			
				getTaskList(num);
			 
			});
		}
	
		const getTaskList = (page)=> {
			
			var postData = {
				page      : page,
				candidate : $('#filterCandidate').val(),
				type      : $('#filterOrig').val(),
				title     : $('#filterTitle').val(),
				
			};
			
			$.post( "controllers/getTaskList.php",postData,function( data ) {
	
				var result = jQuery.parseJSON(data);
				
				if(result.status == false){
					
					alert('something error');
					return;
				}
				
				if(result.response.data.length <=0){
					
					$("#taskTableList").loadTemplate($("#taskTableNoData"));
					$('#page-selection').html('');
					return;
				}
				
				result.response.data = result.response.data.map(function(element) {
					
					element['operate'] = '';
					var id   = element['id'];
					var type = element['type'];
					
					var isShow = (element['post_hide'] == 2) ?　"" : "selected";
					var isHide = (element['post_hide'] == 1) ?　"" : "selected";
					
					var showOption = '<option value="1" ' +isShow+'>隱藏</option>';
					var hideOption = '<option value="2" ' +isHide+'>顯示</option>';
						
					element['postHideSelect'] = '<select class="form-control postHideSelect" style="padding: 0">' + showOption + hideOption +'</select>';
	
					element['isUrlShow'] = "";
		
					if(element['url'] == ""){
						element['isUrlShow'] = "display:none";
 					}
					element['typeText'] = (element['type'] == 'news' ) ? "新聞" : (element['type'] == 'fb' ) ? "臉書" : "置頂";
					element['listId']   = 'list_' + type + "_" + id;
					
					
					element['editView'] = '<button class="btn btn-light editRemarkBtn"><i class="fas fa-edit"></i></button ><button class="btn btn-danger hideView remarkSaveBtn" ><i class="fas fa-check-circle"></i></button ><button class="btn btn-secondary hideView remarkCancelBtn" ><i class="fas fa-times"></i></button >';
					
					
					if(element['type'] == 'custom'){
						element['editView'] = '<button class="btn btn-light editTopBtn"><i class="fas fa-edit"></i></button >';
					}
					
					return element;
				});
				
				
				$("#taskTableList").loadTemplate($("#taskTableData"),result.response.data);
				
				var pageIndex  = result.response.pageInfo.pageIndex;
				var totalCount = result.response.pageInfo.totalCount;
				var pageCount  = result.response.pageInfo.pageCount;
			
				initPageSelection(pageIndex,totalCount,pageCount);
				
			});
			
		}
	
		getTaskList(1);

});
