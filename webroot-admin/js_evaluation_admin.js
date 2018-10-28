/* [ Admin Staff Function Set ] */
	
  
  $(window).load(function () {   //  || $(document).ready(function() {		
	
	//-- 測試評量
	$('#act_test_record').click(function(){
	  location.href='index.php?act=Evaluation/mett/METT00003';	
	})
	
	
	//-- 繼續填寫
	$('.act_evaluate_continue').click(function(){
      location.href='index.php?act=Evaluation/mett/'+$(this).attr('no');
	})
	
	//-- 切換章節
	$('.section_switch > li.session').click(function(){
	  var switch_to = $(this).attr('dom');	
	  if($('#'+switch_to).length){
		$('.data_record_block').hide();  
		$('#'+switch_to).show();  
	    $('li.session.active').removeClass('active');
		$(this).addClass('active');
	  }
	})
	
	//-- 問題導覽
	$('.guide_area > li').click(function(){
	  var block_id = $(this).attr('dom');
	  location.hash = '#'+block_id;
	  $('.guide_area > li').removeClass('active');
	  $(this).addClass('active');
	});
	
	
	//-- 手動擴充內容
	$('.act_add_value').click(function(){
	  var main_dom = $(this).parents('.data_col');	
	  var uldom    = main_dom.find('ul.increase_form'); 
	  if(main_dom.find('li.pattern').length){
		var listrecordnum = uldom.find('li.listrecord:not(.pattern)').length;
		var newrecordid   = uldom.attr('id')+'add'+(listrecordnum+1);
		var new_input = main_dom.find('li.pattern').clone();
        new_input.removeClass('pattern');
		new_input.attr('id',newrecordid);
		new_input.appendTo(uldom);
	  }
	});
	
	//-- 複製評論
	$('.act_copy_emedescrip').click(function(){
	  var main_dom = $(this).parents('h2');
	  const lastdescrip = main_dom.next().html();
	  const thisdescrip = main_dom.prev().val()
	  main_dom.prev().val(thisdescrip+lastdescrip).trigger('change');
	})
	
	//-- 計算面積
	
	$(document).on('change','input[name="emd0800_area"]',function(){
	  var area_total = parseInt($('#emd0501').val())+parseInt($('#emd0502').val())	
	  var area_owner = parseInt($(this).val());
	  var area_count = (area_owner/area_total*100).toFixed(2);
	  $(this).next('input[name="emd0800_proportion"]').val(area_count);
	})
	
	
	//-- 點td即點input
	/*
	$('td.checked').click(function(){
	  if( $(this).children('input._variable').length){
		  
		var form_element = $(this).children('input._variable');
		if(form_element.attr('type')=='radio'){
		  form_element.prop('checked',true);	
		}else{
		  var change = form_element.prop('checked') ? false : true;
		  form_element.prop('checked', change);	
		}
	  }
	})
	*/
	
	
	//-- 開關進一步
	$('._furthercheck').change(function(){
	  var effect_area   = $(this).parents('tbody');
	  var further_check = effect_area.find('input._furthercheck:checked').map(function(){return parseInt($(this).val());}).get();
	  if(further_check.reduce(function (a, b) { return a + b; }, 0)){
		effect_area.next().show();  
	    effect_area.find('.furtherdescrip').show().focus();
	  }else{
		effect_area.next().hide();
		effect_area.find('.furtherdescrip').hide()
	  }
	});
	
	//-- 左右版面關聯
	$('._variable._update').on('focus',function(){
	  var block_dom = $(this).parents('._hashentry');
	  if($("li[dom='"+block_dom.attr('id')+"']").length){
		$('ul.guide_area li.active').removeClass('active');
		$("li[dom='"+block_dom.attr('id')+"']").addClass('active'); 		
	  }
	});
	
	
	//-- 遞交評量
	$('#act_submit_evaluation').click(function(){
	   
	  var record_id 	= $('#record_id').attr('no');
	  var field_dom		= $(this);
	  
	  if(!record_id){
		system_message_alert("","資料尚未建立，請先完成訪談基本資料!!");  
	    return false;
	  }
	  
	  if(!confirm("確定完成本次的評量?!")){
		return false;  
	  } 
	    
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Evaluation/finish/'+record_id},
		beforeSend: function(){  system_loading();  }
      }).done(function(response) {
	    
		if(!response.action){
		  system_message_alert('',response.info);
		  return true;
		}
		
		location.href = 'index.php?act=Evaluation/index';
		
	  }).fail(function(xhr, ajaxOptions, thrownError) {
        console.log( ajaxOptions+" / "+thrownError);
	  }).always(function(r){
        system_loading();
      });
		  	
	});
	
	//-- 下載評量資料
	$('#act_download_evaluation').click(function(){
	  window.open("index.php?act=Evaluation/download")
	})
	
	
	//-- 讀取資料
	$('#act_get_record').click(function(){
	  var record_id 	= $('#record_id').attr('no');	
	  load_record_to_page(record_id); 
	});
	
	//-- 建立評量
	$('#act_create_record').click(function(){
	  
	  var session_dom = $('#questionnaire_information'); 
	  var record_data = {};
	  var record_check=true;
	  
	  session_dom.find('._error').removeClass('_error');
	  session_dom.find('._update').each(function(){
		if(!$(this).val()){
		  $(this).addClass('_error')	
		  system_message_alert('','請填寫必要資訊!!');
		  record_check = false;
		  return false;
		} 
		record_data[$(this).attr('name')] = $(this).val();
	  });
	  
	  if(!record_check){
		return false;  
	  }
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(record_data)));
	  
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Evaluation/create/'+passer_data},
		beforeSend: function(){  system_loading(); }
      }).done(function(response) {
	    
		if(!response.action){
		  system_message_alert('',response.info);
		  return true;
		}
		
		// 輸入資料
		$('#record_id').attr('no',response.data.newa).html(response.data.newa);
		
		// 開啟介面
		$('li.session.active').next().trigger('click');
		
		// 修正網址
		history.replaceState({}, "", 'index.php?act=Evaluation/mett/'+response.data.newa);
		
		
	  }).fail(function(xhr, ajaxOptions, thrownError) {
        console.log( ajaxOptions+" / "+thrownError);
	  }).always(function(r){
        system_loading();
      });  
	  
	});
	
    
	//-- 儲存資料
	$(document).on('change','._variable._update',function(){
	  
	  var area_dom 		= $(this).parents('.data_record_block');
	  var record_id 	= $('#record_id').attr('no');
	  var table_name 	= area_dom.data('table');
	  var field_dom		= $(this);
	  var record_data 	= {};
	  if(!record_id){
		system_message_alert("","資料尚未建立，請先完成訪談基本資料!!");  
	    return false;
	  }
	  
	  if(!table_name){
		system_message_alert("","錯誤的評量區塊，請重新建立訪談記錄!!");  
	    return false;  
	  }
	  
	  if($(this).val() === $(this).data('meta')){
		return true;  
	  }
	  
	  // oth 欄位應轉換為主體欄位
	  if($(this).hasClass('_expend') && typeof $(this).attr('partof')!='undefined'){
		field_dom = $('._update[name="'+$(this).attr('partof')+'"]');
	  }else if($(this).hasClass('_element') && typeof $(this).attr('partof')!='undefined'  ){
		field_dom = $(this).parents($(this).attr('partof'));
	  }
	  
	  
	  // 取得內容
	  if(field_dom.prop("tagName")=='INPUT'){
        var field_name  = field_dom.attr('name');
        var input_value = '';
		if(field_dom.attr('type')=='checkbox'){
		  input_value = $('input[name="'+field_name+'"]:checked').map(function(){ return $(this).val();}).get().join(';');
		}else if(field_dom.attr('type')=='radio'){
		  input_value = $('input[name="'+field_name+'"]:checked').val();	
		}else if(field_dom.attr('type')=='text'){
		  input_value = field_dom.val();
		}
	    record_data[field_name] = input_value;
	  
	  }else if(field_dom.prop("tagName")=='TEXTAREA' || field_dom.prop("tagName")=='SELECT'){
		var field_name  = field_dom.attr('name');
        var input_value = field_dom.val();
        record_data[field_name] = input_value;
		
	  }else if(field_dom.prop("tagName")=='UL' && field_dom.hasClass('increase_form')){
		// 結構資料儲存 
		var field_name = field_dom.attr('id');
		var input_value={};
		field_dom.find('li.listrecord:not(.pattern)').each(function(){
		  var value_set_id = $(this).attr('id');
		  input_value[value_set_id] = {};
		  $(this).find('._update').each(function(){  
		    input_value[value_set_id][$(this).attr('name')] = $(this).val();
		  });
		}); 
		record_data[field_name] = input_value;
		
	  }else{
		  
	  }
	  
	  
	  // 項目相關動作
	  if(typeof field_dom.attr('bind') != 'undefined' && $(field_dom.attr('bind')).length){
		var relate_dom =  $(field_dom.attr('bind'));
        switch(field_dom.attr('effect')){
		  case 'disabled': relate_dom.prop('disabled',true).val('');  record_data[relate_dom.attr('name')]='';  break;
          case 'editable': relate_dom.prop('disabled',false).focus(); break;
		  case 'together': // 如果有內容就打開
		   if(field_dom.val()){
			  relate_dom.prop('disabled',false).focus();  	
			}else{
			  relate_dom.prop('disabled',true).val('');  record_data[relate_dom.attr('name')]='';	
			}
		    break; 
		  
		  case 'switch'	 : // 如果勾選就打開 
		    if(field_dom.prop('checked')){
			  relate_dom.prop('disabled',false).focus();  	
			}else{
			  relate_dom.prop('disabled',true).val('');  record_data[relate_dom.attr('name')]='';	
			}
		    break;
			
          case 'bundle':
		    if(relate_dom.val()){
			  record_data[field_name] +=';'+relate_dom.val();	
			}
		    break;
		  default:break;;
		}
	  }
	  
	  console.log(record_data)
	  
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(record_data)));
	  
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Evaluation/update/'+record_id+'/'+table_name+'/'+passer_data},
		beforeSend: function(){   }
      }).done(function(response) {
	    
		if(!response.action){
		  system_message_alert('',response.info);
		  return true;
		}
		
		if(field_dom.hasClass('_modify')){
		  field_dom.removeClass('_modify').find('._modify').removeClass('_modify');;
		}
		
		if(field_dom.find('._modify').length){
		  field_dom.find('._modify').removeClass('_modify');	
		}
		
	  }).fail(function(xhr, ajaxOptions, thrownError) {
        console.log( ajaxOptions+" / "+thrownError);
	  }).always(function(r){
        //system_loading();
      });
		
	});
	
	
	//-- 帶入上一次填寫資料 (僅適用於保護區資料表)
	$('#act_import_lastone').click(function(){
	  
	  var area_dom 	= $(this).parents('.data_record_block');
	  var record_id = $('#record_id').attr('no');
	  var table_name= area_dom.data('table');
	  var field_dom	= $(this);
	  
	  if(!record_id){
		  system_message_alert("","資料尚未建立，請先完成訪談基本資料!!");  
	      return false;
	  }
	    
	  if(!table_name){
		  system_message_alert("","錯誤的評量區塊，請重新建立訪談記錄!!");  
	      return false;  
	  }
	  
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Evaluation/bringin/'+record_id},
		beforeSend: function(){   }
      }).done(function(response) {
	     
		
		if(!response.action){
		  system_message_alert('',response.info);
		  return true;
		}
		
		load_record_to_page(record_id);  
		
	  }).fail(function(xhr, ajaxOptions, thrownError) {
        console.log( ajaxOptions+" / "+thrownError);
	  }).always(function(r){
        //system_loading();
      });
		
		
		
	  
		
	});
	
	
	//帶資料之網址
	if(document.location.href.match(/Evaluation\/mett\/(METT\d{5})/)){
	  var record = document.location.href.match(/Evaluation\/mett\/(METT\d{5})$/)
      load_record_to_page(record[1]);
	  
	}
	
	//-- 讀取資料到頁面 
    function load_record_to_page(record_id){  	       
      if(!record_id){
		system_message_alert("","資料尚未建立，請先完成訪談基本資料!!");  
	    return false;
	  }  	   
       
      // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Evaluation/read/'+record_id},
		beforeSend: function(){  system_loading(); }
      }).done(function(response) {
	    
		if(!response.action){
		  system_message_alert('',response.info);
		  return true;
		}
		var data_obj = response.data.record;
		
		data_obj.target
		data_obj.history
		
		//將資料輸入系統
		$.each(data_obj.target,function(table,meta){
		  
		  var main_form = $('#'+table);
		  $.each(meta,function(mf,mv){
            
			var   form_set   = main_form.find('._update[name="'+mf+'"]');
			const form_count = form_set.length;
			
			if(form_count == 1){ // 單向資料

			  if(mv){
				
				if(form_set.prop('tagName')=='UL' && form_set.hasClass('increase_form') ){
				  
				  var recordgroup = JSON.parse(mv)
				  $.each(recordgroup,function(recordid,valueset){
					if($('#'+recordid).length){
					  var lidom = $('#'+recordid);
					}else{
					  if(form_set.find('li.pattern').length){
						var lidom = form_set.find('li.pattern').clone();
						lidom.removeClass('pattern');
						lidom.attr('id',recordid);
						lidom.appendTo(form_set);
					  }	 
					}
					$.each(valueset,function(fname,fvalue){
						lidom.find('._update[name="'+fname+'"]').val(fvalue)
					})
				  });
				  
				  
			    }else if(form_set.prop('tagName')=='INPUT' && form_set.attr('type')=='checkbox' ){
				  form_set.prop('checked',(parseInt(mv) ? true: false));
			    }else{
				  form_set.val(mv).data('meta',mv);
			      if(form_set.prop('disabled')) form_set.prop('disabled',false);
				}
			  
			    //開關對應欄位
				if(typeof form_set.attr('bind') != 'undefined' && $(form_set.attr('bind')).length){
					var relate_dom =  $(form_set.attr('bind'));
					switch(form_set.attr('effect')){
					  case 'disabled': relate_dom.prop('disabled',true).val('');  break;
					  case 'editable': relate_dom.prop('disabled',false); break;
					  case 'switch'	 : // 如果勾選就打開 
						if(form_set.prop('checked')){
						  relate_dom.prop('disabled',false);  	
						}else{
						  relate_dom.prop('disabled',true).val('');	
						}
						break;
					  default:break;;
					}
				}
			  
			  
			  }else{
				if(form_set.prop('tagName')=='INPUT' && form_set.attr('type')=='checkbox' ){
				  form_set.prop('checked',false);  
			    }else{
				  form_set.val('').data('meta','');
				}
			  }
			   
			}else if((form_count > 1)){ // 多項資料
              
			  if(!mv) mv = ''; 
			   
			  var value_set = mv.split(';');
			  form_set.each(function(){
				$(this).prop('checked',false);  
				const search = value_set.indexOf($(this).val());
				if(search != -1 ){
				  
				  $(this).prop('checked',true);
				  value_set[search] = '';
				  
				  //開關對應欄位
				  if(typeof $(this).attr('bind') != 'undefined' && $($(this).attr('bind')).length){
					var relate_dom =  $($(this).attr('bind'));
					switch($(this).attr('effect')){
					  case 'disabled': relate_dom.prop('disabled',true).val('');  break;
					  case 'editable': relate_dom.prop('disabled',false).focus(); break;
					  case 'switch'	 : // 如果勾選就打開 
						if($(this).prop('checked')){
						  relate_dom.prop('disabled',false).focus();  	
						}else{
						  relate_dom.prop('disabled',true).val('');	
						}
						break;
					  default:break;;
					}
				  }
				  
				}	
			  })
			  
			  var value_other = value_set.filter(function(el) { return el; });
			  if(main_form.find('._update[name="'+mf+'oth"]').length){
				main_form.find('._update[name="'+mf+'oth"]').val(value_other.join(';'));  
			  }
			}
			
		  })
		  
		});
		
		// 檢核 further table 
		$('tbody.furthermain').each(function(){
          var effect_area   = $(this);
		  var further_check = effect_area.find('input._furthercheck:checked').map(function(){return parseInt($(this).val());}).get();
		  if(further_check.reduce(function (a, b) { return a + b; }, 0)){
			effect_area.next().show();  
			effect_area.find('.furtherdescrip').show().focus();
		  }else{
			effect_area.next().hide();
			effect_area.find('.furtherdescrip').hide()
		  }		
		});
		
		console.log(data_obj.history)
		
		// 載入歷史資料
		$.each(data_obj.history,function(hisindex,recordset){
		  $.each(recordset,function(rtable,rmeta){
            $.each(rmeta,function(mf,mv){
			  
			  var formset  = $('._variable._history[name="H-'+mf+'"][hisindex="'+hisindex+'"]');
			  
			  if(!formset.length) return true;
			  var formtype = formset.prop('tagName')+(typeof formset.attr('type')!='undefined' ? formset.attr('type') : '');   
               
			  switch(formtype){
				case 'INPUTtext'	:  mv ? formset.val(mv) : formset.val('未填'); break;  
				case 'INPUTradio'	:  formset.each(function(){ if($(this).val()==mv) $(this).prop('checked',true); }) 
                case 'DIV'			: 
			    case 'SPAN'			:	mv ? formset.html(mv) : formset.html('未填'); break;  
				case 'INPUTcheckbox': //還沒有
				default:break; 
			  }
			  
		    });
		  });	
		})
		
		
		
		
		
		// 填入DOM資料
		$('#record_id').attr('no',record_id).html(record_id)
		
		// 顯示通知 
		system_message_alert('alert',"讀取評量資料："+record_id); 
		
		
		
	  }).fail(function(xhr, ajaxOptions, thrownError) {
        console.log( ajaxOptions+" / "+thrownError);
	  }).always(function(r){
        system_loading();
      });
	  
	};
	
	
	
	
	
	
	
	
	
	
	
	
	//-- datepicker initial
	$("#date_open,#date_access").datepicker({
	    dateFormat: 'yy-mm-dd',
	    onClose: function(dateText, inst) { 
	      if(/\d{4}-\d{2}-\d{2}$/.test(dateText)){
		    $(this).val(dateText+' 00:00:01');
		  }
	    } 
	});
	
	
	
	
	
	
	//-- admin staff get user data
	$(document).on('click','._data_read',function(){
	  
      // initial	  
	  $('._target').removeClass('_target');
	  
	  // get value
	  var user_no    = $(this).attr('no');
	  var dom_record = $(this);
	  
	  // active ajax
	  if( ! user_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  initial_record_editer();
	  
	  if( user_no=='_addnew' ){
	    dom_record.addClass('_target');
		data_orl = {};
		
		$('#user_id').prop('readonly',false);
		$('#main_group').html('同管理者');
		$('#user_status').prop('disabled',true).val(2)
		
		$dom = dom_record.clone().removeClass('_data_read');
	    $('#record_selecter').find('.record_control').hide();
		$('#record_selecter').find('.record_list').children('.data_result').hide();
		$('#record_selecter').find('.record_list').children('.data_target').empty().append( $dom).show();
		$('#record_editor').find('a.view_switch').trigger('click');
		active_header_footprint_option('record_selecter','新增帳戶','_return_list');
	  
	  }else{
	    
		$.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Staff/read/'+user_no},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  dom_record.addClass('_target');
			  
			  var dataObj =  response.data.user;
			  data_orl = dataObj;
			  
			  // change _data_read area
			  $dom = dom_record.clone().removeClass('_data_read');
			  $('#record_selecter').find('.record_control').hide();
			  $('#record_selecter').find('.record_list').children('.data_result').hide();
			  $('#record_selecter').find('.record_list').children('.data_target').empty().append( $dom).show();
			  $('#record_editor').find('a.view_switch').trigger('click');
			  
			  // insert data
			  insert_staff_data_to_form(dataObj);
			  
			  // set foot print 
			  active_header_footprint_option('record_selecter',dataObj.user_name,'_return_list');
			  
			  // hash the address
			  location.hash = dataObj.user_id
			  
			  
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	    }).done(function() { system_loading();   });
	  }
	
	});
	
	
    //-- link to account logs
	$('#act_staff_logs').click(function(){
	  
      // initial	  
	  var staff_id    =  $('#user_id').val().length? $('#user_id').val() : '';
	  
      // check process data
	  if( !staff_id.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  } 
	  window.open('index.php?act=Record/account/'+staff_id,'_blank');
	});
    
    
	//-- save data modify
	$('#act_staff_save').click(function(){
	  
      // initial	  
	  var staff_no    =  $('._target').length? $('._target').attr('no') : '';
	  var modify_data = {};
	  var roles_data  = {};
	  
	  var act_object  = $(this);
	  var checked = true;
	  
	  // option active checked  // 檢查按鈕是否在可執行狀態
	  if( act_object.prop('disabled') ){
	    return false;
	  }
	  
	  // check process data
	  if( !staff_no.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  } 
	  
	  // get value
	  $('._update').each(function(){
	    if($(this)[0].tagName=='INPUT' && $(this).attr('name')=='roles'){
		  var field_name = $(this).attr('name');
		  roles_data[$(this).val()] = $(this).prop('checked') ? 1 : 0;
		}else{
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
		}
	  });
	  
	  if(!checked){
		return false;  
	  }
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64.encode(JSON.stringify(modify_data)));
	  var passer_roles = encodeURIComponent(Base64.encode(JSON.stringify(roles_data)));
      // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Staff/save/'+staff_no+'/'+passer_data+'/'+passer_roles},
		beforeSend: function(){  active_loading(act_object,'initial'); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  
		  if(response.action){
			
			var dataObj = response.data.user;
			data_orl = dataObj;
			
			// insert data
			insert_staff_data_to_form(dataObj);
			
			// update data no 
			if( staff_no == '_addnew'){  $('._target').attr('no',dataObj.uno) }
		  
		  }else{
			system_message_alert('',response.info);
	      }
		  
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action );  });
	});
	
	
	//--// 從server取得使用者資料並放入編輯區
	function insert_staff_data_to_form(dataObj){
	  var dom_record  = $('._target'); 
	  
	  $.each(dataObj,function(field,meta){
		if(field=='roles' && meta){
		  //  'R01':1 'R02':0 ...	
		  $.each(meta,function(rid,checked){
			$("input[name='roles'][value='"+rid+"']").prop('checked',checked);    
		    $(".role_map[data-role='"+rid+"']").attr('on',checked);
		  });
		}else if(field=='groups'){
			$("span[name='groups']").html('');	  
			$.each(meta,function(i,g){
			  if(parseInt(g.master)){
				$("span#main_group").html("<i title='"+g.ug_info+"'>"+g.ug_name+"</i>");  
			  }else{
				$("span#rela_group").append("<i title='"+g.ug_info+"'>"+g.ug_name+"；</i>");	  
			  }
			});  
		}else{
			if(  $("._variable[id='"+field+"']").length ){  
			  $("._variable[id='"+field+"']").val(meta);  
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
 
    
	 
	
	
	
  });	
  
  
  