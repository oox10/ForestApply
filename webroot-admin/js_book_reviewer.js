/* [ Admin Book Function Set ] */
	
  $(window).load(function () {   //  || $(document).ready(function() {		
	
	
	//-- datepicker initial
	$("#search_date_start,#search_date_end").datepicker({
	    dateFormat: 'yy-mm-dd',
	});
	
	
    $('#editor_reform').click(function(){
	  $('#record_editor').hide();	
	});
    
	
	//-- renew index
	$('#reset_filter').click(function(){
	  location.href = 'index.php?act=Booking/R5review'	
	});
	
	//-- data search
	$('#filter_submit').click(function(){
      
	  var filter = {};
	  
	  if($('#filter_apply_area').val()){
		filter['apply_area'] = $('#filter_apply_area').val();  
	  }
	  
	  if( $('#filter_apply_code').val() ){
		filter['apply_code'] = $('#filter_apply_code').val();
	  }
	  
	  if( $('#filter_search_terms').val() ){
		filter['apply_search'] = $('#filter_search_terms').val();
	  }
	  
	  if( $('#search_date_start').val() ){
		filter['range_start'] = $('#search_date_start').val();
	  }
	  
	  if( $('#search_date_end').val() ){
		filter['range_end'] = $('#search_date_end').val();
	  }
	  
	  var pass_data = encodeURIComponent(Base64M.encode(JSON.stringify(filter)));
	  location.href = 'index.php?act=Booking/R5review/'+pass_data;
	
	});
	
	
	//-- record select all 全選本頁
	if($('.act_select_all').length){
	  $('.act_select_all').change(function(){
		$('.act_select_one,.act_select_all').prop('checked',$(this).prop('checked')); 
	  });
	}
	
	//-- record select one 單選本頁
	$('.act_select_one').click(function(){
	  var select_all_fleg = $('.act_select_one').length == $('.act_select_one:checked').length ? true : false;
	  $('.act_select_all').prop('checked',select_all_fleg);  	
	});
	
	
	
	//-- 外審人員批次同意
	$('#act_batch_accept').click(function(){
	  
	  if(!$('.act_select_one:checked').length){
		system_message_alert('','尚未選擇資料');  
	    return false;
	  }
	  var records    = $('.act_select_one:checked').map(function(){return $(this).val(); }).get();
	  
	  // confirm to admin
	  if(!confirm("確定要對勾選 [ "+records.length+" ] 筆資料皆同意申請")){
	    return false;  
	  }
	  
	  var pass_data = encodeURIComponent(Base64M.encode(JSON.stringify(records)));
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Booking/locreviewaccept/'+pass_data},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  location.reload();
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
      
	});
	
	
	//-- 外審人員審核同意
	$('.act_apply_accept').click(function(){
	  
	  // get value
	  var main_dom  = $(this).parents('tr.data_record');
	  var data_no   = main_dom.attr('no');
	  
	  if( ! data_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
      
	  if(!confirm("確定要同意申請?")){
		return false;	  
	  }
	  
	  var review = {};
	  review['status'] = '外審同意';
	  review['notes']  = '';  
	  
	  var pass_data = encodeURIComponent(Base64M.encode(JSON.stringify(review)));
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Booking/localreview/'+data_no+'/'+pass_data},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  main_dom.find('.apply_review_logs').empty()
			  $.each(response.data.review,function(i,logs){
				var dom = $("<li/>");
                dom.append("<label>"+logs.time+"</label>");
                dom.append("<label>"+logs.status+"</label>");
                dom.append("<label>"+logs.note+"</label>");
			    dom.appendTo( main_dom.find('.apply_review_logs'));
			  })
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
      
	});
	
	//-- 外審人員審核不同意
	$('.act_apply_reject').click(function(){
	  
	  // get value
	  var main_dom  = $(this).parents('tr.data_record');
	  var data_no   = main_dom.attr('no');
	  
	  if( ! data_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  
	  var reject_reason = main_dom.find('.apply_reject_reason').val();
	  if( ! reject_reason ){
	    system_message_alert('',"請填寫不同意理由");
		main_dom.find('.apply_reject_reason').focus();
		return false;
	  }
	  
	  
	  if(!confirm("確定要將申請單設定為不同意?")){
		return false;	  
	  }
	  
	  var review = {};
	  review['status'] = '外審異議';
	  review['notes']  = reject_reason;  
	  
	  var pass_data = encodeURIComponent(Base64M.encode(JSON.stringify(review)));
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Booking/localreview/'+data_no+'/'+pass_data},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  
			  main_dom.find('.apply_review_logs').empty()
			  $.each(response.data.review,function(i,logs){
				var dom = $("<li/>");
                dom.append("<label>"+logs.time+"</label>");
                dom.append("<div>"+logs.status+"</div>");
                dom.append("<div>"+logs.note+"</div>");
			    dom.appendTo( main_dom.find('.apply_review_logs'));
			  })
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
      
	});
	
	
  });	
  
  