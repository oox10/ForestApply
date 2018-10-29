/* [ Admin Mailer Function Set ] */
	
    
	
  $(window).load(function () {   //  || $(document).ready(function() {		
	
	/*  Froala Sample 
	GET : $('#'+docId+'-content').froalaEditor('html.get')
    INSERT : froalaEditor('html.insert',valfill,true);
    check : $(this).data('froala.editor')
	*/
	
	
	//-- 設定分頁 資料超過上萬，分頁自訂
	$(document).off('click','.page_to');
	$(document).off('change','.record_view');

	$('.record_view').change(function(){
	  var link = location.search.replace('/#.*?$/','').split('/');
	  link[3] = $(this).val();
	  location.search = link.join('/');
	});

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
	
	
	//-- 搜尋資料
	$('#act_record_search').click(function(){
	  module_page_rebuilt(); 	
	});
	
	//-- data type selecter	
	$("input:radio[name='data_type']").click(function(){
	  if($(this).val()=='search'){
		$('#data_search_condition').focus();
		return true;  
	  }	
	  
	  if($(this).val()=='all'){
		$('#data_search_condition').val('')		
	  }
	  
	  module_page_rebuilt();
	});
	
	//-- submit condition
	function module_page_rebuilt(){
      
	  var parameter 	= {};
	  
	  // get data group  
	  var record_mode = $("input[name='data_type']:checked").val();
	  
	   
	  parameter['condition'] = $('#data_search_condition').val();	  
	  
	  // get data order
	  if($(".order_by[mode != '0']").length){
		parameter['orderby']   = {'field':$(".order_by[mode!='0']").attr('order'),'mode':$(".order_by[mode!='0']").attr('mode') , 'name':$(".order_by[mode!='0']").attr('name') };  
	  }
	  
	  // get page config
	  var pager = $('.record_view').val();
	  
	  var passer_data	= encodeURIComponent(Base64.encode(JSON.stringify(parameter)));
	  location.href='index.php?act=Mailer/index/'+record_mode+'/'+pager+'/'+passer_data;  
	}
	
	
	
	/* [editor tool setting] */
	var FroalaTool = [ 'bold', 'italic', 'underline', 'strikeThrough', 'fontFamily', 'fontSize', '|', 'color', 'paragraphStyle', '|', 'insertHR', 'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'insertLink', 'insertImage', 'insertTable','|', 'clearFormatting','html'];  //, 'paragraphFormat', 'quote' , 'undo', 'redo', 'selectAll','fullscreen', 'html' , '|', '-','insertFile'  //,'fullscreen' 有問題會變白
	
	if($('#mail_content').length){ 
	  $('#mail_content').froalaEditor({
		language: 'zh_tw',  
		iframe: true,
		//height: editer_height,
		toolbarButtons: FroalaTool
	  });
	  
	  $("a:contains('Unlicensed Froala Editor')").hide();
	}
	
	//-- datepicker initial
	$("#_mail_date").datepicker({
	    dateFormat: 'yy-mm-dd',
	    onClose: function(dateText, inst) { 
	      if(/\d{4}-\d{2}-\d{2}$/.test(dateText)){
		    $(this).val(dateText);
		  }
	    } 
	});
	
	
	//-- data record filter
	$("input[type='radio'][name='record_type']").click(function(){
	  if($(this).prop('checked')){
		var record_flag = $(this).val();
		location.href = 'index.php?act=Mailer/index/'+record_flag;
	  }	
	});
	
	
	//-- admin get record data
	$(document).on('click','._data_read',function(){
	  
      // initial	  
	  $('._target').removeClass('_target');
	  
	  // get value
	  var data_no    = $(this).attr('no');
	  var dom_record = $(this);
	  
	  // active ajax
	  if( ! data_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  initial_record_editer();
	  
	  if( data_no=='_addnew' ){
	    dom_record.addClass('_target');
		data_orl = {};
		alert('功能尚未建置');
		
		/*
		$dom = dom_record.clone().removeClass('_data_read');
	    $('#record_selecter').find('.record_control').hide();
		$('#record_selecter').find('.record_list').children('.data_result').hide();
		$('#record_selecter').find('.record_list').children('.data_target').empty().append( $dom).show();
		$('#record_editor').find('a.view_switch').trigger('click');
		active_header_footprint_option('record_selecter','新增信件','_return_list');
	    */
	  }else{
	    
		$.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Mailer/read/'+data_no},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  dom_record.addClass('_target');
			  
			  var dataObj =  response.data.record;
			  data_orl = dataObj;
			  
			  // change _data_read area
			  $dom = dom_record.clone().removeClass('_data_read');
			  $('#record_selecter').find('.record_control').hide();
			  $('#record_selecter').find('.record_list').children('.data_result').hide();
			  $('#record_selecter').find('.record_list').children('.data_target').empty().append( $dom).show();
			  $('#record_editor').find('a.view_switch').trigger('click');
			  
			  // insert data
			  insert_data_to_mailer_form(dataObj);
			  
			  // set foot print 
			  active_header_footprint_option('record_selecter',dataObj.mail_to,'_return_list');
			  
			  // hash the address
			  location.hash = dataObj.smno
			  
			  
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	    }).done(function() { system_loading();   });
	  }
	
	});
	
	 
	//-- save data modify
	$('#act_mail_save').click(function(){
	  
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
	    if($(this)[0].tagName=='INPUT' && $(this).attr('type')=='checkbox'){
		  var field_name = $(this).attr('name');
		  roles_data[$(this).val()] = $(this).prop('checked') ? 1 : 0;
		}else{
		  var field_name  = $(this).attr('id');
	      var field_value = $(this).val();
		  
		  if(field_name=='mail_content'){
			field_value =  $('#mail_content').froalaEditor('html.get');  
		  }
		  
		  if( data_orl[field_name] !== field_value){
		    modify_data[field_name]  =  field_value;
	      }
		  
		  if( $(this).parents('.data_value').prev().hasClass('_necessary') && field_value==''  ){  
			$(this).focus();
			system_message_alert('',"請填寫必要欄位 ( * 標示)");
		    checked = false;
		    return false;
		  }
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
	    data: {act:'Mailer/save/'+data_no+'/'+passer_data},
		beforeSend: function(){  active_loading(act_object,'initial'); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  
		  if(response.action){
			
			var dataObj = response.data.record;
			data_orl = dataObj;
			
			// insert data
			insert_data_to_mailer_form(dataObj);
			
			// update data no 
			if( data_no == '_addnew'){  $('._target').attr('no',dataObj.pno) }
		  
		  }else{
			system_message_alert('',response.info);
	      }
		  
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action );  });
	});
	
	
	//-- get data to editer  // 從server取得資料並放入編輯區
	function insert_data_to_mailer_form(dataObj){
	  var dom_record  = $('._target'); 
	  
	  $.each(dataObj,function(field,meta){
		if(  $("._variable[id='"+field+"']").length ){  
		  
		  var domtype = $("._variable[id='"+field+"']").prop("tagName");
		  
	      if(domtype == 'SPAN' || domtype == 'div' ){
			$("._variable[id='"+field+"']").html(meta);    
		  }else if(domtype == 'UL' ){
			$("._variable[id='"+field+"']").empty();
			$.each(meta,function(t,l){
			  var log = $('<li/>');
			  log.append("<span class='time'>"+t+"</span>");
			  log.append("<span class='info'>"+l+"</span>");
			  log.prependTo("._variable[id='"+field+"']");	
			});

		  }else if(field=='mail_content'){
			$('#mail_content').froalaEditor('html.set',meta,true);
		  }else{
			$("._variable[id='"+field+"']").val(meta);  	
		  }
		}
		
		// update target record 
		var record_field = dom_record.children("td[field='"+field+"']");
		if( record_field.length && record_field.html() != meta  ){
		  record_field.html(meta);
	    }
		
		// update status
		if(field=='_status_code'){
		  dom_record.find('td.mail_status').attr('mstatus',meta);
		}
		
	  });
	  
	  $('._modify').removeClass('_modify');
	}
	
	
	//-- iterm function execute
	$('#act_func_execute').click(function(){
	  
	  var data_no     =  $('._target').length? $('._target').attr('no') : '';
	  var execute_func =  $('#execute_function_selecter').length ? $('#execute_function_selecter').val() : '';
	  
	  // check process target
	  if( !data_no.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  }  
	  
	  // check process action
	  if( !execute_func.length ){
	    system_message_alert('',"尚未選擇執行功能");
	    return false;
	  } 

      // check process action
	  if( data_no=='_addnew' ){
	    system_message_alert('',"資料尚在編輯中，請先儲存資料");
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
	    data: {act:'Mailer/'+execute_func+'/'+data_no},
		beforeSend: function(){  system_loading(); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			switch(execute_func){
			  case 'dele' : act_record_dele_after(response.data);break;
			  case 'sent' : 
			    insert_data_to_mailer_form(response.data.record);
				system_message_alert('alert',"已發送信件 TO："+response.data);
			    break; 
			  default: break; 
			}
		  }else{
			system_message_alert('',response.info);
	      }
		  
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading(); });
	  $('#execute_function_selecter').val('');
	});
	
	// 執行資料刪除後的動作
	function act_record_dele_after(DataNo){
      $("tr._target[no='"+DataNo+"']").remove();
	  $('#editor_reform').trigger('click');
	  $('.record_view').trigger('change');
	}
	
	// initial account data  //帶有參數的網址連結資料
    if(document.location.hash.match(/^#.+/)){
	    $target = $("tr.data_record[no='"+location.hash.replace(/^#/,'')+"']");
        if($target.length){ 
		  if( !$target.hasClass( '_target' )){
			$target.trigger('click');		
	      }
	    }else{
		  system_message_alert('','查無資料');
	    }
	}
	
	
	
  });	
  
  
  