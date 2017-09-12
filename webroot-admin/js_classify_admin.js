/* [ Admin Classify Function Set ] */
	
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
	  
	  
		$.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Report/read/'+report_no},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
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
	
	//-- classify term(tag folder level) edit option
	$('.act_term_edit').click(function(){
	  if($(this).parents('.data_list').attr('no')){
		$(this).parents('.data_list').find('.term_edit').prop('contenteditable',true).focus(); 
        $(this).next().css('display','inline-block').end().hide();		
	  }	
	});
	
	//-- classify term(tag folder level) save option
	$('.act_term_save').click(function(){
      var term_id     = $(this).parents('.data_list').attr('no');
	  var term_dom    = $(this).parents('.data_list').find('.term_edit');
	  var new_term    = term_dom.text(); 		
	  if(term_dom.text() != term_dom.data('term')){
		
		$.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Classify/modify/'+term_id+'/'+encodeURIComponent(new_term)},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){
			  term_dom.data('term',new_term);	
			  system_message_alert('alert',"已更新資料共"+response.data+"項");
			}else{
			  term_dom.text(term_dom.data('term') );
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	    }).done(function() { system_loading(); }); 
	  }
	  term_dom.prop('contenteditable',false).focus(); 	
	  $(this).css('display','none').prev().show();	
	});
	
	//-- classify term(tag folder level) delete option
	$('.act_term_dele').click(function(){
	  var term_id     = $(this).parents('.data_list').attr('no');
	  var term_dom    = $(this).parents('.data_list').find('.term_edit');
	  var term_name    = term_dom.text(); 		
	  
	  if(!confirm("確定要刪除[ "+ term_name+" ]此項目? \n 相關檔案將失去此項目的連結，並不會將檔案刪除")) return false;
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Classify/delete/'+term_id},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){
			  system_message_alert('alert',"已更新資料共"+response.data+"項");
			  term_dom.parents('.data_list').remove(); 
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() { system_loading(); }); 
	  
	  
	});
	
	
	//--classify level addClass
	$(document).on('click','.act_level_add',function(){
	  
	  var term_dom    = $(this).parents('.data_list');
      var term_new    = term_dom.clone();
	  var level_folder=$("<optgroup/>").attr('label',"掛載資料夾");
	  $('.user_folder').each(function(){
		level_folder.append("<option value='"+$(this).attr('no')+"'>"+$(this).find('.term_edit').text()+"</option>");  
	  });
	  var term_type   = $("<select/>").addClass('level_type_select').html("<option value='L_0'>新增專題</option>").append(level_folder);
	  
	  term_new.attr('no','_new');
	  term_new.find('td:nth-child(2)').html(term_type) ;
	  term_new.find('.term_edit').css('margin-left',(term_dom.data('site')*20)+'px').text('新增專題').prop('contenteditable',true);
	  term_new.find('.act_level_add').next().css('display','inline-block').end().hide();
	  term_dom.after(term_new);
	});
	
	
	//-- new level type select
	$(document).on('change','.level_type_select',function(){
	  var editable = $(this).val()=='L_0' ? true:false;
	  $(this).parents('.data_list').find('.term_edit').text($(this).find('option:selected').html()).prop('contenteditable',editable);;
	});
	
	//-- level save
	$(document).on('click','.act_level_save',function(){
	  var term_dom     = $(this).parents('.data_list');	
	  var parent_level = term_dom.prev().attr('no');
	  var term_name    = term_dom.find('.term_edit').text();
	  var term_code    = term_dom.find('.level_type_select').val();
	  if(!term_name.length || term_name=='新增專題'){
		system_message_alert('',"尚未輸入專題名稱");
        return false;		
	  }
	  if(!confirm("確定要新增專題[ "+term_name+" ]?")) return false;
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Classify/lvadd/'+parent_level+'/'+term_code+'/'+encodeURIComponent(term_name)},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){
			  term_dom.attr('no',response.data.lvno);
			  term_dom.find('td:nth-child(2)').html(response.data.lvcode) ;
			  term_dom.find('.term_edit').prop('contenteditable',false);;
			  term_dom.find('.act_level_save').prev().css('display','inline-block').end().hide();
			  system_message_alert('alert',term_name+" 專題已新增");
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() { system_loading(); });
	});
	
	//-- level delete
	$(document).on('click','.act_level_dele',function(){
	  var term_id     = $(this).parents('.data_list').attr('no');
	  var term_dom    = $(this).parents('.data_list').find('.term_edit');
	  var term_name    = term_dom.text(); 		
	  
	  if(term_id == '_new'){
		term_dom.parents('.data_list').remove();  
	    return true;
	  }
	  
	  if(!confirm("確定要刪除[ "+ term_name+" ]此項目? \n 專題類別刪除前，請先確認是否已經清空類別!!")) return false;
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Classify/delete/'+term_id},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){
			  system_message_alert('alert',"專題類別『"+term_name+"』已刪除");
			  term_dom.parents('.data_list').remove(); 
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() { system_loading(); }); 	
			
	});
	
	
  });	