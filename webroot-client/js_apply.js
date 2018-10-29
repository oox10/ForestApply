
$(window).load(function () {   //  || $(document).ready(function() {
	
	
	/*================================*/
	/*--    Landing Function set    --*/
	/*================================*/
	
	
	
	//-- page link 
	$('#system_mark').click(function(){
	  location.href='index.php';	
	});
	
	
	/*================================*/
	/*--    Booking Function set    --*/
	/*================================*/
	
	var apply = {};
    var picker_is_signal_day = false; 
	
	//-- form element initial
	
	// time picker
	if($('.apply_time').length){
	  $('.apply_time').timepicker({ 
	    'timeFormat': 'H:i:s',
	    'minTime':$('#area_apply_time_picker_config').data('open'),
		'maxTime':$('#area_apply_time_picker_config').data('close')
	  });	
	}
	
	//-- bind apply date picker
	if($('#apply_date_1s').length){
	  initial_date_picker($('#apply_date_1s') , picker_is_signal_day); 
	}
	
	
	//-- cancel apply process 
	$('button.apply_cancel').click(function(){
	  
	  if( location.hash && location.hash!='#'){
        		
        var data_no = location.hash.replace(/^#/,'');
        
		if(!confirm("確認要取消申請?")){	
		  return false;  
		}
		
		// active ajax
        $.ajax({
			url: 'index.php',
			type:'POST',
			dataType:'json',
			data: {'act':'Landing/cancel/'+data_no},
			beforeSend: function(){   },
			error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
			success: 	function(response){	  
			  if(response.action){
				alert('申請已取消');
				location.href='./';  
			  }else{
				system_message_alert('',response.info);  
			  }
			},
			complete:	function(){  }
        }).done(function(r) {   });
		
	  }else{
		location.href='./';     
	  }
	});
	
	
	//-- apply process 
	$('.progress li.step').click(function(){
	  
      if($(this).hasClass('.currency')){
		return false;  
	  }
	  
	  var step_block = $(this).data("section");
	  
	  if(!$('#'+step_block).length){
		return false;  
	  }
	  
	  if( !$(this).prev().hasClass('checked')){
		system_message_alert('','請先完成當前步驟!');
		return false;  
	  }
	  
	  $('.booking_step').hide();
	  $('#'+step_block).show();
	  
	  $('.currency').removeClass('currency');
	  $(this).addClass('currency');
	  
       
	  
	});
	
	
	//-- step 02 checked
	apply['applicant'] = {};
	
	// user data search
	var history = {};
	
	$('.apply_data.chief').change(function(){
	  var applicant = {};
	  var checker   = 0;
	  $('.apply_data.chief').each(function(){
	    if($(this).val()){
		  applicant[$(this).attr('id')] = $(this).val();
		  checker++;
		}
	  });
	  
	  if(checker!=3){ return false; }
	  
	  var pass_data = encodeURIComponent(Base64M.encode(JSON.stringify(applicant)));
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {'act':'Landing/history/'+pass_data},
		beforeSend: function(){   },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response){	  
		  if(response.action && response.data.length){
			history = response.data  
			$.each(response.data,function(i,apply){
			  var record = $('<li/>').addClass('applied').attr('h',i);
			  record.append("<span class='date'>"+apply.apply_date+"</span>");
			  record.append("<span class='area'>"+apply.area_name+"</span>");
			  record.append("<span class='tool'>帶入資料 : <a class='option keyin_userdata' title='帶入申請人資料'>申請人</a> <a class='option keyin_application' title='帶入申請資料' >申請書</a> </span>");	
			  record.appendTo('.apply_record');
			  history[i] = {};
			  history[i]['applicant']   = apply.applicant;
			  history[i]['application'] = apply.application;
			});
			$('.history').show();
		  }
	    },
		complete:	function(){  }
      }).done(function(r) {   });
	});
	
	
	// 帶入使用者資料
	$(document).on('click','.keyin_userdata',function(){
      var applicant_index = $(this).parents('li').attr('h');
      if( history[parseInt(applicant_index)]['applicant'] ){
		var apply_data = history[parseInt(applicant_index)]['applicant'];  
        $.each(apply_data,function(f,v){
		  if( $('#'+f).length) $('#'+f).val(v);
		});
	    system_message_alert('alert',"申請人資料已載入");
	  }
	});
	
	// 帶入申請資料
	$(document).on('click','.keyin_application',function(){
      var applicant_index = $(this).parents('li').attr('h');
      if( history[parseInt(applicant_index)]['application'] ){
		var apply_data = history[parseInt(applicant_index)]['application'];
		// 載入申請資料 #僅載入其他欄位部分
		$.each(apply_data,function(af,av){
		  switch(af){
			case 'fields':  				
			  $.each(av,function(id,fconf){
				if($('#'+id).length){
				  $('#'+id).val(fconf.value);
				}
			  });
			  break; 
		  }
		});
		system_message_alert('alert',"申請書資料已帶入");
	  }
	});
	
	
	// copy mail address to regadd
	if($('#copy_mailaddress').length){
	  $('#copy_mailaddress').click(function(){
	    var user_address = $('#applicant_mailaddress').val();	
	    $('#applicant_regaddress').val(user_address);	
	  });
	}
	
	// applicant submit 
	$('#apply_step_02').click(function(){  
	  
	  var checked=1;
	  
	  $('#applicant_form').find('.apply_data').each(function(){
		$(this).removeClass('_fail');  
		if(!$(this).val()){
		  $(this).addClass('_fail');
		  var field = $(this).parent().prev().text();
		  checked = 0;
		}else{
		  apply['applicant'][$(this).attr('id')] = $(this).val(); 	
		}
	  });  
	  
	  if(!checked){
		system_message_alert('','請將資料填寫完整'); 
		return false;
	  }
	  
	  var apply_code= location.hash ? location.hash.replace(/^#/,'') : '';
	  var pass_data = encodeURIComponent(Base64M.encode(JSON.stringify(apply['applicant'])));
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {'act':'Landing/initial/'+pass_data+'/'+apply_code},
		beforeSend: function(){  system_loading() },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response){
		  if(response.action){
			var apply_code = response.data;
			$('li.step.currency').attr('no',apply_code);
			location.hash = apply_code;
			
			$('.step.currency').addClass('checked');
			$('.step.currency').next().trigger('click');  
			$(window).scrollTop(0);
			$('#apply_area').focus();
			
		  }else{
			$.each(response.data,function(f,v){
			  $('#'+f).val(v).addClass('_fail');	
			});  
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) { system_loading()  });
	  		
	});
	
	
	
	/* == step 03 config  == */
	
	
	//-- textarea auto resize
	$("textarea").css("overflow","hidden").bind("keydown keyup blur focusin", function(){  
		var scrollheight = ($(this).prop("scrollHeight")-10);
		var boxheight	 = $(this).height(); 
		if(scrollheight > boxheight){
		  $(this).height(scrollheight+"px");
		}
    });  
   
	// assist open
	$('.assist_block > input').on('focusin',function(){
	  var position   = $(this).position();  
	  var location_top = position.top+45;
	  if( $('#area_gates_assist').length){
		$('#area_gates_assist').attr('bind',$(this).attr('id')).css('top',location_top).show();
	  }
	});
	
	// assist close
	$('#area_gates_assist').on('mouseleave',function(){
	  $('#area_gates_assist').hide();
	});
	
	// assist input & close 
	$('.input_assist > li').click(function(){
	  var assist_dom  = $(this).parent();
	  var assist_val  = $(this).attr('name');
	  var assist_link = $(this).attr('block');
	  
	  var assist_form= assist_dom.attr('bind');
	  if($('#'+assist_form).length && assist_val){
		if(typeof assist_link !='undefined' && !$("input[type='checkbox'][value='"+assist_link+"']:checked").length){
          $("input[type='checkbox'][value='"+assist_link+"']").prop('checked',true);
		  system_message_alert("alert","新增進入範圍 - "+assist_link );
		}
		$('#'+assist_form).val(assist_val); 
	  }
	  assist_dom.attr('bind','').hide();
	});
	

	// check apply reason 申請相關項目設定
	// 1. 附件  2.跨日
	$('input.apply_reason').click(function(){
	  
	  //-- set attachemnt block
	  var visibility = parseInt( $(this).attr('attach') ) ? 'visible':'hidden';
	  $('#apply_documents').css('visibility',visibility);  
	  
	  //-- set datepicker block
	  var setSingleDate = parseInt($(this).attr('crossday')) ? false:true;
	 
	  // check picker mode is change
      if(picker_is_signal_day !== setSingleDate){
		
		$('#apply_date_1e').prop('readonly',setSingleDate);
	  
	    $('#apply_date_1s').data('dateRangePicker').clear();
	    $('#apply_date_1s').data('dateRangePicker').destroy();  
		initial_date_picker( $('#apply_date_1s'),setSingleDate); 
        picker_is_signal_day = setSingleDate;		
	  }
	  
	  // set mbr check lower
	  var mbrcheck = parseInt($(this).attr('limit')) ? parseInt($(this).attr('limit')) : 1;
	  $('#apply_step_04').attr('mbrlower',mbrcheck);
	  
	});
	
	
	
	//-- step 01 checked
	$('#apply_agrement').click(function(){
	  if(!$(this).prop('checked')){
		return false;  
	  }
	  $('.step.currency').addClass('checked');
	  $('.step.currency').next().trigger('click');  
      $(window).scrollTop(0);
	  $('#applicant_name').focus();
	});
	
	
	
	//-- step 03 checked
	$('.apply_step_03').click(function(){
	  
	  var apply_id = location.hash.replace(/^#/,'');
	  if(!apply_id.length || apply_id.length < 8 ){
		system_message_alert('','尚未填寫申請人資料，請回到上一步'); 
		return false;  
	  }
	  
	  $('._fail').removeClass('_fail');
	  apply['form'] = {};
	  
	  // 檢查各項欄位
	  
	  // check-01 區域、進入範圍、出入口與時間
	  apply['form']['area'] = {};
	  
	  var area = $('#apply_area').val();
	  if(!area){
		system_message_alert('','尚未選擇申請區域');  
	    return false;
	  }
	  
	  // 1.申請區域 : 編碼
	  apply['form']['area']['code'] = area;
	  
	  // 2.進入範圍 : array()
	  apply['form']['area']['inter'] = $("input[name='inter_area']").map(function(){
		if( ($(this).attr('type')=='checkbox' && $(this).prop('checked')) || ($(this).attr('type')=='text' && $(this).val()!='') ){
		  return $(this).val();	
		} 
	  }).get();
	  
	  if(!apply['form']['area']['inter'].length){
		$('#inter_area_other').focus()    
		system_message_alert('','請填寫進入範圍資訊');  
	    return false;  
	  }
	  
	  // 3.到達出入口與時間 : 
	  apply['form']['area']['gate'] = {};
	  if( !$('#area_gate_entr').val() || !$('#area_gate_entr_time').val() || !$('#area_gate_exit').val() || !$('#area_gate_exit_time').val() ){
		system_message_alert('','請補充進出入口資訊');  
        $('#area_gate_entr').focus();		
		return false;  
	  }  
	  apply['form']['area']['gate']['entr']      = $('#area_gate_entr').val();
	  apply['form']['area']['gate']['entr_time'] = $('#area_gate_entr_time').val();
	  apply['form']['area']['gate']['exit']      = $('#area_gate_exit').val();
	  apply['form']['area']['gate']['exit_time'] = $('#area_gate_exit_time').val();
	  
	  
	  // check-02 申請目的與項目
	  // 1. 申請理由
	  apply['form']['reason'] = [];
	  var have_attach = false;
	  
	  if($("input[name='apply_reason']").length){
		$("input[name='apply_reason']").each(function(){
		  if( ( ( $(this).attr('type')=='checkbox' || $(this).attr('type')=='radio')  && $(this).prop('checked')) || 
		      (   $(this).attr('type')=='text' && $(this).val()!='') )
		  {
			var reason = {};
			reason['item']   = $(this).val();
			reason['limit']  = parseInt($(this).attr('limit')) ? 1 : 0;
			apply['form']['reason'].push(reason);	
			
			if(parseInt($(this).attr('attach'))){
			  have_attach = parseInt($(this).attr('attach')); 	
			}
			
		  }
		});
		
		if(!apply['form']['reason'].length){
		  system_message_alert('','尚未選擇申請目的');  
	      return false;	
		}
	  }
	  
	  
	  // 2. 上傳附件
	  apply['form']['attach'] = [];
	  $('li.attachment').each(function(){
		var attachment = {};
		attachment['code'] = $(this).attr('code');
        attachment['time'] = $(this).attr('time');
        attachment['file'] = $(this).attr('file');
        apply['form']['attach'].push(attachment);
	  });
	  
	  if(have_attach && apply['form']['attach'].length < have_attach  ){
		$('#apply_attachment_upload').focus();  
		system_message_alert('','請上傳所需附件，您所勾選的申請項目附件至少需要『'+have_attach+'』件!!');  
	    return false;	  
	  }
	  
	  
	  // check-03 申請時間
	  var date_checked = true;
	  apply['form']['dates'] = [];
	  
	  $('.apply_date').each(function(){
		var date_string = $(this).val();
		
		if( $(this).is(':disabled') || !date_string  ){
		  return true;	
		}
		
		var apply_date = date_string.split('-')
		var y = parseInt(apply_date[0]);
		var m = parseInt(apply_date[1]);
		var d = parseInt(apply_date[2]);
	    var date = new Date(date_string);
		
		if(date_string.length !=10 || !y || !m || !d || isNaN(date)){
		  $(this).addClass('_fail');
		  date_checked = false;
		  system_message_alert('','申請日期格式錯誤');  
		  $(this).focus();
		}
	  });
	  
	  if(!date_checked) return false;
	  
	  $('.apply_dates').each(function(){
		var apply_date_set = $(this).find('.apply_date').map(function(){  if( $(this).val() ) return $(this).val(); }).get();  
		if(apply_date_set.length){
		  apply['form']['dates'].push(apply_date_set);  	
		}
	  });
	  
	  // check-04 其他欄位
	  apply['form']['fields'] = {};
	  if($('.other_form').length){
		
		$('.other_form').each(function(){
          
		  var oth_title = $(this).find('label').text() 
		  var oth_field = $(this).attr('id');
		  var oth_value = '';
		  
		  switch($(this).attr('mode')){
			case 'radio': case 'checkbox':
			  if( !$(this).find('.apply_data:checked').length ){
				system_message_alert('',"申請資料選項：『"+oth_title+"』 尚未勾選!!");			
				date_checked = false;
				return false;
			  }
			  oth_value = $(this).find('.apply_data:checked').map(function(){return $(this).val();}).get().join(';');
			  break;
			  
			case 'textarea': 
            case 'text':
            default:			
			  if( !$(this).find('.apply_data').val() ){
				system_message_alert('',"請填寫欄位『"+oth_title+"』內容");			
				$(this).find('.apply_data').addClass('_fail').focus();
				date_checked = false;
				return false;
			  }
			  oth_value = $(this).find('.apply_data').val();
			  break;  
		  }
		  
		  apply['form']['fields'][oth_field]={
			'field': oth_title ,
			'value': oth_value
		  }
		
		});
	  }
	  
	  if(!date_checked) return false;
	  
	  var pass_data = encodeURIComponent(Base64M.encode(JSON.stringify(apply['form'])));
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {'act':'Landing/applyform/'+apply_id+'/'+pass_data},
		beforeSend: function(){  system_loading() },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response){
			
		  if(response.action){
			load_apply_member(response.data.members.memberlist)  
			$('.step.currency').addClass('checked');
			$('.step.currency').next().trigger('click'); 
			$(window).scrollTop(0);
		  
		  }else{
			if(typeof response.data.submit.fail !== 'undefined'){
			  $.each(response.data.submit.fail,function(f,v){
			    $('#'+f).addClass('_fail').next('.warning').text(v);
			  });  	
			}
			system_message_alert('',response.info);
		  }
	    },
		complete:	function(){  }
      }).done(function(r) { system_loading()  });
	   
	});
	
	
	//-- upload attachment
	$('#apply_attachment_upload').change(function(){
	  
	  // initial	  
	  var apply_id  =  location.hash.replace(/^#/,'');
  	  
	  // check process data
	  if( !apply_id.length ){
	    system_message_alert('',"尚未填寫申請者資料");
		$(this).val('');
	    return false;
	  }
	  
	  var file_upload = $(this).val();
	  var file_name   = file_upload.split('\\').pop();
	  
	  if(!file_upload){
		return false;		
	  }
	  
	  if( /\.(pdf|jpg|png)$/.test(file_name)===false ){
		system_message_alert('','檔案類型錯誤，請上傳PDF或影像掃描檔案');  
		$(this).val('');
	    return false;	
	  }
	  
	  var action = $('#apply_attachment_upload_form').attr('action');
	  $('#apply_attachment_upload_form').attr('action',action+apply_id);
	  var FormObj = document.getElementById('apply_attachment_upload_form'); 
	  FormObj.submit()
	  $('#apply_attachment_upload_form').attr('action',action);	
	  
	});
	
	//-- delete attachment
	$(document).on('click','.adele',function(){
	  var main_dom = $(this).parents('li.attachment');	
	  if(!confirm("確定要刪除已上傳的附件!?")){
		return false;
      } 
	  main_dom.empty().remove();	
	})
	
	
	
	//-- step 04 member list
    
	//-- get member list
	$('.act_member_list_file').click(function(){
      var apply_id  =  location.hash.replace(/^#/,'');	
      var export_type = $(this).data('format');	  
	  window.open("index.php?act=Landing/getlist/"+apply_id+'/'+export_type);
	});
	
	
	
	//-- set upload excel file
	$('#apply_member_upload').change(function(){
	  
	   // initial	  
	  var apply_id  =  location.hash.replace(/^#/,'');
  	  
	  // check process data
	  if( !apply_id.length ){
	    system_message_alert('',"尚未填寫申請者資料");
		$(this).val('');
	    return false;
	  } 
	  
	  
	  var file_upload = $(this).val();
	  var file_name   = file_upload.split('\\').pop();
	  
	  if(!file_upload){
		return false;		
	  }
	  
	  if( /\.xls(x)?$/.test(file_name)===false && /\.ods?$/.test(file_name)===false){
		system_message_alert('','檔案名稱錯誤，請上傳正確資料');  
		$(this).val('');
	    return false;	
	  }
	  
	  var action = $('#apply_member_upload_form').attr('action');
	  $('#apply_member_upload_form').attr('action',action+apply_id);
	  var FormObj = document.getElementById('apply_member_upload_form'); 
	  FormObj.submit()
	  $('#apply_member_upload_form').attr('action',action);
	  
	});
	
	//-- create new member 
	$('#act_add_member').click(function(){
	  var mcount = $('tr.member:not(.template)').length;
	  
	  if(mcount>100){
		system_message_alert('','成員清單超過100人上限!!');  
	    return false;
	  }
	  
	  var mbr_dom = $('tr.member.template').clone().removeClass('template');	
	  mbr_dom.attr('edit','1');
      mbr_dom.find('.mbr_no').text(mcount+1);
	  mbr_dom.find('.mbr_role').text('成員');
	  mbr_dom.find('input').each(function(){
	    if($(this).attr('type')=='radio' || $(this).attr('type')=='checked'){
		  $(this).prop('checked',false);	
		}else{
		  $(this).val('');	 
		}
		$(this).prop('readonly',false);
	  });
	  mbr_dom.find('.member_sex').attr('name','P-'+mcount);
	  mbr_dom.appendTo('#apply_member_list').find('.member_name').focus();
	  $('#apply_submit').prop('disabled',true);
	});
	
	//-- delete member
	$(document).on('click','.mdele',function(){
	  var main_dom = $(this).parents('.member');
	  if(!confirm("確定要刪除已編輯之成員!?")){
		return false;
      } 	
	  main_dom.remove();
	  system_message_alert('alert','成員已刪除');
	  $('#apply_submit').prop('disabled',true);
	});
	
	// edit member
	$(document).on('click','.medit',function(){
	  var main_dom = $(this).parents('.member');
	  main_dom.attr('edit',1).find('input').prop('readonly',false);
	  $('#apply_submit').prop('disabled',true);
	  
	});
	
	// save member
	$(document).on('click','.msave',function(){
	  var main_dom = $(this).parents('.member');
	  main_dom.attr('edit',0).find('input').prop('readonly',true);
	});
	
	
	// 取得成員清單
	function get_apply_member(){
      var mcount = $('tr.member:not(.template)').length;
      if(mcount <= 0 ){
		system_message_alert('','尚未編輯參加成員名單!');  
	    return false;
	  }	  

	  var member_list = []; 
      $('tr.member:not(.template)').each(function(){
		var member = {};
        member['member_role'] = $(this).find('.mbr_role').text();		
        $(this).find('input').each(function(){
	      if( $(this).attr('type')=='radio' || $(this).attr('type')=='checked' ){
		    if($(this).prop('checked') ){
			  member[$(this).attr('class')] = $(this).val();
		    }
		  }else{
		    member[$(this).attr('class')] = $(this).val();	 
		  }
		  
		  $(this).parent('.mbr_data').attr('fail','');
	    });
		member_list.push(member);
	  });
	  return member_list;
	}
	
	
	
	//-- step 04 submit member list
	$('#apply_step_04').click(function(){
      
	  // initial	  
	  var apply_id  =  location.hash.replace(/^#/,'');
	  
	  // check process data
	  if( !apply_id.length ){
	    system_message_alert('',"請先完成先前步驟");
		return false;
	  } 
	  
	  var mmeber_list = get_apply_member();
	  
	  // check mber lower
	  const member_lowerbound = parseInt($(this).attr('mbrlower')) ? parseInt($(this).attr('mbrlower')) : 1;  
	  if( mmeber_list.length < member_lowerbound){
		system_message_alert('',"您所申請的項目，進入成員至少需要『 "+member_lowerbound+" 』位");
		return false;  
	  }
	  
	  var pass_data = encodeURIComponent(Base64M.encode(JSON.stringify(mmeber_list)));
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {'act':'Landing/savembr/'+apply_id+'/'+pass_data},
		beforeSend: function(){  system_loading() },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response){
		  if(response.action){
			
			$('#apply_member_list').find('tr.member').each(function(){
			  $(this).attr('edit',0).attr('save',1);
			});
			$('.step.currency').addClass('checked');
	        $('#apply_submit').prop('disabled',false);
			
		  }else{
            
			if(typeof response.data != 'undefined' ){  
			  $.each(response.data , function(no,fields){
			    var member_form = $('tr.member:eq('+no+')');
                member_form.find('.medit').trigger('click'); 				
				$.each(fields,function(f,e){
				  member_form.find('.'+f).parent().attr('fail',e);  	
				});
			  });
			}  
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) { system_loading()  });
	  
	});
	
	
	//-- submit apply
	$('#apply_submit').click(function(){
	  
	  if($(this).prop('disabled')){
		system_message_alert('',"遞交申請尚未解鎖，請先執行確認成員功能後再遞交申請。");  
	    return false;  
	  }
	  
	  if($(this).hasClass('checked')){
		return false;  
	  }
	  
	  // initial	  
	  var apply_id  =  location.hash.replace(/^#/,'');  	
       	  
      // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {'act':'Landing/submit/'+apply_id},
		beforeSend: function(){  system_loading() },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response){
		  if(response.action){
			location.href = 'index.php?act=Landing/license/'+response.data.check	
		  }else{
			system_message_alert('',response.info);
		  }
	    },
		complete:	function(){  }
      }).done(function(r) { system_loading(); });
	});
	
	
	
	/*== [ apply review page ] ==*/ 
	
	//-- apply data to update // 更新
	$('#act_apply_tomodify').click(function(){
	  
      if($(this).prop('disabled')){
		system_message_alert('','功能尚未開放，您可能處於錯誤狀態');    
	    return false;
	  }
	  
	  if( !$(this).attr('code') || !$(this).attr('area') ){
		system_message_alert('','參數錯誤，請重新整理頁面');    
	    return false;   
	  }
	  
	  location.href = 'index.php?act=Landing/reserve/'+$(this).attr('area')+'#'+$(this).attr('code');
	  
	});
	
	//-- apply data to review //補件
	$('#act_apply_toreview').click(function(){
	  
      if($(this).prop('disabled')){
		system_message_alert('','功能尚未開放，您可能處於錯誤狀態');    
	    return false;
	  }
	  
	  if( !$(this).attr('code') || !$(this).attr('area') ){
		system_message_alert('','參數錯誤，請重新整理頁面');    
	    return false;   
	  }
	  
	  location.href = 'index.php?act=Landing/reserve/'+$(this).attr('area')+'#'+$(this).attr('code');
	  
	});
	
	//-- apply data to cancel //取消
	$('#act_apply_tocancel').click(function(){
	  
      if($(this).prop('disabled')){
		system_message_alert('','功能尚未開放，您可能處於錯誤狀態');    
	    return false;
	  }
	  
	  if( !$(this).attr('code') || !$(this).attr('area') ){
		system_message_alert('','參數錯誤，請重新整理頁面');    
	    return false;   
	  }
	  
	  
	  if(!confirm("取消後本次申請單將被註銷並無法復原，確定要取消目前的申請單？，")){
		return false;  
	  }
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {'act':'Landing/cancel/'+$(this).attr('code')},
		beforeSend: function(){  system_loading() },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response){
		  if(response.action){  
		    
			var dt = new Date();
			var today = dt.getFullYear()+'-'+(dt.getMonth()+1)+'-'+dt.getDate();
		    $('#apply_progress_info').html('最終階段');
			$('#apply_status_info').html('取消申請');
			$('.process_header').attr('stage',5);
			$('#client').find('td.stage5').prepend("<div class='progres_record'><div class='prmain'><span class='logtime' title=''> "+today+" </span><span class='loginfo'>取消申請</span></div></div>");
		    
			$('#act_apply_tocancel').remove();
		  
		  }else{
			system_message_alert('',response.info);
		  }
	    },
		complete:	function(){  }
      }).done(function(r) { system_loading(); });
	  
	  
	});
	
	
	//-- apply data license download //download
	$('#act_license_download').click(function(){
	  	
	  if($(this).prop('disabled')){
		system_message_alert('','申請尚未完成，不開放下載');    
	    return false;
	  }
	  
	  if( !$(this).attr('code') || !$(this).attr('area') ){
		system_message_alert('','參數錯誤，請重新整理頁面');    
	    return false;   
	  }	
		
	  // active ajax
      window.open('index.php?act=Landing/download/'+$(this).attr('code'))
	  
	});
	
	
	//-- apply change member 替換成員
	$('#act_quick_apply').click(function(){
	  
      if($(this).prop('disabled')){
		system_message_alert('','功能尚未開放，您可能處於錯誤狀態');    
	    return false;
	  }
	  
	  if( !$(this).attr('code') || !$(this).attr('area') ){
		system_message_alert('','參數錯誤，請重新整理頁面');    
	    return false;   
	  }
	  
	  var area_code = $(this).attr('area');
	  
	  if(!confirm("確定將使用本資料再次提出申請其他進入日期? \n注意，相同成員同一區域之進入日期不可重複!!")){
		return false;  
	  }
	  
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {'act':'Landing/duplicate/'+$(this).attr('code')},
		beforeSend: function(){  system_loading() },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response){
		  if(response.action){  
		    location.href = 'index.php?act=Landing/reserve/'+area_code+'#'+response.data.newcode; 
		  }else{
			system_message_alert('',response.info);
		  }
	    },
		complete:	function(){  }
      }).done(function(r) { system_loading(); });
	  
	});
	
	
	
	
	//-- apply change member 替換成員
	$('#act_apply_mbrchang').click(function(){
	  
      if($(this).prop('disabled')){
		system_message_alert('','功能尚未開放，您可能處於錯誤狀態');    
	    return false;
	  }
	  
	  if( !$(this).attr('code') || !$(this).attr('area') ){
		system_message_alert('','參數錯誤，請重新整理頁面');    
	    return false;   
	  }
	  location.href = 'index.php?act=Landing/reserve/'+$(this).attr('area')+'#'+$(this).attr('code');
	  
	  
	});
	
	
	
	
	
	 // initial account data  //帶有參數的網址連結資料
	if(document.location.hash.match(/^#.+/) && location.search.match(/^\?act=Landing\/reserve\/[\w\d]+/) ){
		
	  var apply_code = location.hash.replace(/^#/,'');
	  // active ajax
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {'act':'Landing/recover/'+apply_code },
		beforeSend: function(){  system_loading() },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response){
		  if(response.action){
			
			var apply_data = response.data.applied;
			
			// 載入申請人資料
			$.each(apply_data.applicant,function(uf,uv){
			  if($('#'+uf).length){
				$('#'+uf).val(uv);  
			  }	
			});
			
			
			// 載入申請資料
			$.each(apply_data.application,function(af,av){
			  switch(af){
				
				case 'area':   
				  
				  if( $('#apply_area').length ) {
					$('#apply_area').val(av.code);
				  } 
				  
				  if(av.inter.length){
					$("input[name='inter_area']").prop('checked',false);
					$.each(av.inter,function(i,block){
					  if($("input[name='inter_area'][value='"+block+"']").length){
						$("input[name='inter_area'][value='"+block+"']").prop('checked',true);  
					  }else{
						$('#inter_area_other').val(block);
					  }
					});  
				  }
				  
				  if(typeof av.gate != 'undefined'){
                    $('#area_gate_entr').val(av.gate.entr);
					$('#area_gate_entr_time').val(av.gate.entr_time);
					$('#area_gate_exit').val(av.gate.exit);
					$('#area_gate_exit_time').val(av.gate.exit_time);					
				  }
				  break;
				
				case 'reason': 
				  
				  $("input.apply_reason").prop('checked',false);
				  $.each(av,function(i,r){
					if($("input.apply_reason[value='"+r.item+"']").length){
					  
					  $("input.apply_reason[value='"+r.item+"']").prop('checked',true);
					  
					  var visibility = parseInt($("input.apply_reason[value='"+r.item+"']").attr('attach')) ? 'visible':'hidden';
					  $('#apply_documents').css('visibility',visibility);   
					  
					  // set mbr check lower
					  const mbrcheck = parseInt( $("input.apply_reason[value='"+r.item+"']").attr('limit')) ? parseInt( $("input.apply_reason[value='"+r.item+"']").attr('limit')) : 1;
					  $('#apply_step_04').attr('mbrlower',mbrcheck);
					  
					  
					}else{
					  $('#apply_reason_other').val(r.item);	
					}
					
				  });
				  break;
				
				case 'attach':
				  $.each(av,function(id,doc){
					var attach = $('<li/>').addClass('attachment').attr({'code':doc.code,'file':doc.file,'time':doc.time});
					attach.append('<a class="option adele" title="刪除附件"><i class="fa fa-trash-o" aria-hidden="true"></i></a>');    
					attach.append('<span class="file" title="'+doc.file+'" >'+doc['file']+'<br/><i class="time">'+doc.time+'</i></span>');    
					attach.appendTo($('#attachment_list'));
				  });
				  break;
				
                case 'dates':  				
                  $.each(av,function(i,dv){
					var date_set = $('.apply_dates')[i];
					$(date_set).find('input.apply_data').each(function(i){
					  $(this).val(dv[i]);	
					});
					
					if(dv.length==1){
					  $('#apply_date_1s').data('dateRangePicker').setDateRange(dv[0],dv[0]);
					  $('#apply_date_1e').val(dv[0]);
					}else{
					  $('#apply_date_1s').data('dateRangePicker').setDateRange(dv[0],dv[1]);		
					}
					
				  });
				  break; 
				
				case 'fields':  				
                  $.each(av,function(id,fconf){
					if($('#'+id).length){
					  var main_dom = $('#'+id);
					  if(main_dom.attr('mode')=='checkbox' || main_dom.attr('mode')=='radio'){
						var checked = fconf.value.split(';');
                        $.each(checked,function(i,v){
						  main_dom.find('.apply_data[name="'+id+'"][value="'+v+'"]').prop('checked',true);	
						});
					  }else{
						main_dom.find('.apply_data[name="'+id+'"]').val(fconf.value);  
					  }	 
					}
				  });
				  break; 
			  }
			});
			
			// 載入名單
			load_apply_member(apply_data.joinmember);
			
			
			
			// 進入審核後不可變更相關欄位鎖定相關申請項目
			if(parseInt(apply_data.stage) > 2 ){
			  $("input[name='apply_reason'],#apply_date_1s").prop('readonly',true).prop('disabled',true);  
			}
			
			
			
			// 定位申請狀態
			/*
			if($('li.step[status="_INITIAL"]').length){
			  $('li.step[status="_INITIAL"]').trigger('click');	
			  $('li.step[status="_INITIAL"]').prevAll().addClass('checked');
			}
			*/
			
			$('#apply_agrement').prop('checked',true);
			$('li.step[status="_INITIAL"]').prevAll().addClass('checked').end().trigger('click');
			$(window).scrollTop(0);
			
		  }else{
			system_message_alert('',response.info);
	      }
	    },
		complete:	function(){  }
      }).done(function(r) { system_loading()  });
	}
	
	/******************************
	===  register.html -unused  ===   
	******************************/
	
	// user declare
	if($('.declare_option').length){
	  $('#declare_agree').click(function(){
	    $('.regdeclare_block').hide();
	  });
	  
	  $('#declare_disagree').click(function(){
	    //alert("您不同意本聲明，將回到系統首頁.");
	    location.href='index.php?act=Account';
	  });
	  
	}
	
	// user sign up check
    if($('#reg_act_sent').length){
      
	  $('#reg_act_sent').click(function(){
	    
		var reg_check = {};
		$('._regist._wrong').removeClass('_wrong');
		$('._regist').each(function(){  
		  switch($(this).attr('id')){
		    case 'user_mail'	: if($(this).val().match(/^[\w\d\.\_\-]+@[\w\d\.]+$/)){ reg_check[$(this).attr('id')]=$(this).val();   } break;
			case 'user_name'	: if($(this).val().length){reg_check[$(this).attr('id')]=$(this).val();  } break;
			case 'user_idno'	: if($(this).val().length){reg_check[$(this).attr('id')]=$(this).val();  } break;
			case 'user_staff'	: if($(this).val().length){reg_check[$(this).attr('id')]=$(this).val();  } break;
			case 'user_tel'		: if($(this).val().length){reg_check[$(this).attr('id')]=$(this).val();  } break;
			case 'user_organ'	: if($(this).val().length){reg_check[$(this).attr('id')]=$(this).val();  } break;
			case 'user_group'	: if($(this).val().length){reg_check[$(this).attr('id')]=$(this).val();  } break;
		    default: reg_check[$(this).attr('id')]=$(this).val();
		  }
		});
	    
	    if(  Object.keys(reg_check).length  != $('._regist').length  ){
		  // 各欄位標示
		  $($('._regist').get().reverse()).each(function(){
			if(!reg_check[$(this).attr('id')]){
			  $(this).addClass('_wrong').focus();
			}
		  });
		  
		  // reset 驗證碼
		  document.getElementById('captcha').src=document.getElementById('captcha').src + '?' + (new Date()).getMilliseconds();
		  $('#captcha_input').val('');
		  
		  system_message_alert('','請將資料填寫完整');
		  return false;
		}  
		  
		if($('#captcha_input').val().length != 4 ){
		  $('#captcha_input').focus();
		  system_message_alert('',"請輸入正確的驗證碼");
		  // reset 驗證碼
		  document.getElementById('captcha').src=document.getElementById('captcha').src + '?' + (new Date()).getMilliseconds();
		  $('#captcha_input').val('');
		  return false;
		}
		
		// lock all field
		$('._regist').prop('disabled',true);
		
		//送出表單
		if(!confirm("確認送出註冊申請？")){	
		  return false;  
		}	
		
		var captcha = $('#captcha_input').val();
		var reg_data = encodeURIComponent(JSON.stringify(reg_check));
			
		$.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Account/signup/'+captcha+'/'+reg_data},
		  beforeSend: function(){  system_loading(); 
		    $('#regist_submit').hide();
			$('.process').html('帳號註冊中....');
			$('#regist_finish').show();
		  },
		  error: function(xhr, ajaxOptions, thrownError) { 
		    system_message_alert('',"頁面失敗，請重新送出"); 
		    $('#regist_submit').show();
			$('.process').empty(); $('#regist_finish').hide();
			$('._regist').prop('disabled',false);
		  },
	      success: function(response) {
			if(response.action){
			  $('.process').html('帳號註冊成功，請靜候審核通知信件');
              system_message_alert('alert','帳號註冊成功，請靜候審核通知信件');
			}else{
			  $('._regist').prop('disabled',false);
			  $('#regist_submit').show();
			  $('.process').empty(); 
			  $('#regist_finish').hide();
			  
			  $.each(response.data , function(key,err){
				$('#'+key).addClass('_wrong').focus().val(err);
			  });
			  system_message_alert('',response.info)
			}  
		  },
		  complete:function(){
			document.getElementById('captcha').src=document.getElementById('captcha').src + '?' + (new Date()).getMilliseconds();
		    $('#captcha_input').val('');	
			system_loading(); 		
		  }
        });
	  });
    
	}
   
   
    // 重新輸入
    if($('#reg_act_reset').length){
      $('#reg_act_reset').click(function(){
	  
		$('input.reg_cont').val('');
		$('textarea.reg_cont').val('');
		$("input[name='user_staff']").prop('checked',false);
		$("input#user_staff_other").val('');
		$('span.reg_data').html('');
		
		// reset 驗證碼
		document.getElementById('captcha').src=document.getElementById('captcha').src + '?' + (new Date()).getMilliseconds()
		$('#captcha_input').val('');
	  });
    }
	
	
	
});  /* << end of window load >> */  
    
	
	
	
	//-- initial date picker  // 初始化datepicker
	function initial_date_picker(DomObj,isSingelDayMode){
	  
	  var date_config = $('#area_apply_date_picker_config').data('config');
	  var date_start  = $('#area_apply_date_picker_config').data('start');
	  
	  
	  DomObj.dateRangePicker({
		inline:true,
		container: '#date-range12-container',
		alwaysOpen:true,
		language:'tw',
		autoClose: false,
		customTopBar: '申請進入日期',
		autoClose: false,
		singleDate : isSingelDayMode,
		showShortcuts: true,
		startDate: new Date(date_start),
		stickyMonths :true,
		customOpenAnimation: function(cb){
		  $(this).fadeIn(300, cb);
		},
		
		beforeShowDay: function(t)
		{
		  var date_index = moment(t).format('YYYY-MM-DD');
		  
		  if(typeof date_config[date_index]!='undefined'){
			var valid = parseInt(date_config[date_index]['apply']) ? true : false;
			var _tooltip = date_config[date_index]['info'];  
		  }else{
			var valid = false;
			var _tooltip = valid ? '' : '不開放申請';
		  }
		  
		  //var valid = !(t.getDay() == 0 || t.getDay() == 6);  //disable saturday and sunday
		  //var _tooltip = valid ? '' : '不開放申請';
		  
		  var _class = '';
		  return [valid,_class,_tooltip];
		},
		separator : ' to ',
		getValue: function(){
		  if ($('#apply_date_1s').val() && $('#apply_date_1e').val() )
			return $('#apply_date_1s').val() + ' to ' + $('#apply_date_1e').val();
		  else
			return '';
		},
		setValue: function(s,s1,s2){
		  $('#apply_date_1s').val(s1);
		  if($('#apply_date_1e').is('[readonly]')){
			$('#apply_date_1e').val(s1);  
		  }else{
			$('#apply_date_1e').val(s2);    
		  }
		},
		showDateFilter: function(time, date)  
		{
	      var date_index = moment(time).format('YYYY-MM-DD');
		  var _type  = '';
		  var info = '<i class="fa fa-ban" aria-hidden="true"></i>';
		  var hint = '本日不可申請';
		  if(typeof date_config[date_index]!='undefined' && parseInt(date_config[date_index]['apply'])){
			
			info = date_config[date_index]['booked']+'人';	  
		    hint = parseInt(info) ? '本日已申請 '+info+' 人' : '本日尚未有人提出申請';
		  }
		    
			return '<div style="min-width:50px;padding:0 5px;" title="'+hint+'">\
						<span style="font-weight:bold">'+date+'</span>\
						<div style="margin-top:5px;opacity:0.3;" >'+info+'</div>\
					</div>';
		}
			
	  }).bind('datepicker-opened',function(){
		$('#date-range12-container').find('.month-wrapper').css('width','100%')		
      });	
	  	
	}
	
	//--UPLOAD ATTACHMENT & INSERT LIST
	function process_attachupload(ProcessString){
      var response = JSON.parse(ProcessString)
	  if(response.action){ 
	    var attach = $('<li/>').addClass('attachment').attr({'code':response.data.code,'file':response.data.file,'time':response.data.time});
        attach.append('<a class="option adele"><i class="fa fa-trash-o" aria-hidden="true"></i></a>');    
		attach.append('<span class="file" title="'+response.data['file']+'" >'+response.data['file']+'<br/><i class="time">'+response.data['time']+'</i></span>');    
		attach.appendTo($('#attachment_list'));
		system_message_alert('alert','附件檔案上傳成功');
	  }else{
	    system_message_alert('',response.info)
	  }
	  $('#apply_attachment_upload').val('');	
	  
    }
  
    //--UPLOAD EXCEL & INSERT MEMBER
    function process_mbrupload(ProcessString){
      var response = JSON.parse(ProcessString)
	  if(response.action){ 
	    $('#apply_member_list').attr('pnum',response.data.process.length);
        load_apply_member(response.data.process);
		system_message_alert('alert','名單上傳成功，共登記 '+response.data.process.length+' 位參加人員');
	  }else{
	    system_message_alert('',response.info)
	  }
	  $('#apply_member_upload').val('');	
    }
	
   
    // 載入成員資料
	function load_apply_member(MemberList){
	  $('#apply_member_list').empty();
	  
	  $.each(MemberList,function(i,mbr){
		var mbr_dom = $('tr.member.template').clone().removeClass('template');	
	    mbr_dom.attr('edit','1');
        mbr_dom.find('.mbr_no').text(i+1);
	    mbr_dom.find('.mbr_role').text(mbr.member_role);
	    mbr_dom.find('input').each(function(){
	      if($(this).attr('type')=='radio' || $(this).attr('type')=='checked'){
		    if($(this).val() == mbr[$(this).attr('class')]){
			  $(this).prop('checked',true);  
		    }
		  }else{
		    $(this).val(mbr[$(this).attr('class')]);	 
		  }
		  $(this).prop('readonly',false);
	    });
		mbr_dom.find('.member_sex').attr('name','P-'+i);
		
		mbr_dom.appendTo($('#apply_member_list')); 
	    
	  });
	}
 