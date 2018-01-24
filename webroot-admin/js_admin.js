/*    
  
  javascrip use jquery 
  rcdh 10 javascript pattenr rules v1
  
*/
 
  /**************************************   
	    TLCDA-Archive Admin System     
  **************************************/
	
  var data_orl = {};	
	
	
  $(window).on('load',function () {   //  || $(document).ready(function() {	
	
	
	/* [ System Work Function Set ] */
	
	// manual display switch
	
	$('.system_mark').click(function(){
	  if($('.system_content_area').hasClass('wide_mode')){
		$('.system_content_area').removeClass('wide_mode');  
	  }else{
		$('.system_content_area').addClass('wide_mode');  
	  }
	  $(window).trigger('resize');
	});
	
	//-- page initial alert
	if($('.msg_info').html()){
	  system_message_alert('','');
	}
	
	//-- admin manual action navigation
	$('.func_activate').click(function(){
	  if($(this).hasClass('func_undo')){
	    alert("尚未開放");
	  }else{
	    location.href='index.php?act='+$(this).attr('id');
	  }
	});
	
	//-- system manual heightline
	var act_name = location.search.replace(/^\?act=/,'');
	var reference = act_name.split('/');
	
	var module_index = [];
	
	for(var i=0 ; i< reference.length ; i++){
	  module_index.push(reference[i]);
      var target_dom = $('.func_activate[id="'+module_index.join('/')+'"]');
	  if(target_dom.length){
	    target_dom.addClass('inthis');	
	    break;
	  }	  	
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
	
	
	//-- 系統介面排版設定
	resite_form_area();
	$(window).resize(function() {
	  resite_form_area();			
	});
	
	
	// 設定填寫版面排版
	function resite_form_area(){
	  if( $('.data_record_block').length ){	  
	    if( $('.data_record_block').width() < 1050 ){
		  $('.float_cell').removeClass('col2').addClass('col1');  
	    }else{
		  $('.float_cell').removeClass('col1').addClass('col2');  	
		}
	  }
	}
	
    
	
	/* [ Admin Account Function Set ] */
	
	//-- 系統帳號相關功能
	
	// 帳號功能版面開關 
	$('.account_info').click(function(){
	  if($('.account_control').length){
		$('.account_control').toggle();   
	  }		
	});
	
	
	// 群組變換
	$('#acc_group_select').change(function(){
	  
	  if(!$(this).val() || !$(this).find("option[value='"+$(this).val()+"']").length ){
		system_message_alert('error','錯誤的群組');
	    return false;
	  }
	  
	  var group_code = $(this).val();
	  
      $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Admin/gpswitch/'+group_code},
		  beforeSend: 	function(){ system_loading();},
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
			
			if(response.action){
			  location.hash = '';	
              location.reload(true);
			}else{
			  system_message_alert('',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() {  });   
	});
	
	
	
	// 登出
	$('#acc_logout').click(function(){
	  location.href = 'index.php?act=Account/logout' 	;  
	});
	
	
	
	
	
	/* [System Breadcrumbs] 系統麵包屑 */
	
	// location.pathname  // /MtDoc/editer/Project/DocNo
	
    var breadcrumbs = {};    
	var action_mode = 'get';
	function built_system_Breadcrumbs(){
		
	  var system_location = location.search;
	  var action_level    = system_location.replace(/\?act=/,'').split('/');
  	  $Index = $('<li/>').addClass('breadcrumb').append("<a href='index.php?act=Main'>首頁</a>");
	  $("#system_breadcrumbs").empty().append($Index);
	  
	  var module = [];
	  
	  $.each(action_level,function(i,act){
		module.push(act);
		
		if(act == 'Main' ){ 	  
		  return true;
		}
		  
		if($('[id="'+module.join('/')+'"]').length){
		  var dom = $('[id="'+module.join('/')+'"]');
		  var $crumb = $Index.clone();
		  $crumb.children('a').attr('href' , 'index.php?act='+module.join('/')).html(dom.attr('title'));
	      $crumb.appendTo("#system_breadcrumbs");
		}  
		  
	  });
	  
	  if($('#search_breadcrumbs').length){
		var search_queue = JSON.parse($('#search_breadcrumbs').html());
	    $.each(search_queue,function(i,act){
		  var $crumb = $Index.clone(); 
		  $crumb.children('a').attr('href' , act['link']).html(act['type']+':'+ act['term']+' ('+act['result']+')' );
	      $crumb.appendTo("#system_breadcrumbs");  
		});
	  }
	  
	}
	
	if($('#system_breadcrumbs').length){
	  built_system_Breadcrumbs();
	}
	
	
	//-- 麵包屑綁定回到上一頁標示
	window.addEventListener('popstate', function(event){
	  if(document.location.hash.match(/^#.+/)){
	    $target = $("td[field='user_id']:contains("+location.hash.replace(/^#/,'')+")").parents('tr._data_read ');
        if(!$target.hasClass( '_target' )){
		  $target.trigger('click');  
	    }
	  }else if(document.location.hash=='#' || document.location.hash==''){
	    $('._return_list').trigger('click'); 	
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
	      data: {act:'Tracking/submit/'+system_name+'/'+encodeURIComponent(JSON.stringify(feedback_info))},
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
	
	/*--------------------------------------------------------------------------------------------------------*/
	/*--------------------------------------------------------------------------------------------------------*/
	
	
	
	
	/* [ Admin Work Function Set ] */
	
	
	//-- check editor value modify
	
	$('._variable').on('keyup change blur',function(){
	  
	  var field_name = $(this).attr('id');
	  var form_value = $(this).val();
	  
	  if(typeof data_orl[field_name] == 'undefined' ){
		data_orl[field_name] = '';  
	  }
	  
	  if( data_orl[field_name] !== form_value ){
	    $(this).addClass('_modify');    
	  }else{
	    if($(this).hasClass('_modify')){
		  $(this).removeClass('_modify');    
		}
	  }
	});
	
	
	
	//-- return to list
	$(document).on('click','._return_list',function(){
	  if($('._return_form').length){
		$('#editor_backto').trigger('click');  
	  }
	  $('#editor_reform').trigger('click');
	});
	
	//-- return to form
	$(document).on('click','._return_form',function(){
	  $('#editor_backto').trigger('click');
	});
	
	
	
	//-- 資料頁面開關
	$('.view_switch').click(function(){
	  var target_block = $(this).parents('div.record_header').next();
	  if( target_block.is(":visible")){
	    $(this).html(' + ');
	  }else{
		$(this).html(' − ');
	  }
	  target_block.toggle();
	});
	
	
	//-- 關閉編輯頁面
	$('#editor_reform').click(function(){
	  
	  if($('._modify').length){
	    if( !confirm("確定要放棄變更資料?") ){
		  return false;
		}
	  }
	  initial_record_editer();
	  $('._reset').val('');  // 動作重設
	  $('._target').removeClass('_target'); // 移除目標資料標註
	  $('._relative').empty(); // 關連資料需清空
	  
	  $('#record_selecter').find('.record_list').children('.data_target').empty().hide();
	  $('#record_selecter').find('.record_list').children('.data_result').show();
	  $('#record_selecter').find('.record_control').show();
	  
	  var target_block = $(this).parents('div.record_header').next();
	  if(target_block.is(":visible")){
        $('#editor_switch').trigger('click');
	  }
	  active_header_footprint_option('record_selecter','','_return_list');
	  if($("tr.data_record[no='_addnew']").length){ $("tr.data_record[no='_addnew']").remove(); }
	  
	  location.hash = '';
	  
	});
	
	
	/* { GROUP } Data Split 、 Select & Page Tap */
	
	//-- 資料顯示列數切換資料與分頁
	$('.record_view').change(function(){
	  record_pager_initial($('tr.data_record'),$(this));
	  record_pager_builter($('tr.data_record'),1);
	});
	
	
	//-- 資料跳頁
	$(document).on('click','.page_to',function(){
	  
	  var page_to  = $(this).attr('page');
      
	  if(!$('#page_selecter').length){
	    var page_now = parseInt($('.page_now').attr('page'));
		var max_page  = $('.page_select > a.page_tap:nth-last-child(1)').attr('page');
      }else{
	    var page_now = parseInt($('#page_selecter > option:selected').attr('page'));
		var max_page  = $('#page_selecter > option:nth-last-child(1)').attr('page');  
	  }
	  
	  switch(page_to){
		case 'prev':  var new_page = (page_now-1) > 0 ? page_now-1 : page_now  ;  break;
		case 'next':  var new_page = ((page_now+1) >= max_page) ? max_page : page_now+1 ;  break;
        default:  new_page = page_to;  break; 		
	  }
	  record_pager_builter( $('tr.data_record') , new_page);
	
	});
	
	//-- 資料跳頁
	$(document).on('change','#page_selecter',function(){
	  
	  var page_to  = $(this).val();
      var page_now = parseInt($('#page_selecter > option:selected').attr('page'));
	  var max_page  = $('#page_selecter > option:nth-last-child(1)').attr('page');  
	  
	  switch(page_to){
		case 'prev':  var new_page = (page_now-1) > 0 ? page_now-1 : page_now  ;  break;
		case 'next':  var new_page = ((page_now+1) >= max_page) ? max_page : page_now+1 ;  break;
        default:  new_page = page_to;  break; 		
	  }
	  record_pager_builter( $('tr.data_record') , new_page);
	
	});
	
	
	//-- 資料搜尋 Step 1
    var search_buff  = '';
	var display_data = {};
	$('.search_input').keydown(function(){
	  search_buff = $('.search_input').val();
	  
	  // 儲存原始分頁變數
	  if( !Object.keys(display_data).length ){
		display_data.page_num =  parseInt($('.page_now').attr('page'));
		display_data.row_num  =  $('.record_view').val();
	  }
	});
	
	//-- 資料搜尋 Step 2
	$('.search_input').keyup(function(){
	  	
	  if( $('.search_input').val() !=''  &&  $('.search_input').val() !='＿' ){
	    
		$("tbody.data_result").attr('mode','search');
		
		if(search_buff != $('.search_input').val()){  //搜尋內容有改變才需要執行
		  
		  // 搜尋資料,並顯示資料列
		  var search_term = $('.search_input').val();
		  
		  $('tr.data_record').filter(function(index){
		    if($(this).find("td:contains('"+search_term+"')" ).length){
		      $(this).find("td:contains('"+search_term+"')" ).addClass('search_hits');  	
		      return true;
		    }
	      }).addClass('search_hits');    
	      
		  // 重新設定資料分頁
		  $('.record_view').val('all').trigger('change');
		}
	  }else{
		
		$("tbody.data_result").attr('mode','list');
		$('.search_hits').removeClass('search_hits'); // 移除搜尋標記
		
		// 重新設定分頁顯示
		$('.record_view').val(display_data.row_num);
		record_pager_initial($('tr.data_record'),$('.record_view'));
	    record_pager_builter($('tr.data_record'),display_data.page_num);
		
		/*
		var display_row_num = display_data.row_num =='all' ? $('tr.data_record').length : display_data.row_num;
		record_table_display( display_row_num , $('tr.data_record').filter(':visible').length , display_data.page_num ) ; 
	    record_page_built( display_row_num , $('tr.data_record').filter(':visible').length , display_data.page_num);
		$('.record_view').val(display_data.row_num);    // 還原分頁設定 
        */
		
		// 清空分頁設定變數
		display_data = {};
	  }
	  
	});
	
	
	
	
	//-- 依據當前顯示資料設定分頁
	// [input] DomSet : jquery dom // 要分頁的物件，預設為  $(tr.data_record)
	// [input] PageSeter : jquery dom // 設定分頁的項目，預設為  $(".record_view")
	
	function record_pager_initial( DomSet  , PageSeter ){
	  DomSet.attr('page','').css('display','');
	  var records_row_num  = DomSet.filter(':visible').length;           // 需分頁資料總數
	  var display_row_num =  PageSeter.length ? PageSeter.val() : 'all'; // 每頁資料數   
	  
	  if(display_row_num!='all' && parseInt(display_row_num) ){
		DomSet.filter(':visible').each(function(i){
		  $(this).attr('page' , parseInt( i/display_row_num )+1	); 	
		  $(this).find("td[field='no']").html((i+1)+'.');
		});
	  }else{
		DomSet.filter(':visible').attr('page',1);
	  }
	  
	  if($('#records_display_count').length){
		$('#records_display_count').html(records_row_num);  
	  }
	  
	  return records_row_num;
	}
	
	//-- 依據每頁資料數量建置分頁
	function record_pager_builter( DomSet , PageNow){
	  $('.page_select').empty();
	  	  
	  var page_max         = 0;
	  var record_counter   = 0;
	  
	  var display_str      = DomSet.length;           // 需分頁資料總數
	  var display_end 	   = 0;
	  
	  // 切換顯示資料
	  DomSet.each(function(i){
		var page_num = parseInt($(this).attr('page'));
		if(page_num){
		  record_counter++;
		  if( page_num == parseInt(PageNow)){  
		    $(this).css('display','table-row');
			display_str = Math.min(record_counter,display_str);
			display_end = Math.max(record_counter,display_end);
		  }else{
		    $(this).css('display','none');
		  }
		  page_max = page_num;
		}
	  });
	  
      // 建構分頁籤
	  var total_page = page_max;
	  if(total_page <= 15){
		for(var p=1 ; p<=total_page ; p++ ){
		  var page_class = (p==PageNow) ? 'page_tap page_now' : 'page_tap page_to';
		  $('<a>').addClass(page_class).attr('page',p).html(p).appendTo( ".page_select" );
        }  
	  }else{
		var pager = $('<select/>').addClass('page_jump').attr('id','page_selecter');		
		for(var p=1 ; p<=total_page ; p++ ){
		  var selected = (p==PageNow) ? true : false;
		  $('<option/>').attr('page',p).val(p).html('P.'+p).prop('selected',selected).appendTo( pager );
		}   
		pager.appendTo( ".page_select" );  
	  }
	  
	  // 標示資料
	  $('#records_display_start').html(display_str);
	  $('#records_display_end').html(display_end);
	}
	
  
  });  //-- end of initial --//
  
  
  /* [ System Trival Function Set ] */
  
  //-- 加入回家選項
  function active_header_footprint_option(domid,target_name,return_to){  
	  if($('#'+domid).length){
		var $target = $('#'+domid).find('.record_header').children('.record_name');
		if( !target_name || $target.hasClass("_return_list") || $target.hasClass("_return_form")){  
		  // 移除 header return 選項
		   $target.removeClass(return_to).next(".record_target").remove();
		}else{
		  // 加入 header return 選項
		  $DOM = $("<span/>").addClass('record_target').html(target_name);
          $target.addClass(return_to).after($DOM);
		}
	  }
  }
  

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
  
  //-- 重新設定編及區域
	function initial_record_editer(){
	  
	  // 數位物件需先停止下載
	  if($('video').length){
		window.stop(); 
		$('video').attr('src','');
	    $('video').load();
	  }
	  
	  $('._variable').each(function(){
		
		var tagDom = $(this)[0].tagName;
		
		if( tagDom == 'INPUT' && ( $(this).attr('type')=='checkbox' || $(this).attr('type')=='radio' ) ){
		  $(this).prop('checked',false);	
		}else if( tagDom=='INPUT' || tagDom=="TEXTAREA" || tagDom=='SELECT' ){
		  if( typeof $(this).data('default')  !='undefined' ){
			$(this).val($(this).data('default'));  
		  }else{
			$(this).val('');  
		  }
		}else{
		  $(this).html('');
		}
		
		$(this).removeClass("_modify").prop('disabled',false);
		
		if($(this).attr('default')=='readonly'){
		  $(this).prop('readonly',true);
		}
	    
		// 編輯器
		if($(this).data('froala.editor')){
		  $(this).froalaEditor('html.set','');	 
		}
	
	  });	
	};
  
  
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
  
  
  //-- action loading # Action botton Loading 
  function active_loading( domObject , status){
	if( ! domObject.prev().hasClass('_actprocess') ){
	   var processing = domObject.attr('id')+'_activating';
	   $DOM = $("<span/>").addClass('_actprocess').attr('id',processing).css('padding','0 10px');
	   domObject.before($DOM);
	}else{
	  var processing = domObject.prev().attr('id');
	}	
	switch(status){
	  case false   : case 0   : domObject.prop('disabled',false);  $('#'+processing).html("<img src='theme/image/act_mark_fail.png' />").finish().animate({'opacity':0},4000,function(){  $('._actprocess').remove(); }); break;
	  case true    : case 1   : domObject.prop('disabled',false);  $('#'+processing).html("<img src='theme/image/act_mark_done.png' />").finish().animate({'opacity':0},4000,function(){  $('._actprocess').remove(); }); break;
	  case 'initial': $('#'+processing).html("<img src='theme/image/act_mark_process.gif' />"); domObject.prop('disabled','true'); break; 
	}
  }
  
  
  //顯示倒數秒收
  function showTime()
  {  
	waitTime -= 1;
    document.getElementById('wait_time').innerHTML= waitTime;
    
    if(waitTime==0)
    {
      location.href='index.php';
    }
    //每秒執行一次,showTime()
    setTimeout("showTime()",1000);
  }
  
  
  //-- sse event regist
  var TaskEvent = {};
  function system_event_regist(even_type,task_no){
	  
	if(!parseInt(task_no)){
	  return false;	
	}
	
	TaskEvent[task_no] = new EventSource("event.php?task="+task_no);
    TaskEvent[task_no].onmessage = function(event) { console.log(event.data); };
    
	switch(even_type){
	  case 'package':
        system_event_alert({"task":task_no, "info":"資料匯出打包中.."},'load');
		TaskEvent[task_no].addEventListener('_PROCESSING', function(e) {
		  var data = JSON.parse(e.data);
          $("li[task='"+data.task+"']").find('.progress').html(data.progress);
        }, false);
	    
		TaskEvent[task_no].addEventListener('_PHO_EXPORT', function(e) {
	      var data = JSON.parse(e.data);
		  system_event_alert(data,'link');
		  TaskEvent[task_no].close();
        }, false);
	    
		break;
	  
	  case 'import':
        system_event_alert({"task":task_no, "info":"資料上傳已經完成，檔案轉置中 "},'load');
		TaskEvent[task_no].addEventListener('_PROCESSING', function(e) {
	      var data = JSON.parse(e.data);
          $("li[task='"+data.task+"']").find('.progress').html(data.progress);
		}, false);
	    
		TaskEvent[task_no].addEventListener('_PHO_IMPORT', function(e) {
	      var data = JSON.parse(e.data);
		  system_event_alert(data,'done');
		  TaskEvent[task_no].close();
        }, false);
	    
		break;
	  default:break;	  
	}
	
	
  }
  
  
  //-- alert event message
  function system_event_alert(data,type){
	type = type!='' ? type : 'alert'; 
	
	var DOM = $("<li/>");
	switch(type){
	  case 'load'  : DOM.attr('task',data.task).html(data.info+" <span class='progress'>0 / 0</span>"); break;
	  case 'link'  : DOM.addClass('download_link').attr('data-href',data.href).html("資料打包完成，請點選下載 ("+data.count+")"); break;  
	  case 'done'  : DOM.html("上載資料已匯入系統 ("+data.count+")"); break;  
	  default: DOM.html(data.info); break;
	}
	$('#task_info').find('li').hide().end().prepend(DOM);
  }
  
   
	//-- 若網頁內容更換，則將所有object load 停止
	$(window).bind('beforeunload', function() {
	  if($('video').length){
		$('video').attr('src','');
	    $('video').load();
	    window.stop();  
	  }
	});
	
	
	
	
  