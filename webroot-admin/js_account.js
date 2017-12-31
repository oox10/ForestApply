  
 
  $(window).load(function () {   //  || $(document).ready(function() {
	
	//-- go to regist
	$('#act_register').click(function(){
	  location.href='./index.php?act=Account/regist';	
	});
	
	//-- go to login
	$('#act_login').click(function(){
	  location.href='./index.php?act=Account';	
	});
	
	//-- go to forgot
	$('#act_forgot').click(function(){
	  location.href='./index.php?act=Account/forgot';		  
	});
	
	//-- reset keyin
	$('#act_reset').click(function(){
	  $('._keyin').val('');
	  location.href='./index.php?act=Account';
	});
	
	
	//-- cancel button
	$('#act_cancel').click(function(){
	  var input_check = false;
	  
	  $('._regist').each(function(){
		if( $(this).val() ){
			input_check = true;
			return false;
		}  
	  });
	  
	  if(input_check){
		if(confirm("確定要取消註冊，這將會清空目前輸入的資料!")){
		  $('._regist').val('');
		}else{
		  return false;
		}  
	  }
      location.href='./index.php?act=Account';
	});
	
	
	//-- admin login 
	$('#act_signin').click(function(){

	  // initial
	  $('input.lg_text').removeClass('form_error');
	  
	  // check input 
	  if(!$('#uname').val()){
	    $('#uname').addClass('form_error').focus();
		system_message_alert('error','請填寫帳號');
		return false;
	  }
	  
	  if(!$('#upass').val()){
	    $('#upass').addClass('form_error').focus();
		system_message_alert('error','請填寫密碼');
		return false;
	  }
	  
	  var login_info = {};
	  login_info['account']  = $('#uname').val();
	  login_info['password'] = $('#upass').val();
	  
	  var login_data = encodeURIComponent(Base64.encode(JSON.stringify(login_info)));
	 
	  $.ajax({
        url: 'index.php',
		type:'POST',
	    dataType:'json',
	    data: {act:'Account/signin/'+login_data},
	    beforeSend: function(){ system_loading(); },
        error: function(xhr, ajaxOptions, thrownError){ console.log( ajaxOptions+" / "+thrownError); },
	    success: function(response) { 
		  if(!response.action){ 
		    system_message_alert('error',response.info);
		    $('#upass').val('').focus();
			system_loading();
		    return 0
		  }
		  if(typeof response.data.repass != 'undefined' ){
			location.href='index.php?act=Account/start/'+response.data.repass;
		    return 1;	
		  }else if( typeof response.data.lgkey != 'undefined' ){
			location.href='index.php?act=Account/inter/'+response.data.lgkey;
			return 1;	
		  }else{
			system_message_alert('error','未知錯誤，請洽管理人員');
		  }
		  
		},
		complete:		function(){ }
	  }).done(function() {   });
	});
	
	
	//-- client reset password submit
	$('#act_repass').click(function(){
	  
	  // initial
	  $('input._keyin').removeClass('form_error');
	  
	  // check input 
	  if(!$('#upass').val()){
	    $('#upass').addClass('form_error').focus();
		system_message_alert('error',"請輸入密碼");
		return false;
	  }
	  
	  if( $('#upass_chk').val() != $('#upass').val()  ){
	    $('#upass_chk').addClass('form_error').val('').focus();
		system_message_alert('error',"2次輸入的密碼不相符，請重新輸入");
		return false;
	  }
	  
	  var register_info = {};
	  register_info['regist_password01'] = $('#upass').val();
	  register_info['regist_password02'] = $('#upass_chk').val();
	  
	  var $dom = $(this);
	  var pass_data = encodeURIComponent(Base64.encode(JSON.stringify(register_info)));
	  
	  $.ajax({
        url: 'index.php',
		type:'POST',
	    dataType:'json',
	    data: {act:'Account/repass/'+pass_data},
	    beforeSend: function(){ active_loading($dom,'initial'); $('._keyin').prop('readonly',true);  },
        error: function(xhr, ajaxOptions, thrownError){ console.log( ajaxOptions+" / "+thrownError); },
	    success: function(response) {
		  if(response.action){
			$('._keyin,.signin_func').hide();
			system_message_alert('alert','帳號已啟動,請重新登入');
		  }else{
		    system_message_alert('error',response.info);
		  }
		  $('input._keyin').val('');
		},
		complete:		function(){ }
	  }).done(function(r) { active_loading($dom,r.action); });
	});
	
	
	
	
	
	//-- client active password reset mail
	$('#act_recover').click(function(){
	  
	  // initial
	  $('input._keyin').removeClass('form_error');
	  
	  
	  if( !$('#userid').val() ){
	    $('#userid').addClass('form_error').val('').focus();
		system_message_alert('error',"請輸入註冊帳號");
		return false;
	  }
	  
	  // check input 
	  if(!$('#email').val()){
	    $('#email').addClass('form_error').focus();
		system_message_alert('error',"請輸入註冊信箱");
		return false;
	  }
	  
	  
	  
	  if(!$('#captcha_input').val()){
	    $('#captcha_input').addClass('form_error').focus();
		system_message_alert('error',"請輸入驗證碼");
		return false;
	  }
	  
	  var register_info = {};
	  register_info['regist_email']   = $('#email').val();
	  register_info['regist_account'] = $('#userid').val();
	  register_info['verification'] = $('#captcha_input').val();
	  
	  var pass_data = encodeURIComponent(Base64.encode(JSON.stringify(register_info)));
	  var $dom = $(this);
	 
	  $.ajax({
        url: 'index.php',
		type:'POST',
	    dataType:'json',
	    data: {act:'Account/reseter/'+pass_data},
	    beforeSend: function(){ active_loading($dom,'initial'); $('._keyin').prop('readonly',true);},
        error: function(xhr, ajaxOptions, thrownError){ console.log( ajaxOptions+" / "+thrownError); },
	    success: function(response) {
		  if(response.action){
			system_message_alert('alert','已發送密碼重設信件');
		    $('.signin_func').hide();
		  }else{
		    system_message_alert('error',response.info);
			$('#captcha_refresh').trigger('click');
		    $('#captcha_input').val('');
			$('input._keyin').prop('readonly',false);
		  }
		},
		complete:		function(){ }
	  }).done(function(r) { active_loading($dom,r.action); });
	});
	
	
	
	/**********************
	===  register.html  ===   
	**********************/
	
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
			case 'user_idno'	: reg_check[$(this).attr('id')]= $(this).val(); break;
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
			if( !reg_check[$(this).attr('id')] ){
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
	
	
  });  

 