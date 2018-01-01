/* [ Admin Book Function Set ] */
	
  $(window).load(function () {   //  || $(document).ready(function() {		
	
	
	//-- 
	$(window).resize(function() {
	  if($('.system_main_area').width() < 1220 ){
		$('.system_content_area').addClass('wide_mode');	  
	  }	
	});
	
	//-- mode initial
	$('li.mode_switch').click(function(){  	
	  location.href = 'index.php?act=Apply/'+$(this).data('mode');  	
	});
	
	//-- datepicker initial
	$("#search_date_start,#search_date_end").datepicker({
	    dateFormat: 'yy-mm-dd',
	});
	
    
    $('#editor_reform').click(function(){
	  $('#record_editor').hide();	
	});
    
	
	//-- 設定分頁 資料超過上萬，分頁自訂
	$(document).off('click','.page_to');
	
	//-- 切頁換頁
	$('.page_to').click(function(){
	  if(!$(this).attr('page')){
	    return false;
	  }	
	  var link = location.search.replace('/#.*?$/','').split('/');
	  link[3] = $(this).attr('page');
	  location.search = link.join('/');
	});
	
	$('.page_jump').change(function(){
	  if(!$(this).val()){
	    return false;
	  }	
	  var link = location.search.replace('/#.*?$/','').split('/');
	  link[3] = $(this).val();
	  location.search = link.join('/');
	});
	
	
	//-- select singel area type
	$('#filter_area_type').change(function(){
	  var area_type = $(this).val();	
	  if(area_type=='ALL'){
		$('#filter_apply_area').find("optgroup").show().end().val('');  
	  }else{
		$('#filter_apply_area').find("optgroup").hide();
	    $('#filter_apply_area').find("optgroup[label='"+area_type+"']").show().end().val(''); 
	  }
	});
	
	
	//-- renew index
	$('#reset_filter').click(function(){
	  location.href = 'index.php?act=Booking/index/ALL'	
	});
	
	//-- data search
	$('#filter_submit').click(function(){
      
      var area_selected = $('.filter_area_type').map(function(){ if($(this).prop('checked')) return $(this).val(); }).get();
	  var area_types    = 'ALL';
	  if(area_selected.length != $('.filter_area_type').length){
		area_types = area_selected.join(',');  
	  }
	  
	  var filter = {};
	  
	  if($('#filter_area_type').val()!='ALL'){
		filter['area_type'] = $('#filter_area_type').val();  
	  }
	  
	  if($('#filter_apply_area').val()){
		filter['apply_area'] = $('#filter_apply_area').val();  
	  }
	  
	  if($('#filter_search_terms').val()){
		filter['apply_search'] = $('#filter_search_terms').val();  
	  }
	  if( $('#filter_apply_status').val() ){
		filter['apply_status'] = $('#filter_apply_status').val();
	  }
	  if( $('#filter_is_review').prop('checked')){
		filter['apply_review'] = 1;  
	  }
	  if( $('#filter_is_check').prop('checked')){
		filter['apply_checked'] = 1;  
	  }
	  if( $('#filter_is_unfinish').prop('checked')){
		filter['apply_unfinish'] = 1;  
	  }
	  
	  if($('#filter_date_type').val()){
		if( !$('#search_date_start').val() && !$('#search_date_end').val() ){
		  system_message_alert('','請填寫時間範圍');
          return false;		  
		}
		filter[$('#filter_date_type').val()] = {};
		filter[$('#filter_date_type').val()]['date_start'] = $('#search_date_start').val();
		filter[$('#filter_date_type').val()]['date_end'] = $('#search_date_end').val();
	  }
	  var pass_data = encodeURIComponent(Base64M.encode(JSON.stringify(filter)));
	  location.href = 'index.php?act=Booking/search/'+area_types+'/'+$('.record_pageing').val()+'/'+pass_data;
	});
	
	
	//-- data change
	$('.data_trival').click(function(){
	  	
	  // get now record
	  var data_target = (!$('._target').length) ? $('.data_record:first') : $('._target');
	  if(!data_target.length){
		system_message_alert('','目前無任何資料');  
	    return false;
	  }
	  
	  // get next record
	  var data_toaccess = ( $(this).attr('id') == 'act_apply_prev' ) ? data_target.prev('.data_record'):data_target.next('.data_record');
	  if(!data_toaccess.length){
		system_message_alert('','沒有 '+$(this).text()+' 資料');   
	  }
	  
	  // change page num
	  if(!data_toaccess.is(':visible')){
		if($(this).attr('id') == 'act_apply_prev'){
		  $(".page_to[page='prev']").trigger('click');  	
		}else{
		  $(".page_to[page='next']").trigger('click');  	
		}
	  }
	  
	  // final
	  data_toaccess.find('._data_read').trigger('click');
	  
	});
	
    
    //-- admin book get data
	$(document).on('click','._data_read',function(){
	  
	  // initial	  
	  $('._target').removeClass('_target');
	  
	  // get value
	  var main_dom   = $(this).parents('.data_record');
	  var data_no    = main_dom.attr('no');
	  var dom_record = main_dom;
	  
	  // active ajax
	  if( ! data_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  initial_record_editer();
	  
	  if( data_no=='_addnew' ){
	    dom_record.addClass('_target');
		data_orl = {};
		
		$dom = dom_record.clone().removeClass('_data_read');
	    $('#record_selecter').find('.record_control').hide();
		$('#record_selecter').find('.record_list').children('.data_result').hide();
		$('#record_selecter').find('.record_list').children('.data_target').empty().append( $dom).show();
		$('#record_editor').find('a.view_switch').trigger('click');
		active_header_footprint_option('record_selecter','新增申請單','_return_list');
	  
	  }else{
	    
		$.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Booking/read/'+data_no},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  
			  dom_record.addClass('_target');
			  
			  
			  $('#apply_code').html(response.data.read.apply.apply_code);
			  $('#apply_date').html(response.data.read.apply.apply_date);
			  $('#applicant_name').html(response.data.read.apply.applicant_name);
			  $('#apply_checker').html(response.data.read.apply.apply_checker);
			  $('#check_note').val(response.data.read.apply.check_note);
			  
			  
			  $('#apply_review').attr('review',response.data.read.apply.apply_review);
			  
			  // insert apply license
			  $('#apply_license').html(response.data.license.PAGE_CONTENT);
			  
			  // insert data : applicant
			  insert_applicant_form(response.data.read.applicant);
			  
			  // insert data : progres 
			  insert_progress_table(response.data.read.stagenow,response.data.read.progress);
			  
			  // insert data : attachment 
			  insert_attachment_list(data_no,response.data.read.attachment);
			  
			  // insert data : attachment 
			  insert_apply_history(data_no,response.data.read.history);
			  
			  /*
			  var dataObj =  response.data.read.record;
			  data_orl = dataObj;
			  
			  */
			  
			  // set foot print 
			  active_header_footprint_option('record_selecter',data_no,'_return_list');
			  
			  
			  // hash the address
			  location.hash = data_no
			  
			  
			  // show editor 
			  $('#record_editor').show();
			  
			  
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	    }).done(function() { system_loading();   });
	  }
	
	});
    
	
	//-- 急件送審
	$('#act_apply_toreview').click(function(){
	  // get value
	  var data_no    = $('._target').attr('no');
	   
	  // active ajax
	  if( ! data_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
      
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Booking/setstage/'+data_no+'/3'},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  insert_progress_table(response.data.review.stagenow,response.data.review.progress);
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
	  
	});
	
	
	//-- 
	$('#act_apply_startmail').click(function(){
	  // get value
	  var data_no    = $('._target').attr('no');
	   
	  // active ajax
	  if( ! data_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  } 

      if(!confirm("確定要寄送信件?")){
		return false;	  
	  }
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Mailer/regist/'+data_no},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  system_message_alert('alert','信件已註冊，將於預定日期：'+response.data.mail.maildate+' 寄發。');
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
	});
	
	//-- 設定申請資料
	$('.act_submit_review').click(function(){
	  
	  // get value
	  var data_no    = $('._target').attr('no');
	   
	  // active ajax
	  if( ! data_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
      
	  var review_option = $(this).attr('mode');
	  var review = {};
	  
	  if(review_option=='apply_review'){
		if(!$('#apply_review_value').val()){
		  system_message_alert('',"審查狀態錯誤");
		  return false;  
	    }  
		review['status'] = $('#apply_review_value').val()
	    review['notes']  = $('#apply_review_notes').val()  
	  }else if(review_option=='apply_status'){
		review['status'] = $(this).val()
	    review['notes']  = '';
	  }else{
		system_message_alert('',"功能尚未定義");
		return false;  
	  }
	  
	  if(!$(this).parents('td').hasClass('nowstage')){
		system_message_alert('',"申請階段錯誤，無法使用此功能");
		return false;  
	  }
	  
	  
	  
	  if(!confirm("確定要設定申請狀態?")){
		$('.review_admin').val('')
		return false;	  
	  }
	  
	  var pass_data = encodeURIComponent(Base64M.encode(JSON.stringify(review)));
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Booking/review/'+data_no+'/'+pass_data},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  insert_progress_table(response.data.review.stagenow,response.data.review.progress);
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
      
	  $('.review_admin').val('')
	  
	});
	
	//-- save data modify
	$('#act_apply_save').click(function(){
	  
      // initial	  
	  var data_no    =  $('._target').length? $('._target').attr('no') : '';
	  var modify_data = {};
	  
	  var act_object  = $(this);
	  var checked = true;
	  
	  // option active checked  // 檢查按鈕是否在可執行狀態
	  if( act_object.prop('disabled') ){
	    return false;
	  }
	  
	  // check process data
	  if( !data_no.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  } 
	  
	  // get value
	  $('._update').each(function(){
		var field_name  = $(this).attr('id');
	    var field_value = $(this).val();
		if( data_orl[field_name] !== field_value){
		  modify_data[field_name]  =  field_value;
	    }
		  
		if( $(this).parent().prev().hasClass('_necessary') && field_value==''  ){  
		  $(this).focus();
		  system_message_alert('',"請填寫必要欄位 ( * 標示)");
		  checked = false;
		  return false;
		}
	  });
	  
	  if(!checked){
		return false;  
	  }
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(modify_data)));
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Booking/save/'+data_no+'/'+passer_data},
		beforeSend: function(){  active_loading(act_object,'initial'); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			$('._modify').removeClass('_modify');
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action );  });
	});
	
	
	//-- export apply ticket
	$('#act_apply_ticket').click(function(){
	  // initial	  
	  var data_no    =  $('._target').length? $('._target').attr('no') : '';  	
	  // check process data
	  if( !data_no.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  } 
      window.open('index.php?act=Booking/ticket/'+data_no);
		
	});
	
	
	
	//-- Input Meta Json To Edit Dom 
	function insert_applicant_form(DObj){
      
	  var dom_record  = $('._target');
	   
      $.each(DObj,function(DataSet,DataObj){
		  
        switch(DataSet){
		  
		  case 'info':
		  
            $('.applicant_meta').find('.data_col').hide();
			
			
			$.each(DataObj,function(field,meta){
			  // 匯入編輯欄位	
			  if(  $("._variable[id='"+field+"']").length ){
				var FieldDom = $("._variable[id='"+field+"']");  
				if( FieldDom.hasClass('_update')){
				  if(FieldDom.is(':radio') || FieldDom.is(':checkbox') ){
				    FieldDom.prop('checked',parseInt(meta));	  
				  }else{
					FieldDom.val(meta);	   
				  }
				  
				  if(FieldDom[0].tagName == 'TEXTAREA'){
					FieldDom.css('height','');
					if(FieldDom.val()){
					  var scrollheight = (FieldDom.prop("scrollHeight")-10);
					  var boxheight	 = FieldDom.height(); 
					  if(scrollheight > boxheight){
					    FieldDom.height(scrollheight+"px");
					  }	
					}
				  }
				}else{
				  FieldDom.html(meta)	
				}
			  }  
			  // update target record 
			  var record_field = dom_record.children("td[field='"+field+"']");
			  if( record_field.length && record_field.html() != meta  ){
				record_field.html(meta);
			  }
			  
			  $("._variable[id='"+field+"']").parents('.data_col').show();
			  
			});
		  
		    $('._modify').removeClass('_modify');
			break; 
		  
		  case 'history':
		    
			$.each(DataObj,function(i,adate){ 
			  var DOM = $("<div/>").addClass("data_value mutile_fields");
			  DOM.attr('ballot',adate['_ballot']);
			  DOM.append("<input type='text' class='_variable ' id='apply_date-into_date_"+adate['acno']+"' value='"+adate['into_date']+"'  readonly />");
			  DOM.append("<input type='text' class='_variable ' id='apply_date-exit_date_"+adate['acno']+"' value='"+adate['exit_date']+"'  readonly />");
			  DOM.appendTo("#booking_apply_dates");
		    });
			break;
		  
		  case 'attach':
            $.each(DataObj,function(i,attach){ 
			  var DOM = $("<li/>").addClass("attach");
			  //DOM.attr('ballot',adate['_ballot']);
			  DOM.append("<span>test</span>");
			  DOM.appendTo("#apply_attachemnts_list");
		    });
			break; 		  
		
		  case 'member':
		    // 載入名單
			$('#apply_member_list').empty();
			$.each(DataObj,function(i,member){
			  var record = $("<tr/>").addClass('record');  
			  record.append("<td>"+(i+1)+"</td>");
			  record.append("<td>"+member.name+"</td>");
			  record.append("<td>"+member.id+"</td>");
			  record.append("<td>"+member.birthday+"</td>");
			  record.append("<td>"+member.address+"</td>");
			  record.append("<td>"+member.phone+"</td>");
			  record.append("<td>"+member.urgent_name+"</td>");
			  record.append("<td>"+member.urgent_phone+"</td>");
			  record.appendTo($('#apply_member_list'));
			});
			
			
			break;
		  default:break;
		}                            		 

	  });
		
	}
	
	
	//-- Input Apply Progress  
	function insert_progress_table(Stage,Progres){
	  
	  // 使用者部分
	  $.each(Progres.client,function(pstage,plist){
		if(!$('tr.process_task#client').length){
		  return false;	
		}
		var log_dom = $('tr.process_task#client').find('td.stage'+pstage);
		log_dom.empty();
		$.each(plist,function(i,log){
          var record = $('<span/>').addClass('rvlog').attr('title',log.time+' '+log.note).html(log.time.substr(0,10).replace(/\-/g,'')+' '+log.status);
          record.prependTo(log_dom);  		  
		})
	  });
	  
	  // 管理者部分
	  $.each(Progres.review,function(pstage,plist){
		if(!$('tr.process_task#review').length){
		  return false;	
		}
		var log_dom = $('tr.process_task#review').find('td.stage'+pstage);
		if(log_dom.hasClass('_variable')) log_dom.empty();
		$.each(plist,function(i,log){
          var record = $('<span/>').addClass('rvlog').attr('title',log.time).html(log.time.substr(0,10).replace(/\-/g,'')+' '+log.status);
		  if(log.note.length){
			record.html(record.html()+'<center>'+log.note+'</center>');  
		  }
          record.prependTo(log_dom);  		  
		})
	  });
	  
	  // 陳核部分
	  if(typeof Progres.admin == 'undefined')  Progres.admin = {};
	  $.each(Progres.admin,function(pstage,plist){
		if(!$('tr.process_task#admin').length){
		  return false;	
		}
		var log_dom = $('tr.process_task#admin').find('td.stage'+pstage);
		if(log_dom.hasClass('_variable')) log_dom.empty();
		$.each(plist,function(i,log){
          var record = $('<span/>').addClass('rvlog').attr('title',log.time).html(log.time.substr(0,10).replace(/\-/g,'')+' '+log.status);
		  if(log.note.length){
			record.html(record.html()+'<br>'+log.note);  
		  }
          record.prependTo(log_dom);  		  
		})
	  });
	  
	  // mark stage now
	  $('td[class^="stage"]').removeClass('nowstage');
	  if(Stage <= 5){
		$('tr.process_task#review').show();   
		$('td.stage'+Stage).addClass('nowstage');  
	  }else{
		// 關閉控制
        $('tr.process_task#review').hide(); 
	  }
	 
	}
	
	
	//-- Input Apply Attachment  
	function insert_attachment_list(ApplyNo,Attachments){
	  
	  // 使用者部分
	  $.each(Attachments,function(i,attach){
		var record = $('<li/>').addClass('attach');
		record.append("<span class='upltime'>"+attach.time+"</span>")
		record.append("<span class='uplname'>"+attach.file+"</span>")
		record.append("<a class='option upllink' href='index.php?act=Booking/attach/"+ApplyNo+"/"+attach.code+"' title='下載檔案' target=_blank ><i class='fa fa-download' aria-hidden='true'></i></a>")
		record.appendTo($('#apply_attachment_record'));  		  
	  });
	}
	
	//-- Input Apply History  
	function insert_apply_history(ApplyNo,ApplyHistory){
	  
	  // 使用者部分
	  $.each(ApplyHistory,function(i,ahis){
		var record = $('<li/>').addClass('his');
		record.append("<span class='ahdate'>"+ahis.apply_date+"</span>")
		record.append("<span class='aharea'>"+ahis.area_name+"</span>")
		record.append("<span class='ahmbrc'>"+ahis.member_count+"</span>")
		record.append("<span class='ahfinal'>"+ahis._final+"</span>")
		record.appendTo($('#apply_history'));  		  
	  });
	}
	
	 
  
  });	
  
  