/* [ Admin Area Function Set ] */
	
  $(window).load(function () {   //  || $(document).ready(function() {		
	
	
	// time picker
	if($('input[type="time"]').length){
	  $('input[type="time"]').timepicker({ 'timeFormat': 'H:i:s' });	
	}
	
	//-- data record filter
	$("input[type='radio'][name='area_type']").click(function(){
	  if($(this).prop('checked')){
		var record_flag = $(this).val();
		$("tr.data_record").addClass('hide');
		$("tr.data_record[filter='"+record_flag+"']").removeClass('hide');
		$('.record_view').trigger('change');
	  }	
	});
	
	
	//-- get area data
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
	  
	  clean_module_relate_block();
	  
	  if( data_no=='_addnew' ){
	    dom_record.addClass('_target');
		data_orl = {};
		
		// 版面調整
		$dom = dom_record.clone().removeClass('_data_read');
	    $('#record_selecter').find('.record_control').hide();
		$('#record_selecter').find('.record_list').children('.data_result').hide();
		$('#record_selecter').find('.record_list').children('.data_target').empty().append( $dom).show();
		$('#record_editor').find('a.view_switch').trigger('click');
		active_header_footprint_option('record_selecter','新增區域','_return_list');
	    
	  }else{
	    
		$.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Area/read/'+data_no},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  dom_record.addClass('_target');
			  
			  var dataObj =  response.data.area;
			  data_orl = dataObj;
			  
			  // change _data_read area
			  $dom = dom_record.clone().removeClass('_data_read');
			  $('#record_selecter').find('.record_control').hide();
			  $('#record_selecter').find('.record_list').children('.data_result').hide();
			  $('#record_selecter').find('.record_list').children('.data_target').empty().append( $dom).show();
			  $('#record_editor').find('a.view_switch').trigger('click');
			  
			  
			  // insert data
			  insert_data_to_area_form(dataObj);
			  insert_data_to_rela_form(dataObj);
			  insert_data_to_config_form(dataObj.area_forms);
			  
			  // set foot print 
			  active_header_footprint_option('record_selecter',dataObj.area_name,'_return_list');
			  
			  // hash the address
			  location.hash = dataObj.area_code
			  
			  //initMap();
			  
			  
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	    }).done(function() { system_loading();   });
	  }
	
	});
	
	 
	//-- save data modify
	$('#act_area_save').click(function(){
	  
      // initial	  
	  var data_no    =  $('._target').length? $('._target').attr('no') : '';
	  var modify_data = {};
	  var blocks_data = {};
	  
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
		}else{
		  var field_name  = $(this).attr('id');
	      var field_value = $(this).val();
		  
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
	  
	  // get block name
	  $(".blocksel[area='"+data_no+"']").each(function(){
	    var block_id   = $(this).attr('no');
		var block_meta = {};
		if(!$('#'+block_id).length){
		  return true;
		}
		$('#'+block_id).find('._variable').each(function(){
		  block_meta[$(this).attr('meta')] = $(this).val();		
		});
		
		$(this).find('.bname').html(block_meta['block_name']); // 更新 tag name
		
		blocks_data[block_id] = block_meta;
	  });
	  
	  if(!checked){
		return false;  
	  }
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(modify_data)));
	  var passer_block = encodeURIComponent(Base64M.encode(JSON.stringify(blocks_data)));

	 
      // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Area/save/'+data_no+'/'+passer_data+'/'+passer_block},
		beforeSend: function(){  active_loading(act_object,'initial'); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  
		  if(response.action){
			
			var dataObj = response.data.area;
			data_orl = dataObj;
			
			// insert data
		    insert_data_to_area_form(dataObj);
			
			// update data no 
			if( data_no == '_addnew'){  $('._target').attr('no',dataObj.area_code) }
		  
		  }else{
			system_message_alert('',response.info);
	      }
		  
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action );  });
	});
	

	//-- get data to editer  // 從server取得資料並放入編輯區
	function insert_data_to_area_form(dataObj){
	  var dom_record  = $('._target'); 
	   
	  $.each(dataObj,function(field,meta){
		
		if(field=='owner_list'){    
		   // 所屬群組		   
		   $.each(meta,function(gid,gname){
			var DOM = $("<li/>").html(gname);
			DOM.appendTo("#rela_groups_list");
		  });  
		}else{
		  //  其他欄位
          if(  $("._variable[id='"+field+"']").length ){  
			if(field=='post_content'){
			  $('#post_content').froalaEditor('html.set',meta,true);
		    }else{
			  $("._variable[id='"+field+"']").val(meta);  	
			}
		  }
		}
		
		// update target record 
		var record_field = dom_record.children("td[field='"+field+"']");
		if( record_field.length && record_field.html() != meta  ){
		  record_field.html(meta);
	    }
	  });
	  
	  $('._modify').removeClass('_modify');
	}
	
	//-- renew data rela data  // 禁申時間  區塊  關聯資料
	function insert_data_to_rela_form(dataObj){
	  var dom_record  = $('._target'); 
	   
	  $.each(dataObj,function(field,meta){
		
		if(field=='stop_dates' && meta){  // 禁申區
		  
		  // stop date list	
		  $.each(meta,function(i,sdate){ 
			var DOM = $("li.stop_day._template").clone().removeClass("_template _default");
			DOM.attr('no',sdate['asno']);
			DOM.attr('valid',sdate['@data_valid']);
			DOM.find("input[name='date_start']").val(sdate['date_start']).prop('disabled',!parseInt(sdate['@data_valid']) ? true:false);
			DOM.find("input[name='date_end']").val(sdate['date_end']).prop('disabled',!parseInt(sdate['@data_valid']) ? true:false);
			DOM.find("input[name='reason']").val(sdate['reason']).prop('disabled',!parseInt(sdate['@data_valid']) ? true:false);
			DOM.find("input[name='_active']").prop("checked",parseInt(sdate['_active'])).prop('disabled',!parseInt(sdate['@data_valid']) ? true:false);
			$.each(dataObj['area_block'],function(i,block){ 
			   $('<option/>').val(block.ab_id).html(block.block_name).appendTo(DOM.find(".effect_block"));
			});
			DOM.find(".effect_block").val(sdate['effect']).prop('disabled',!parseInt(sdate['@data_valid']) ? true:false);
		    DOM.appendTo("#stop_dates_list");
		  });
		
        }else if(field=='area_refer' && meta){  // 禁申區
		  
		  // refer date list	
		  $.each(meta,function(type,rset){ 
			switch(type){
			  case 'image':
                $.each(rset,function(imgindex,imgdata){
				  var refer = $("<li/>").attr('no','image/'+imgindex); 
				  refer.append("<div class='refer_element'><img src='photo.php?src="+dom_record.attr('no')+'/'+imgindex+"' /></div>");
				  refer.append("<div class='refer_delete'><span class='option'><i class='fa fa-times' aria-hidden='true'></i></span></div>");	
				  refer.appendTo(".area_refer_container");
				}); 
				break;			  
			  
			  case 'gmaps':
                break;			  
			}
		  });
		  
		}else if(field=='area_block' && meta){  // 子區塊
		  $.each(meta,function(i,block){ 
			var block_tag = $('<li/>');
			block_tag.addClass('blocksel').attr({'no':block.ab_id,'area':dom_record.attr('no')}).html( '<span class="option act_remove_block" title="刪除"><i class="fa fa-times" aria-hidden="true"></i></span> <span class="bname" >'+block.block_name+'</span>');
			var new_block = $('#area_block_template').clone();
			new_block.attr('id',block.ab_id);
			new_block.find("input[meta='block_name']").val(block.block_name);
			new_block.find("textarea[meta='block_descrip']").val(block.block_descrip);
			new_block.find("input[meta='block_gates']").val(block.block_gates);
			new_block.find("input[meta='area_load']").val(block.area_load);
			new_block.find("input[meta='accept_max_day']").val(block.accept_max_day);
			new_block.find("input[meta='accept_min_day']").val(block.accept_min_day );
			new_block.find("input[meta='wait_list']").val(block.wait_list );
			$('#area_blocks').append(new_block);
			block_tag.appendTo( '.block_switch' ); 
		  });
		}
		
		// update target record 
		var record_field = dom_record.children("td[field='"+field+"']");
		if( record_field.length && record_field.html() != meta  ){
		  record_field.html(meta);
	    }
	  });
	  
	  $('._modify').removeClass('_modify');
	}
	
	
	//-- get data to editer  // 從server取得資料並放入編輯區
	function insert_data_to_config_form(dataObj){
	  var dom_record  = $('._target'); 
	  var field_count = 1;
	  $.each(dataObj,function(field,meta){
		
		if(field == 'application_reason'){
          var form_dom = $('#application_reason');		  
          form_dom.find("input[name='input_type'][value='"+meta.config.input+"']").prop('checked',true);
		  
		  $.each(meta.elements,function(i,conf){
			var option = $('li#sample_term').clone().attr('id','');  
			option.find("input[name='option_name']").val(conf.name);  
			option.find("input[name='option_note']").val(conf.note);  
			$.each( conf.conf.split(';') , function(i,set){
			  option.find("input[name='option_set'][value='"+set+"']").prop('checked',true);
              option.find("input[name='option_test'][bind='"+set+"']").prop('readonly',false); 
			});
			
			// 20181024 增加選項的檢測條件    test:{'attach':1}
			if(conf.test){
			  $.each( conf.test, function(c,v){
			    option.find("input[name='option_test'][bind='"+c+"']").val(v);		
			  });	
			}
			option.appendTo('.field_options');
		  });
		  
		}else{
		  var form_dom = $('#sample_form_editor').clone().attr('id',field); 	
          form_dom.find("input[name='input_type_']").attr('name','input_type_'+field);
		  form_dom.find("input[name='input_type_"+field+"'][value='"+meta.config.input+"']").prop('checked',true);
          form_dom.find("input[name='field_class']").val(meta.config.class);
		  form_dom.find("input[name='field_label']").val(meta.config.label);
		  form_dom.find("input[name='field_value']").val(meta.config.value);
		  form_dom.find("textarea[name='field_notes']").val(meta.config.notes);
		  form_dom.find(".feno").text(field_count++);
		  form_dom.appendTo('.apply_form');
		}
		
		
	  });
	  
	  //$('._modify').removeClass('_modify');
	}
	
	
	
	//-- create new area data
	$('#act_area_new').click(function(){
	    
	  // initial page
	  $('#editor_reform').trigger('click');
	  
	  // create new record
	  $tr = $("<tr/>").addClass('data_record _data_read').attr('no','_addnew');
	  $tr.append(" <td field='no'  > - </td>");
	  $tr.append(" <td field='area_type'  ></td>");
	  $tr.append(" <td field='area_name'  > </td>");
	  $tr.append(" <td field='area_load'  ></td>");
	  $tr.append(" <td ><i class='mark24 pic_area_display_0' title='預設關閉'></i></td>");
	 
	  // inseart to record table	
	  if(!$("tr.data_record[no='_addnew']").length){
	    $tr.prependTo('tbody.data_result').trigger('click');
	  }	
	});
	
	
	//-- create new block
	$('#act_new_block').click(function(){
      
	  var block_tag = $('<li/>');
	  var block_num = $('li.blocksel').length;
	  var data_no   =  $('._target').length? $('._target').attr('no') : '';
	  
	  // active ajax
	  if( ! data_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  // active ajax
      $.ajax({
	    url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Area/addblock/'+data_no },
	    beforeSend: function(){  system_loading() },
	    error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
	      if(response.action){
			block_tag.addClass('blocksel').attr({'no':response.data,'area':data_no}).html( '<span class="option act_remove_block" title="刪除"><i class="fa fa-times" aria-hidden="true"></i></span> <span class="bname" >新增子區 '+block_num+'</span>');
	  
			var new_block = $('#area_block_template').clone();
			new_block.attr('id',response.data);
			new_block.find("input[meta='block_name']").val('新增子區'+block_num);
			new_block.find("input[meta='area_load']").val($('#area_load').val() );
			new_block.find("input[meta='accept_max_day']").val($('#accept_max_day').val() );
			new_block.find("input[meta='accept_min_day']").val($('#accept_min_day').val() );
			new_block.find("input[meta='wait_list']").val($('#wait_list').val() );
			$('#area_blocks').append(new_block);
			block_tag.appendTo( '.block_switch' ).trigger('click');  
	      }else{
			system_message_alert('',response.info);
		  }
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading()  });
	  
	  
	});
	
	//-- delete block
	$(document).on('click','.act_remove_block',function(){
      
	  var block_tag = $(this).parent('li.blocksel');
	  var block_id  = block_tag.attr('no');  
	  
	  // active ajax
	  if( ! block_id ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  // active ajax
      $.ajax({
	    url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Area/delblock/'+block_id },
	    beforeSend: function(){  system_loading() },
	    error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
	      if(response.action){
			if( !$('.area_block_config#'+block_id).length ){
		      $('.area_block_config#'+block_id).empty().remove();
	        }
	        block_tag.empty().remove();
	        $('li.blocksel:nth-child(1)').trigger('click');
	      }else{
			system_message_alert('',response.info);
		  }
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading()  });
	});
	
	
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
	    data: {act:'Area/'+execute_func+'/'+data_no},
		beforeSend: function(){  system_loading(); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			switch(execute_func){
			  case 'dele' : act_record_dele_after(response.data);break;
			  case 'show' : act_record_show_after(response.data);break; 
			  case 'mask' : act_record_mask_after(response.data);break;
			    
				break; 
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
	  system_message_alert('alert',"資料已刪除");
	}
	
	// 執行資料顯示後的動作
	function act_record_show_after(DataNo){
      $("tr._target[no='"+DataNo+"']").attr('status','').find('i.pic_area_display_0').toggleClass("pic_area_display_0 pic_area_display_1");
	}
	
	// 執行資料遮蔽後的動作
	function act_record_mask_after(DataNo){
       $("tr._target[no='"+DataNo+"']").attr('status','mask').find('i.pic_area_display_1').toggleClass("pic_area_display_1 pic_area_display_0");
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
	
	
	//-- 設定分頁
	$('.record_view').val(10).trigger('change');
	
	
	
	//**--  模組關聯函數  -- **//
	
	function clean_module_relate_block(){
	  $("#stop_dates_list").find("li:not(._default)").remove();
      $("#rela_groups_list").find("li:not(._default)").remove();
	  $('.area_refer_container').empty();
	  $(".blocksel[no!='main']").each(function(){
		var blockid = $(this).attr('no')  
		$('#'+blockid).remove();  
	    $(this).remove();
	  })
	  
	  $('table#sample_form_editor').nextAll().remove();
	  $('li#sample_term').nextAll().remove();
	  
	  
	}
	
	//-- [Module] : Bind Date Selecter On Dynamic Field
	$('body').on('focus',"input.date_seleter", function(){
      $(this).datepicker({
		dateFormat: 'yy-mm-dd',
	    onClose: function(dateText, inst) { 
	      //if(/\d{4}-\d{2}-\d{2}$/.test(dateText)){
		  //  $(this).val(dateText+' 00:00:01');
		  //}
	    }
	  });
    });
	
	//-- [Module] : Add Stop Date
	$('#act_new_stop_date').click(function(){
	  
	  var data_no    =  $('._target').length? $('._target').attr('no') : '';
	  if( data_no=='_addnew' ){
	    system_message_alert('',"請先儲存區域資料後再新增禁止申請日期!!");
	    return false;
	  }
	  
	  if($("li.stop_day[no='0']").length){
		system_message_alert('',"一次僅能新增一項，請先將新增資料儲存");
        return false;
	  }
	  
	  var blocks = {};
	  $('.blocksel').each(function(){
		if($(this).attr('no') != 'main')  blocks[$(this).attr('no')] = $(this).find('.bname').text(); 
	  });
	  
	  var DOM = $("li.stop_day._template").clone().removeClass("_template _default");
		  DOM.attr('no','0');
		  DOM.attr('valid',1);
		  DOM.find("input[name='date_start']").val('');
		  DOM.find("input[name='date_end']").val('');
		  DOM.find("input[name='reason']").val('');
		  DOM.find("input[name='_active']").prop(true);
		  $.each(blocks,function(bid,bname){ 
			$('<option/>').val(bid).html(bname).appendTo(DOM.find(".effect_block"));
	      });
		  DOM.insertAfter("li.stop_day._template");
	});
	
	
	//-- [Module] : Save Save Dates
	$(document).on('click','.act_save_stop_date',function(){
	  
      var data_no    =  $('._target').length? $('._target').attr('no') : '';
	  var target_dom = $(this).parents('.stop_day');
	  var data_meta  = {};
	  
	  
	  target_dom.removeClass('_fail');  
	  
	  // check process data
	  if( !data_no.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  }
     
      if( data_no=='_addnew' ){
	    system_message_alert('',"請先儲存區域資料後再新增禁止申請日期!!");
	    return false;
	  }
	  
	  data_meta['no'] = target_dom.attr('no');
	  
	  // get & check value
	  if(target_dom.find("input[name='date_start']").val()==''){
		target_dom.find("input[name='date_start']").focus();
		system_message_alert("","請設定起始日期");   
	    return false;
	  }
	  data_meta['date_start']  = target_dom.find("input[name='date_start']").val();
	  
	  if(target_dom.find("input[name='date_end']").val()==''){
		target_dom.find("input[name='date_end']").focus();
		system_message_alert("","請設定結束日期");   
	    return false;
	  }
	  data_meta['date_end']  = target_dom.find("input[name='date_end']").val();
	  
	  if(target_dom.find("input[name='reason']").val()==''){
		target_dom.find("input[name='reason']").focus();
		system_message_alert("","請填寫禁申理由");   
	    return false;
	  }
	  data_meta['reason']  = target_dom.find("input[name='reason']").val();
	  data_meta['effect']  = target_dom.find("select.effect_block").val();
	  data_meta['_active'] = target_dom.find("input[name='_active']").prop('checked') ? 1 : 0;
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64.encode(JSON.stringify(data_meta)));
      
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Area/stop_save/'+data_no+'/'+passer_data},
		beforeSend: function(){  system_loading() },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			target_dom.attr('no',response.data);
			target_dom.removeClass('_fail');
			system_message_alert('alert',"設定成功");
		  }else{
			target_dom.addClass('_fail');  
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading()  });
	  
	});
	
	
	
	//-- [Module] : Delete Stop Date
	$(document).on('click','.act_dele_stop_date',function(){
	 
      var data_no    =  $('._target').length? $('._target').attr('no') : '';
	  var target_dom = $(this).parents('.stop_day');
	   
	  // check process data
	  if( !data_no.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  }
     
	  if(!confirm("確定要刪除新增的日期 ?")){
        return false;  
	  } 
	 
	  if(!parseInt(target_dom.attr('no'))){
		target_dom.remove();  
	    return false;
	  }
	  
	  var sdate_no = target_dom.attr('no');
	 
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Area/stop_dele/'+data_no+'/'+sdate_no},
		beforeSend: function(){  system_loading() },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			target_dom.remove();
			system_message_alert('alert',"刪除成功");
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading()  });
		
	});
	
	
	//-- [Module] : Process Stop Date  // 停止並通知目前申請單
	$(document).on('click','.act_process_stop_date',function(){
	 
      var data_no    =  $('._target').length? $('._target').attr('no') : '';
	  var target_dom = $(this).parents('.stop_day');
	   
	  // check process data
	  if( !data_no.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  }
	  
	  // check date is save
	  if(!parseInt(target_dom.attr('no'))){
		system_message_alert('',"資料尚未儲存");  
	    return false;
	  }
	  
	  // check date is active
	  if(!target_dom.find("input[name='_active']").prop('checked')){
		system_message_alert('',"停止日期尚未啟用");  
	    return false;
	  }
	  
	  if(!confirm("確定要取消所有禁止申請日期範圍的申請單 ?")){
        return false;  
	  } 
	 
	  var sdate_no = target_dom.attr('no');
	 
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Area/stop_active/'+data_no+'/'+sdate_no},
		beforeSend: function(){  system_loading() },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			system_message_alert('alert',"已成功取消 "+ response.data+" 筆申請單");
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading()  });
		
	});
	
	
	
	
	
	
	//-- [Module] : area block switch
	$(document).on('click','.blocksel',function(){
	  var block_id = $(this).attr('no')	
	  
      if($(this).hasClass('selected')){
		return false;  
	  }
	  
	  if(!$('#'+block_id).length){
		system_message_alert('','子區塊設定不存在');
        return false;
	  }
	  
	  $('.area_block_config').attr('view',0);
	  $('#'+block_id).attr('view',1);
	  
	  $('.selected').removeClass('selected');
	  $(this).addClass('selected');
	  
	});
	
	
	//-- [Module] : area image refer add
	$('#act_add_picture').click(function(){
      var refer = $("<li/>").attr('no','new');
	  var data_no    =  $('._target').length? $('._target').attr('no') : '';
	  
	  // active ajax
	  if( ! data_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  refer.append("<form method='post' id='area_refer_upload_form' action='index.php?act=Area/addimg/"+data_no+"' enctype='multipart/form-data' target='upload_target' ><input type='file' name='file' /><button type='button' class='active act_upload_refer_image' title='上傳' ><i class='fa fa-upload' aria-hidden='true'></i></button></form>");
	  refer.append("<div class='refer_element'></div>");
	  refer.append("<div class='refer_delete'><span class='option'><i class='fa fa-times' aria-hidden='true'></i></span></div>");
	  $('.area_refer_container').prepend(refer);
	  
	});
	
	
	//-- [Module] : area image refer upload
	$(document).on('click','.act_upload_refer_image',function(){
	  
	  var uploader = $(this).prev();
	  var file_upload = uploader.val();
	  var file_name   = file_upload.split('\\').pop();
	  if(!file_upload){
		return false;		
	  }
	  
	  if( /\.(jpg|png)?$/.test(file_name)===false ){
		system_message_alert('','檔案類型錯誤，請上傳正確圖片資料');  
		uploader.val('');
	    return false;	
	  }
	  
	  var FormObj = $('#area_refer_upload_form'); 
	  FormObj.submit();
	  uploader.val('');
	  
	});
	
	//-- [Module] : area image refer upload
	$(document).on('click','.refer_delete',function(){
	  
	  var data_no    =  $('._target').length? $('._target').attr('no') : '';
	  // active ajax
	  if( ! data_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  var container = $(this).parents('li');
	  
	  if( container.attr('no') == 'new' ){
		container.empty().remove();  
	  }else{
		
		if(!confirm("確定要刪除參考資料?")){
		  return false;	
		}
		
		// active ajax
        $.ajax({
	      url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Area/delrefer/'+data_no+'/'+container.attr('no') },
	      beforeSend: function(){  system_loading() },
	      error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 	function(response) {
	        if(response.action){
			  container.empty().remove();  
			  system_message_alert('alert',"刪除成功");
			}else{
			  system_message_alert('',response.info);
			}
	      },
		  complete:	function(){  }
        }).done(function(r) {  system_loading()  });
	  }
	});
	
	
	/*== [ Module - APPLY FORM EDIT ] ==*/
	
	//-- Add New Apply Reason
	$('.act_add_reason').click(function(){
      var new_reason_term = $('li#sample_term').clone(); 	  
	  new_reason_term.attr('id','').appendTo("ol.field_options");  
	});
	
	//-- Del Apply Reason
	$(document).on('click','.act_del_reason',function(){
      $(this).parents('li.sel_term').empty().remove();
	});
	
	//-- switch apply reason option_test
	$(document).on('click','input[name="option_set"]',function(){
	  var test_dom = $(this).next('input[name="option_test"][bind="'+$(this).val()+'"]');
	  if(test_dom.length){
		var option_switch = $(this).prop('checked') ? false:true;
		test_dom.prop('readonly',option_switch);
		if(!option_switch && !test_dom.val()){
		  const defaultval = test_dom.attr('min');
		  test_dom.val(defaultval);
		}else{
		  test_dom.val('');	
		}
	  }
	});
	
	
	//-- Save Apply Reason
	$(document).on('click','.act_save_apply_form',function(){
	 
      var data_no    =  $('._target').length? $('._target').attr('no') : '';
	  var target_dom = $(this);
	  var form_config = {};  
	  
	  // check process data
	  if( !data_no.length ){
	    system_message_alert('',"尚未選擇資料");
		return false;
	  }
	  
	  // check process action
	  if( data_no=='_addnew' ){
	    system_message_alert('',"資料尚在編輯中，請先儲存資料");
		return false;
	  }	   
	  
	  
	  
	  
	  $(".field_config[id!='sample_form_editor']").each(function(){
		  
		var config_dom = $(this); 
		  
		if(config_dom.attr('id')=='application_reason'){
		  
		  // get reason config
		  var field_id = config_dom.attr('id');
		  form_config[field_id] = {'config':{},'elements':[]};
		  form_config[field_id]['config']['input'] = config_dom.find("input[name='input_type']:checked").val();
		  var option_dom = config_dom.find('.field_options');
		  option_dom.children(".sel_term[id!='sample_term']").each(function(i){
			var option_config = {};  		
			option_config['name'] = $(this).find("input[name='option_name']").val();
			option_config['conf'] = $(this).find("input[name='option_set']:checked").map(function(){return $(this).val(); }).get().join(';');
			option_config['note'] = $(this).find("input[name='option_note']").val();
			option_config['test'] = {};
			$(this).find("input[name='option_test']").each(function(){
			  if($(this).prop('readonly')) return true;
			  option_config['test'][$(this).attr('bind')]= $(this).val();
			});
			form_config[field_id]['elements'][i] = option_config;
		  });
		  
		}else{  
		  // get form config
		  var field_id = config_dom.attr('id');
		  form_config[field_id] = {'config':{}};
		  form_config[field_id]['config']['input'] = config_dom.find("input[name ^='input_type_']:checked").val();
		  form_config[field_id]['config']['class'] = config_dom.find("input[name='field_class']").val();
		  form_config[field_id]['config']['label'] = config_dom.find("input[name='field_label']").val();
		  form_config[field_id]['config']['value'] = config_dom.find("input[name='field_value']").val();
		  form_config[field_id]['config']['notes'] = config_dom.find("textarea[name='field_notes']").val();
		}
		
	  }); 
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(form_config))); 
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Area/formconfig/'+data_no+'/'+passer_data},
		beforeSend: function(){  system_loading() },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			system_message_alert('alert','設定已更新');
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading()  });
	    
	});
	
	
	//-- Add New Apply Form Field
	$('.act_add_form_field').click(function(){
      var new_field_term = $('table#sample_form_editor').clone(); 	 
      var field_count = $("table.field_config[id!='sample_form_editor']").length;	 
	  new_field_term.find("input[name='input_type_']").attr('name','input_type_application_field_'+field_count);	  
	  new_field_term.attr('id','application_field_'+(field_count+1)).find('.feno').text(field_count+1).end().appendTo(".apply_form");  
	  new_field_term.find('input[name="field_class"]').focus();
	});
	
	
	
	//-- Del Apply Form Field
	$(document).on('click','.act_del_form_field',function(){
		  
	  // confirm to admin
	  if(!confirm("確定要刪除申請欄位?")){
	    return false;  
	  }
		
      $(this).parents('table.field_config').empty().remove();
	  $('#form_config_main_save').trigger('click');
	});
	
	
	
	
	
	
  });	
  
  
  
  
  
  
  
  
  /*== [ Module - BLOCK EDIT ] ==*/
  
  //--UPLOAD REFER IMAGE AND DISPLAY
  function process_referupload(ProcessString){
    var response = JSON.parse(ProcessString)
	if(response.action){ 
	  var target_dom = $("li[no='new']");
	  target_dom.find('.refer_element').append("<img src='photo.php?src="+response.data.upload.path+"' >");
	  target_dom.find('form').empty();
	  target_dom.attr('no','image/'+response.data.upload.name);
	}else{
	  system_message_alert('',response.info)
	}
	$('#apply_member_upload').val('');	
  }
  
  
  var map;
  function initMap() {
	// Create the map with no initial style specified.
	// It therefore has default styling.
	map = new google.maps.Map(document.getElementById('gmap'), {
	  center: {lat: 23.598911, lng: 121.017341},
	  zoom: 8,
	  mapTypeControl: true,
	  disableDefaultUI: false,
	  scrollwheel: false,
	  streetViewControl: true,
	  
	  mapTypeId: google.maps.MapTypeId.TERRAIN 
	});
	
	// Add a style-selector control to the map.
	var styleControl = document.getElementById('style-selector-control');
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(styleControl);

	// Set the map's style to the initial value of the selector.
	var styleSelector = document.getElementById('style-selector');
	map.setOptions({styles: styles.styleArray});
	
	// 繪製
	var drawingManager = new google.maps.drawing.DrawingManager({
		drawingMode: google.maps.drawing.OverlayType.MARKER,
		drawingControl: true,
		drawingControlOptions: {
		  position: google.maps.ControlPosition.TOP_CENTER,
		  drawingModes: [
			google.maps.drawing.OverlayType.MARKER,
			//google.maps.drawing.OverlayType.CIRCLE,
			google.maps.drawing.OverlayType.POLYGON,
			google.maps.drawing.OverlayType.POLYLINE,
			//google.maps.drawing.OverlayType.RECTANGLE
		  ]
		},
		polylineOptions: { editable: true,},
		polygonOptions: { editable: true,}
		
	});
	drawingManager.setMap(map);
	
	/*
	
	var latlngbounds = new google.maps.LatLngBounds();
	var geocoder = new google.maps.Geocoder();
	var i=0
	
	var promises = [];
	$(".filter[name='place']").each(function(){
	  if( i++ > 10 ){ return false; }
	  if( $(this).val() == '不詳' ){ return true; }
	   
	  var location = $(this).val();
	  
	  // 地名轉換座標並標記
	  geocoder.geocode({'address': '臺灣,'+location}, function(results, status) {
		if (status === google.maps.GeocoderStatus.OK) {
		  
		  //resultsMap.setCenter(results[0].geometry.location);
		  
		  var image = $(".filter[name='place'][value='"+location+"']").prop('checked') ? 'theme/image/mark_place_at.png':'theme/image/mark_place.png';
		  
		  var marker = new google.maps.Marker({
			map: map,
			position: results[0].geometry.location,
			title: location,
			icon: image
		  });
		  marker.addListener('click', function() {
			$(".filter[name='place'][value='"+marker.title+"']").next().trigger('click');
		  });
		  
		  latlngbounds.extend(marker.getPosition());
		  map.fitBounds(latlngbounds);
		  //map.setCenter(latlngbounds.getCenter());
		} else {
		  //alert('Geocode was not successful for the following reason: ' + status);
		}
	  });
	 
	}).promise().done( function(){ 
	  // 確保each 做完  但是因為 geocoder.geocode = ajax 所以each finish 還是沒用
	});
	*/
	
	google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
	  if (event.type == google.maps.drawing.OverlayType.MARKER) {
		var radius = event.overlay.getPosition();
		radius.lat()
		radius.lng()
	  }else if (event.type == google.maps.drawing.OverlayType.POLYGON) {
       var radius = event.overlay.getPaths();
	   console.log(radius)
      }
	  
	  
	  
	});
	
  }
  
  
  var styles = {
	default: null,
	styleArray:[{featureType: "all",stylers: [{ saturation: -80 }]},{featureType: "road.arterial",elementType: "geometry",stylers: [{ hue: "#00ffee" },{ saturation: 50 }]},{featureType: "poi.business",elementType: "labels",stylers: [{ visibility: "off" }]}],retro: [{elementType: 'geometry', stylers: [{color: '#ebe3cd'}]},{elementType: 'labels.text.fill', stylers: [{color: '#523735'}]},{elementType: 'labels.text.stroke', stylers: [{color: '#f5f1e6'}]},{featureType: 'administrative',elementType: 'geometry.stroke',stylers: [{color: '#c9b2a6'}]},{featureType: 'administrative.land_parcel',elementType: 'geometry.stroke',stylers: [{color: '#dcd2be'}]},{featureType: 'administrative.land_parcel',elementType: 'labels.text.fill',stylers: [{color: '#ae9e90'}]},{featureType: 'landscape.natural',elementType: 'geometry',stylers: [{color: '#dfd2ae'}]},{featureType: 'poi',elementType: 'geometry',stylers: [{color: '#dfd2ae'}]},{featureType: 'poi',elementType: 'labels.text.fill',stylers: [{color: '#93817c'}]},{featureType: 'poi.park',elementType: 'geometry.fill',stylers: [{color: '#a5b076'}]},{featureType: 'poi.park',elementType: 'labels.text.fill',stylers: [{color: '#447530'}]},{featureType: 'road',elementType: 'geometry',stylers: [{color: '#f5f1e6'}]},{featureType: 'road.arterial',elementType: 'geometry',stylers: [{color: '#fdfcf8'}]},{featureType: 'road.highway',elementType: 'geometry',stylers: [{color: '#f8c967'}]},{featureType: 'road.highway',elementType: 'geometry.stroke',stylers: [{color: '#e9bc62'}]},{featureType: 'road.highway.controlled_access',elementType: 'geometry',stylers: [{color: '#e98d58'}]},{featureType: 'road.highway.controlled_access',elementType: 'geometry.stroke',stylers: [{color: '#db8555'}]},{featureType: 'road.local',elementType: 'labels.text.fill',stylers: [{color: '#806b63'}]},{featureType: 'transit.line',elementType: 'geometry',stylers: [{color: '#dfd2ae'}]},{featureType: 'transit.line',elementType: 'labels.text.fill',stylers: [{color: '#8f7d77'}]},{featureType: 'transit.line',elementType: 'labels.text.stroke',stylers: [{color: '#ebe3cd'}]},{featureType: 'transit.station',elementType: 'geometry',stylers: [{color: '#dfd2ae'}]},{featureType: 'water',elementType: 'geometry.fill',stylers: [{color: '#b9d3c2'}]},{featureType: 'water',elementType: 'labels.text.fill',stylers: [{color: '#92998d'}]}],
  };
  
  
  
  
  
  
  
  
  