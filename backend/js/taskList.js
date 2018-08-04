
$( document ).ready(function() {
	
		$(document).on("click","#filterQuery",function() {
		
			getTaskList(1);
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
			
			$(this).parent().find('span').hide();
			$(this).parent().find('.editRemarkBtn').hide();
			$(this).parent().find('.editRemarkInput').show();
			$(this).parent().find('.remarkSaveBtn').show();
			$(this).parent().find('.remarkCancelBtn').show();
			
		});
		
		$(document).on("click",".remarkSaveBtn",function() {
			
			var postData = {
				typeId     : $(this).parent().find('.editRemarkInput').data('type_id'),
				type       : $(this).parent().find('.editRemarkInput').data('type'),
				updateVale : $(this).parent().find('.editRemarkInput').val(),
 			};
			
			updateRemark(postData);
		});
		
		$(document).on("click",".remarkCancelBtn",function() {
			$(this).parent().find('.postRemarkText').show();
			$(this).parent().find('.editRemarkBtn').show();
			$(this).parent().find('.remarkSaveBtn').hide();
			$(this).parent().find('.editRemarkInput').hide();
			$(this).parent().find('.remarkCancelBtn').hide();
			
			var origText = $(this).parent().find('.postRemarkText').text();
			$(this).parent().find('.editRemarkInput').val(origText);
		});
		
	
		const updateRemark = (postData)=> {
			
			$.post( "controllers/updateRemark.php",postData,function( data ) {
	
				var result = jQuery.parseJSON(data);
				
				if(result.status == false){
					
					alert(result.error);
					return;
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
						
				
					element['typeText'] = (element['type'] == 'news' ) ? "新聞" : "臉書";
					element['listId']   = 'list_' + type + "_" + id;
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
