/* [ Admin Track Function Set ] */
	
  $(window).load(function () {   //  || $(document).ready(function() {		
	
	
	//-- click record & get record data
	
	$(document).on('click','._data_read',function(){
	  
      // initial	  
	  $('._target').removeClass('_target');
	  
	  // get value
	  var report_no    = $(this).attr('no');
	  var dom_record = $(this);
	  
	  // active ajax
	  if( ! report_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  initial_record_editer();
	  clean_module_relate_block();
	  
		$.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Tracking/read/'+report_no},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			
			console.log(response);
			
			if(response.action){  
			  
			  dom_record.addClass('_target');
			  
			  var dataObj =  response.data.report;
			  data_orl = dataObj;
			  
			  // change _data_read area
			  $dom = dom_record.clone().removeClass('_data_read');
			  $('#record_selecter').find('.record_control').hide();
			  $('#record_selecter').find('.record_list').children('.data_result').hide();
			  $('#record_selecter').find('.record_list').children('.data_target').empty().append( $dom).show();
			  $('#record_editor').find('a.view_switch').trigger('click');
			  
			  // insert data
			  insert_report_data_to_form(dataObj);
			  
			  // set foot print 
			  active_header_footprint_option('record_selecter',dataObj.fb_type+"("+dataObj.fb_time+")",'_return_list');
			  
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
		}).done(function() { system_loading();   });
		
	});
	
	
	//-- save data modify
	$('#act_report_save').click(function(){
	  
      // initial	  
	  var report_no    =  $('._target').length? $('._target').attr('no') : '';
	  var modify_data  =  {};
	 
	  var act_object  = $(this);
	  var checked = true;
	  
	  // option active checked  // 檢查按鈕是否在可執行狀態
	  if( act_object.prop('disabled') ){
	    return false;
	  }
	  
	  // check process data
	  if( !report_no.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  }
	  
	  modify_data['fb_treatment']  =  $('#fb_treatment').val() ;
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64.encode(JSON.stringify(modify_data)));
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Tracking/update/'+report_no+'/'+passer_data},
		beforeSend: function(){  active_loading(act_object,'initial'); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			var dataObj = response.data.report;
			data_orl = dataObj;
			clean_module_relate_block();
			insert_report_data_to_form(dataObj);
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action );  });
	});
	
	
	//-- save message modify
	$('#submit_fb_note').click(function(){
	  
      // initial	  
	  var report_no    =  $('._target').length? $('._target').attr('no') : '';
	  var message      =  $('#user_massage').val() ;
	 
	  var act_object  = $(this);
	  var checked = true;
	  
	  // option active checked  // 檢查按鈕是否在可執行狀態
	  if( act_object.prop('disabled') ){
	    return false;
	  }
	  
	  // check process data
	  if( !report_no.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  } 
	  
	  if( !message.length ){
	    return false;
	  } 
	  
	  // encode data
	  var passer_data  = encodeURIComponent(message);
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Tracking/messg/'+report_no+'/'+passer_data},
		beforeSend: function(){  active_loading(act_object,'initial'); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  
		  if(response.action){
			$.each(response.data.note,function(fb_i,fb_m){
			  $('<div/>').addClass('note_messg').html(fb_m).prependTo("._variable[id='fb_note']");   
			  $('<div/>').addClass('note_index').html(fb_i).prependTo("._variable[id='fb_note']");   
			});
			$('#user_massage').val('').focus();
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action );  });
	});
	
	
	//-- set report data to editer  // 從server取得資料並放入編輯區
	function insert_report_data_to_form(dataObj){
	  var dom_record  = $('._target'); 
	  
	  $.each(dataObj,function(field,meta){
		if(field=='fb_preview'){
		  $("<img>").addClass('preview').attr('src',meta).appendTo("._variable[id='"+field+"']");
		}else if(field=='fb_logs'){
          $.each(meta,function(fb_i,fb_m){
			$('<div/>').addClass('note_index').html(fb_i).appendTo("._variable[id='fb_note']");  
		    $('<div/>').addClass('note_messg').html(fb_m).appendTo("._variable[id='fb_note']");  
		  });
		}else{
		  if(  $("._variable[id='"+field+"']").length ){  
			$("._variable[id='"+field+"']").val(meta);  
		  }
		}
		// update target record 
		var record_field = dom_record.find("td[field='"+field+"'] , div[field='"+field+"']");
		if( record_field.length && record_field.html() != meta  ){
		  record_field.html(meta); 
	    }
	  });
	  
	  $('._modify').removeClass('_modify');
	}
	
	
	//-- iterm function execute
	$('#act_func_execute').click(function(){
	  
	  var report_no    =  $('._target').length? $('._target').attr('no') : '';
	  var execute_func =  $('#execute_function_selecter').length ? $('#execute_function_selecter').val() : '';
	  
	  // check process target
	  if( !report_no.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  }  
	  
	  // check process action
	  if( !execute_func.length ){
	    system_message_alert('',"尚未選擇執行功能");
	    return false;
	  } 
	  
	  // function check
	  if(execute_func=='endfb' && $('#fb_treatment').val()==''){
	    system_message_alert('',"結案前須填寫處理結果!!");
		return false;  
	  }
	  
	  if(execute_func=='endfb' && $('._modify').length){
	    system_message_alert('',"請先儲存資料");
		return false;  
	  }
	  
	  // confirm to admin
	  if(!confirm("確定要對資料執行 [ "+$("option[value='"+execute_func+"']").html()+" ] ?")){
	    return false;  
	  }
	  
	   // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Tracking/'+execute_func+'/'+report_no},
		beforeSend: function(){  system_loading(); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			switch(execute_func){
			  case 'endfb'  : location.reload();  break;
			}
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading(); });
	  $('#execute_function_selecter').val('');
	});
	
	//-- 設定分頁
	$('.record_view').val(10).trigger('change');
	
	
	
	//**--  模組關聯函數  -- **//
	
	function clean_module_relate_block(){
	  $("#fb_preview").empty();
	}
	
	
	
	
  });	