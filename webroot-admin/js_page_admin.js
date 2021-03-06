/* [ Admin Post Function Set ] */
	
  $(window).load(function () {   //  || $(document).ready(function() {		
	
	/*  Froala Sample 
	GET : $('#'+docId+'-content').froalaEditor('html.get')
    INSERT : froalaEditor('html.insert',valfill,true);
    check : $(this).data('froala.editor')
	*/
	
	
	/* [editor tool setting] */
	var FroalaTool = [ 'bold', 'italic', 'underline', 'strikeThrough', 'fontFamily', 'fontSize', '|', 'color', 'paragraphStyle', '|', 'insertHR', 'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'insertLink', 'insertImage', 'insertTable','|', 'clearFormatting','html'];  //, 'paragraphFormat', 'quote' , 'undo', 'redo', 'selectAll','fullscreen', 'html' , '|', '-','insertFile'  //,'fullscreen' 有問題會變白
	
	if($('#page_content').length){ 
	  $('#page_content').froalaEditor({
		language: 'zh_tw',  
		iframe: true,
		//height: editer_height,
		toolbarButtons: FroalaTool
	  });
	  
	  $("a:contains('Unlicensed Froala Editor')").hide();
	}
	
	 
	
	//-- data record filter
	$("input[type='radio'][name='record_type']").click(function(){
	  if($(this).prop('checked')){
		var record_flag = $(this).val();
		$("tr.data_record").addClass('hide');
		$("tr.data_record[filter='"+record_flag+"']").removeClass('hide');
		$('.record_view').trigger('change');
	  }	
	});
	
	
	//-- admin staff get user data
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
		
		$dom = dom_record.clone().removeClass('_data_read');
	    $('#record_selecter').find('.record_control').hide();
		$('#record_selecter').find('.record_list').children('.data_result').hide();
		$('#record_selecter').find('.record_list').children('.data_target').empty().append( $dom).show();
		$('#record_editor').find('a.view_switch').trigger('click');
		active_header_footprint_option('record_selecter','新增內容','_return_list');
	  
	  }else{
	    
		$.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Page/read/'+data_no},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  dom_record.addClass('_target');
			  
			  var dataObj =  response.data.read;
			  
			  data_orl = dataObj;
			  
			  // change _data_read area
			  $dom = dom_record.clone().removeClass('_data_read');
			  $('#record_selecter').find('.record_control').hide();
			  $('#record_selecter').find('.record_list').children('.data_result').hide();
			  $('#record_selecter').find('.record_list').children('.data_target').empty().append( $dom).show();
			  $('#record_editor').find('a.view_switch').trigger('click');
			  
			  // insert data
			  insert_data_to_form(dataObj);
			  
			  // set foot print 
			  active_header_footprint_option('record_selecter',dataObj.page_title,'_return_list');
			  
			  // hash the address
			  location.hash = dataObj.spno
			  
			  
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	    }).done(function() { system_loading();   });
	  }
	
	});
	
	 
	//-- save data modify
	$('#act_page_save').click(function(){
	  
      // initial	  
	  var data_no    =  $('._target').length? $('._target').attr('no') : '';
	  var modify_data = {};
	  var roles_data  = {};
	  
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
		  
		  if(field_name=='page_content'){
			field_value =  $('#page_content').froalaEditor('html.get');  
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
	    data: {act:'Page/save/'+data_no+'/'+passer_data},
		beforeSend: function(){  active_loading(act_object,'initial'); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  
		  if(response.action){
			
			var dataObj = response.data.read;
			data_orl = dataObj;
			
			// insert data
			insert_data_to_form(dataObj);
			
			// update data no 
			if( data_no == '_addnew'){  $('._target').attr('no',dataObj.spno) }
		  
		  }else{
			system_message_alert('',response.info);
	      }
		  
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action );  });
	});
	
	
	//-- get data to editer  // 從server取得資料並放入編輯區
	function insert_data_to_form(dataObj){
	  var dom_record  = $('._target'); 
	  
	  $.each(dataObj,function(field,meta){
		if(field=='roles' && meta){
		  //  'R01':1 'R02':0 ...	
		  $.each(meta,function(rid,checked){
			$("input:checkbox[name='roles'][value='"+rid+"']").prop('checked',checked);    
		    $(".role_map[data-role='"+rid+"']").attr('on',checked);
		  });
		}else if(field=='groups'){
			$("span[name='groups']").html('');	  
			$.each(meta,function(i,g){
			  if(g.master){
				$("span#main_group").html("<i title='"+g.ug_info+"'>"+g.ug_name+"</i>");  
			  }else{
				$("span#rela_group").append("<i title='"+g.ug_info+"'>"+g.ug_name+"</i>");	  
			  }
			});  
		}else{
		  
          if(  $("._variable[id='"+field+"']").length ){  
			if(field=='page_content'){
			  $('#page_content').froalaEditor('html.set',meta,true);
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
	
	//-- create new staff data
	$('#act_page_new').click(function(){
	    
	  // initial page
	  $('#editor_reform').trigger('click');
	  
	  // create new record
	  $tr = $("<tr/>").addClass('data_record _data_read').attr('no','_addnew');
	  $tr.append(" <td field='no'  > - </td>");
	  $tr.append(" <td field='page_type'  ></td>");
	  $tr.append(" <td field='page_owner'  > </td>");
	  $tr.append(" <td field='page_show'  ></td>");
	  $tr.append(" <td field='page_title'  ></td>");
	  $tr.append(" <td field='_timeupdate'  ></td>");
      $tr.append(" <td ><i class='fa fa-check' aria-hidden='true'></i></td>");
	  
	  // inseart to record table	
	  if(!$("tr.data_record[no='_addnew']").length){
	    $tr.prependTo('tbody.data_result').trigger('click');
	  }	
	});
	
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
	
	
  });	
  
  
  