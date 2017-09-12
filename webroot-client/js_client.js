/*     
  javascrip use jquery 
  rcdh 10 javascript pattenr rules v1
*/
 
  var data_orl = {};	
	
  $(window).load(function () {   //  || $(document).ready(function() {	
	
	
	/* [ System Work Function Set ] */
	
	//-- page initial alert
	if($('.msg_info').html()){
	  system_message_alert('','');
	}
	
	//-- 系統loading 版面設定
	if(!$('.system_loading_area').length){  
	  var SysLoader = $('<div/>').addClass('system_loading_area');
	  var loadBlock = $('<div/>').addClass('loading_block');
	  $('<div/>').addClass('loading_string').html('系統處理中').appendTo(loadBlock);
	  $('<div/>').addClass('loading_image').attr('id','sysloader').appendTo(loadBlock);
	 // $('<div/>').addClass('loading_info').html('如果系統過久無回應，請按[ Esc ] 關閉 loading 版面，並重新操作剛才的動作.').appendTo(loadBlock);
	  SysLoader.append(loadBlock);
	  $('body').append(SysLoader);	  
	}
	  sysloading = new CanvasLoader('sysloader');
      sysloading.setColor('#449dc7'); // default is '#000000'
      sysloading.setShape('spiral'); // default is 'oval'
      sysloading.setDiameter(30); // default is 40
      sysloading.setDensity(30); // default is 40
      sysloading.setRange(0.7); // default is 1.3
      sysloading.setFPS(20); // default is 24
      sysloading.hide(); // Hidden by default
	
	
	//-- 系統Loading 沒反應之處理
	function cancel_pre_action(){
      if(window.stop !== undefined){
        window.stop();
      }else if(document.execCommand !== undefined){
       document.execCommand("Stop", false);
      }
    }
    
	//-- cancel system loading
	$(document).keydown(function(event){
	  if(event.keyCode==27){
	    if($('.system_loading_area').is(':visible')){
		  cancel_pre_action();
		  system_loading();
		}
	  }	
	});
	
	
    /* [System Breadcrumbs] 錯誤回報 */
    
    //-- feedback area initial
	$('#user_feedback').click(function(){
      $('.system_feedback_area').show('slow',function(){
	    $(".feedback_area_sel[value='system_body_block']").trigger('click');
	  });  
    });
	
    //-- report function open/close
	if($('.system_feedback_area').length){
	  $('.system_feedback_area').draggable({ disabled: false });
	}
	
	/*  Admin feedback Function Set */
	
	//-- reset feedback
	function feedback_reset(){
	  $('.fb_preview').empty();
	  $('input.feedback_area_sel:checked').prop('checked',false);
	  $('input.feedback_type:checked').prop('checked',false);
	  $('input.fbd_type_other , textarea.feedback_content').val('');
	}
	
	//-- feedback area close
	$('#act_feedback_close').click(function(){
	  $('.system_feedback_area').hide('slow',feedback_reset());
	});
	
	
	//-- feedback image area select
	$('.feedback_area_sel').click(function(){
	  $('#feedback_img_upload').val('');
	  $('.fb_preview').empty();
	  $('.fb_imgload').show();
	  var area_name = $(this).val();
	  html2canvas($('.system_main_area'), {   
        onrendered: function(canvas){
          var img = canvas.toDataURL();
		  $('.fb_preview').append("<img src='"+img+"' class='fp_img'>").prev().hide();  
		}
      });
	});
	
	
	//-- feedback image upload
	
	$('#feedback_img_upload').click(function(){
	  $('input.feedback_upload_sel').prop('checked',true);
	  $('.fb_preview').empty();
	}).change(function(event){
	
	  if(event.target.files.length > 0 && $(this).val().match(/(jpg|gif|png)$/) ){	    
		$('.fb_imgload').show();
		var file   = event.target.files[0];
		var reader = new FileReader();
		reader.onload = (function () {
		  $('.fb_preview').append("<img src='"+this.result+"' class='fp_img'>").prev().hide();
		});
		reader.readAsDataURL(file);
	  }else{
	    $(this).val('');
		alert("格式錯誤，請上傳影像檔");
	  }
	});

	
	//-- feedback type selecter
	$('input.feedback_type').click(function(){  
	  var fbtype = $(this).val();
	  var new_fbcont = '';
	  if( $(this).prop('checked')  &&   !$('textarea.feedback_content').val().match(fbtype)){
		if($('textarea.feedback_content').val() ){
		  
		  if($('input.feedback_type:checked').length==1){
		    new_fbcont  = "["+fbtype+"]:\n"+$('textarea.feedback_content').val();
		  }else{
		    new_fbcont  = $('textarea.feedback_content').val()+"\n"+"["+fbtype+"]:\n";
		  }
		}else{
		  new_fbcont = "["+fbtype+"]:\n";
		}
		$('textarea.feedback_content').val(new_fbcont);
	  }
	});
	
	//-- feedback cancal
	$('#act_feedback_cancel').click(function(){
	  if(confirm(" 確定要放棄回報內容 ? ")){
        feedback_reset();
	  }
	});
	
	
	//-- feedback submit
	
	$('#act_feedback_submit').click(function(){
	  var system_name = $('title').length ? $('title').html() : $(location).attr('hostname');
	  if(!$('input.feedback_type:checked').length){
		alert("請選擇回報類型"); 		
	  }else{  
		var feedback_info = {};
	    feedback_info.url = document.URL;
	    feedback_info.preview = $('img.fp_img').attr('src');     	
	    feedback_info.type 	= $('input.feedback_type:checked').map(function(){ return ($(this).val()=='其他') ? '其他:'+$('input.fbd_type_other').val() : $(this).val(); }).get().join(','); 
        feedback_info.content = $('textarea.feedback_content').val();
	  
		$.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Report/submit/'+system_name+'/'+encodeURIComponent(JSON.stringify(feedback_info))},
		  beforeSend: 	function(){ system_loading();},
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
			if(response.action){
			  $('#system_message').html("回報送出，請靜待處理").dialog({title:"System Info" });
			  $('#act_feedback_close').trigger('click');
			}else{
			  system_message_alert('',response.info);
			}
	      },
		  complete:	function(){ }
		}).done(function() {  
		  system_loading();
		});        
	  }	
	});
	
  
  });  //-- end of initial --//
  
 
  /* [ System Work Function Set ] */
	
  //-- message alert 
  function system_message_alert(mtype,messg){
    
	mtype = mtype ? mtype : 'error';
	
	$('.system_message_area').finish();
	
	if($('#message_container').length){
	  if(messg){
	    $('#message_container').children('.msg_title').html(messg);
	    $('#message_container').children('.msg_info').html('');
	  }
	}
	
    $('.system_message_area').addClass(mtype).finish().show().delay(2000).animate({opacity:'0'},2000,function(){ 
	  $(this).hide().css('opacity','0.8').removeClass(mtype);
	  $('#message_container').children('.msg_title').html('');
	  $('#message_container').children('.msg_info').html('');
	});
	
  }
  
  //-- System loading   # All Page Mask Loading
  function system_loading(){
    var display='show';
	clearTimeout($.data(this, 'timer'));
	if($('.system_loading_area').is(':visible')){
	  sysloading.hide();
	  $('.loading_info').css('opacity','0');
	  display = 'none';
	}else{
	  sysloading.show();
	  display = 'block';
	  $.data(this, 'timer', setTimeout(function() {
        $('.loading_info').animate({opacity:'1'},1000);
      }, 8000));
	}	
	$('.system_loading_area').css('display',display);
  }

