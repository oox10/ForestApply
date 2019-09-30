  
 
  $(window).load(function () {   //  || $(document).ready(function() {
	
	
	/*================================*/
	/*--    Landing Function set    --*/
	/*================================*/
	
	
	//-- page link 
	$('#system_mark').click(function(){
	  location.href='index.php';	
	});
	
	//-- form initial 首頁使用者選單
	$('.formmode').click(function(){
	  var target_dom = $(this).data('dom')
	  $('.formblock').hide();
	  $('#'+target_dom).show();
	  $('.formmode.atthis').removeClass('atthis'); 
	  $(this).addClass('atthis');
	});
	
	if($('.formmode.atthis').length){
	  $('.formmode.atthis').trigger('click'); 
    }
	
	//-- go to recover
	$('#act_forgot').click(function(){
      $('#check_form').hide();
      $('#recover_form').show();
	});
	
	//-- go to index
	$('#act_gohome').click(function(){
      location.href='./';
	});
	
	//-- cancel 
	$('button.cancel').click(function(){
	  if($(this).attr('from')=='forgot'){
		$('#check_form,#recover_form').toggle();
	  }else{
		location.href='./';   
	  }
	});
	
	// calendar apply
	if($(".apply_entry").length){
	  $(document).on('click',"div.date_slot[type='apply']",function(){
	    if( parseInt($(this).data('quota')) <= 0 ){
		  if(!confirm('當日申請人數已超出上限，申請將會進行抽籤，確定要進行申請嗎?')){
			return false;   
		  }
	    }
		var area_code = $(this).parents('.calendar').attr('area');
	    location.href='index.php?act=Landing/reserve/'+area_code;
	  });	
	}
	
	//-- go to area booking process 
	$('#area_reserve').click(function(){
	  var area_code = $('#apply_area_sel').val();
	  if(!area_code){
		system_message_alert('','請選擇申請區域');  
	    return false;
	  }
	  location.href='index.php?act=Landing/reserve/'+area_code;	
	})
	
	
	//-- user applied search
	$('#user_applied').click(function(){
	  
	  $('.form_raw.fail').removeClass('fail');
	  var applied = {};
	  
	  if( !$('#applied_code').val() || ($('#applied_code').val().length !=8 && $('#applied_code').val().length !=9)){
		$('#applied_code').focus().parents('.form_raw').addClass('fail');
		system_message_alert('','請輸入正確的申請編號')  
	    return false;
	  }
	  if( !$('#applier_mail').val() ){
		$('#applier_mail').focus().parents('.form_raw').addClass('fail');  
		system_message_alert('','請輸入申請人的電子郵件/email')  
	    return false;
	  }
	  
	  applied.code = $('#applied_code').val();
	  applied.mail = $('#applier_mail').val();
	  
	  location.href='index.php?act=Landing/verify/'+encodeURIComponent(Base64M.encode(JSON.stringify(applied)));	
	  	
	});
	
	//-- 重新寄發申請編號
	$('#user_recover').click(function(){
	  
	  $('.form_raw.fail').removeClass('fail');
	  var applied = {};
	  
	  if( !$('#applicant_id').val() || $('#applicant_id').val().length < 8 ){
		$('#applicant_id').focus().parents('.form_raw').addClass('fail');
			system_message_alert('','請輸入正確的申請人ID')  
			return false;
	  }
	  
	  if( !$('#applicant_mail').val() ){
		$('#applicant_mail').focus().parents('.form_raw').addClass('fail');  
		system_message_alert('','請輸入申請人的電子郵件/email')  
	    return false;
	  }
	  
	  if( !$('#date_enter').val() || !$('#date_enter').val().match(/^\d{4}\-\d{2}\-\d{2}$/) ){
		$('#date_enter').focus().parents('.form_raw').addClass('fail');  
		system_message_alert('','請輸入正確的進入日期')  
	    return false;
	  }
	  
	  applied.user = $('#applicant_id').val();
	  applied.mail = $('#applicant_mail').val();
	  applied.date = $('#date_enter').val();
	  
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Landing/resend/'+encodeURIComponent(Base64M.encode(JSON.stringify(applied)))},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(!response.action){  
			  system_message_alert('',response.info);
			  return false;
			}
			$('#recover_form').find('input').val('');
			system_message_alert('alert',"已將申請序號寄到您的信箱");
			
			//$('#act_gohome').trigger('click')
			
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
	 
	  	
	});
	
	
	// reform date stop info 
	function reform_stop_day(){
	  var stop_reason = '';
	  var stop_slot_no= 0;
	  $(".date_slot[type='stop']").each(function(){
        if($(this).prev().length && $(this).prev().attr('type')=='stop' && stop_reason==$(this).data('info') ){
		  $(this).append("<span class='stop_info' title='"+$(this).data('info')+"' style='left:-"+(stop_slot_no*100-2)+"%' >"+$(this).data('info')+"</span>");
		  stop_slot_no++;
		  return true;	
		}
		$(this).append("<span class='stop_info' >"+$(this).data('info')+"</span>");
	    stop_reason  = $(this).data('info');
		stop_slot_no = 1;
		if( $(this).next().attr('type')!='stop'){
		  $(this).css('overflow','visible');	
		}
	  });	
	}
	
	//-- reinsert calendar
	function reform_calendar(areaid,calfrom,calnums){
	  $.ajax({
        url: 'index.php',
		type:'POST',
	    dataType:'json',
	    data: {act:'Landing/schedule/'+areaid+'/'+calfrom+'/'+calnums},
	    beforeSend: function(){ system_loading() },
        error: function(xhr, ajaxOptions, thrownError){ console.log( ajaxOptions+" / "+thrownError); },
	    success: function(response) {
		  //console.log(response.data);
		  
		  if(response.action){
			var calindex = 0;
            $.each(response.data.date,function(month,dates){
				
			  var calendar = $('.calendar[area="'+areaid+'"]:eq('+calindex+')');
			  calendar.find('.date_ynm').data('month',month).text(month.substr(0,4)+' 年 '+month.substr(5,2)+' 月');
			  
			  var date_container = calendar.find('.date_container');
			  date_container.empty();
			  $.each(dates,function(d,slot){
				var info_alert = '';
				info_alert = slot.type=='apply' ? '申請進入' : '不開放申請';
				info_alert = slot.type=='stop'  ? '不開放申請:'+slot.info : info_alert;
				
				var datedom = $("<div/>").addClass('date_slot').attr({"type":slot.type,"data-date":slot.date,"data-quota":slot.quota,"data-info":slot.info,'title':info_alert}).attr('select','0');
                datedom.append('<span class="date_no">'+slot.date.substr(-2,2)+'</span>');
                datedom.append('<span class="date_mask"></span>');
                datedom.append('<span class="date_quota" title="已申請人數:'+slot.booked+'">'+( parseInt(slot.booked) ? slot.booked+' 人':"0")+'</span>');			
				datedom.append('<span class="date_info"></span>');			
                datedom.appendTo(date_container);
			  });
			  calindex++;
			});
			
			$('#area_target').html($('.area_into_selecter option:selected').html())
			$('#area_load').html(response.data.info.area.area_load);
			
			reform_stop_day();
            
			$('.apply_date').each(function(){
		      var date_applied = $(this).val();
			  if(date_applied){
			    $('.date_slot[data-date="'+date_applied+'"]').attr('select',1);
		      }  
	        });
			
			// Process Area insert info
			$('.descrip > div').empty();
			var area_meta = response.data.info.area;	
			
			if( Object.keys(response.data.info.area.refer).length){
			  
			  if(response.data.info.area.refer.image){
			    $.each(response.data.info.area.refer.image,function(imgindex,imgmeta){
				  $('.descrip > .photo_container').append("<a href='photo.php?src="+areaid+'/'+imgindex+"' target=_blank ><img src='photo.php?src="+areaid+'/'+imgindex+"' /></a>");  
				  return false;
			    });
			  }	
			
			}else{  
			  var area_apply_for = []; 
			  if(area_meta.forms.application_reason.elements.length){
				$.each(area_meta.forms.application_reason.elements,function(i,reason){ area_apply_for.push(reason.name); }); 
			  }
			  
			  var dom = $("<ul/>").addClass('area_detail');
              dom.append("<li><label>區域名稱</label><div>"+area_meta.area_name+"</div></li>");  			  
			  dom.append("<li><label>受理日期範圍</label><div> 進入前"+area_meta.accept_max_day+" - "+area_meta.accept_min_day+"日</div></li>");  			  
			  dom.append("<li><label>可申請項目</label><div>"+area_apply_for.join('；')+"</div></li>");  			  
			  dom.append("<li><label>主管機關與聯繫電話</label><div>"+area_meta.master_group+' / '+area_meta.master_contect+"</div></li>");  			  
			  $('.descrip > .photo_container').append(dom);
			}
			
			// insert descrip 
			var area_introduction = area_meta.area_descrip;
			if(area_meta.area_link){
			  area_introduction+=area_introduction.length ? '&#187; <a href="'+area_meta.area_link+'" target=_blank >區域介紹</a>' : '區域介紹 :'+'<a href="'+area_meta.area_link+'" target=_blank >'+area_meta.area_link+'</a>';
			}
			$('.descrip > .area_descrip').html(area_introduction);
			
		  }else{
		    system_message_alert('error',response.info);
		  }
		},
		complete:		function(){ }
	  }).done(function(r) { system_loading() });		
	}
	
	// change apply calendar
	$('.date_change').click(function(){
      var main = $(this).parents('.calendar');	 
	  var mode = $(this).attr('mode');
	  var areaid  = main.attr('area');
	  var calnow  = main.find('.date_ynm').data('month');
	  var calnext = (mode=='+') ? moment(calnow+'-01').add(1, 'month').format('YYYY-MM') : moment(calnow+'-01').add(-1, 'month').format('YYYY-MM');  
	  var calnums = 0 + parseInt(mode+($(".calendar[area='"+areaid+"']").length));
	  
	  if(!areaid.length){
		system_message_alert('','尚未選擇區域');  
	    return false;
	  }
	  reform_calendar(areaid,calnext,calnums);
	});
	
	//-- landing form select area 
	$('#area_type_sel').change(function(){
      var area_type = $(this).val();
	  if(!area_type){ return false;   }
      $('#apply_area_sel').find('optgroup,option').show();
	  $("optgroup[label!='"+area_type+"']").children().hide().end().hide();
	  $('#apply_area_sel').trigger('mousedown');
	});
	
	
	// area info change 
	$('.area_into_selecter').change(function(){
	  var main = $(this).parents('.calendar');	 
      main.attr('area',$(this).val());       
	  var calfrom = '_now'; 	  
	  var calnums = 1;
	  var areaid  = $(this).val();
	  
	  if(!areaid.length){
		system_message_alert('','尚未選擇區域');  
	    return false;
	  }
	  reform_calendar(areaid,calfrom,calnums);
	});
	
	//-- area descrip function // 區域資訊 
	if($('.area_type_selecter').length){
      $('.area_type_selecter').each(function(){
		var area_type = $(this).val();  
        var area_list_sel = $(this).next();
		area_list_sel.find("optgroup[label='"+area_type+"']").children('option:nth-child(1)').prop('selected',true);
		area_list_sel.trigger('change');
	  }); 

      $('.area_type_selecter').change(function(){
	    $(this).next().val('').focus();	
	  });
	}
	
	/*===============================*/
	/*-- Announcement Function set --*/
	/*===============================*/
	
	$('#act_switch_post_mode').click(function(){
	  var more_flag = parseInt($('.billboard').attr('more'));	  
      $('.billboard').attr('more',parseInt(1 - more_flag));
	});
	
	
	
	$('.post').click(function(){
	  var dom = $(this);
	  var data_no = dom.attr('no');
	  
	  if(!parseInt(data_no)){
		system_message_alert('',"尚未選擇資料");  
	    return false;  
	  }
	  
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Landing/getann/'+data_no},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  $(this).addClass('viewed');	
	          
			  var post = response.data;
			  
			  $('.ann_type').text(post.post_type);
			  $('.ann_title').text(post.post_title);
			  $('.ann_contents').html(Base64.decode(post.post_content));
			  $('.ann_time').text(post.post_time_start);
			  $('.ann_from').text(post.post_from);
			  $('.ann_counter').text(post.post_hits);
			  
			  $('.system_announcement_area').css('display','block');	
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
	  
	});
	
	$('.ann_close').click(function(){
	  $('.viewed').removeClass('viewed');
	  $('.system_announcement_area').css('display','none');	
	});
	  
	//-- post emergency popout
	if( $(".post[popout='1']").length ){
	  $(".post[popout='1']:eq(0)").trigger('click');	
	}
	
	
	
	/*================================*/
	/*--    Regist Function set    --*/
	/*================================*/
	
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
	
	
  }); /* << end of window load >> */    
  
 