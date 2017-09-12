/* [ Admin Staff Function Set ] */
	
  
  $(window).load(function () {   //  || $(document).ready(function() {		
	
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
	
	
	//-- get staff data to editer  // 從server取得使用者資料並放入編輯區
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
	
	//-- change role map display
    $("input[name='roles']").change(function(){	
	  $(".role_map[data-role='"+$(this).val()+"']").attr('on',$(this).prop('checked')*1);
	});
    
	//-- create new staff data
	$('#act_staff_new').click(function(){
	    
	  // initial page
	  $('#editor_reform').trigger('click');
	  
	  // create new record
	  $tr = $("<tr/>").addClass('data_record _data_read').attr('no','_addnew');
	  $tr.append(" <td field='uno'  > - </td>");
	  $tr.append(" <td field='user_group'  ></td>");
	  $tr.append(" <td field='user_id'  > </td>");
	  $tr.append(" <td field='user_organ'  ></td>");
	  $tr.append(" <td field='user_name'  ></td>");
	  $tr.append(" <td field='user_tel'  ></td>");
	  $tr.append(" <td ><i class='mark24 pic_account_status1'></i></td>");
	  
	  // inseart to record table	
	  if(!$("tr.data_record[no='_addnew']").length){
	    $tr.prependTo('tbody.data_result').trigger('click');
	  }	
	});
	
	
	
	//-- iterm function execute
	$('#act_func_execute').click(function(){
	  
	  var staff_no     =  $('._target').length? $('._target').attr('no') : '';
	  var execute_func =  $('#execute_function_selecter').length ? $('#execute_function_selecter').val() : '';
	  
	  // check process target
	  if( !staff_no.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  }  
	  
	  // check process action
	  if( !execute_func.length ){
	    system_message_alert('',"尚未選擇執行功能");
	    return false;
	  } 

      // check process action
	  if( staff_no=='_addnew' ){
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
	    data: {act:'Staff/'+execute_func+'/'+staff_no},
		beforeSend: function(){  system_loading(); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			switch(execute_func){
			  case 'dele' : act_staff_del_after(response.data);break;
			  case 'startmail': alert("已成功寄出帳號開通信件 TO: "+response.data+" "); break;
			}
		  }else{
			system_message_alert('',response.info);
	      }
		  
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading(); });
	  $('#execute_function_selecter').val('');
	});
	
	// 執行帳號刪除後的動作
	function act_staff_del_after(StaffNo){
      $("tr._target[no='"+StaffNo+"']").remove();
	  $('#editor_reform').trigger('click');
	  $('.record_view').trigger('change');
	}
	
	
	
	/**-- [ group member setting Setting ] --**/

    //-- Open group member Setting area
    $('#act_set_gmember').click(function(){
	  
	  // Update DB
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Staff/gmember'},
		beforeSend: function(){  system_loading(); },
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			
			var now_group  = '';
			var now_member = {};
			
			// insert group selecter
			var gps = response.data.groups;
			$.each(gps,function(i,gp){
			  var $DOM = $("<option/>").val(gp.ug_code).html(gp.ug_name + ' - ' +gp.ug_info ).attr({'data-name':gp.ug_name,'data-info':gp.ug_info,'data-code':gp.ug_code});
			  if( $('#acc_group_select').val() == gp.ug_code){
				$DOM.prop('selected',true);  
				now_group = $('#acc_group_select').val();
			  }
              $DOM.appendTo('#group_queue');
			});
			
			// insert member select
			var mbr = response.data.members;
			$.each(mbr,function(gpc,mbrs){
			  if(gpc == now_group){
				now_member = mbrs; 
			  }else{
				var gpcode = '';  
				$.each(mbrs,function(i,mbr){
				  if(gpcode != mbr['gid']){
					$('#group_members').append("<optgroup label='"+gpc+"' >");  
					gpcode = mbr['gid'];
				  }
				  var $DOM = $("<option/>").val(mbr.uno).html(mbr.user_id+' / '+mbr.user_name);
				  $DOM.appendTo('#group_members');
				});  
			  }
			});
			
			// insert group members
			$.each(now_member,function(i,mem){
			  var $DOM = $("<tr/>").addClass('gmember').attr('no',mem['user_id']);
              $DOM.append("<td>"+mem['user_id']+"</td>");  
			  $DOM.append("<td>"+mem['user_name']+"</td>");
			  $DOM.append("<td>"+mem['roles'].join(',')+"</td>");
			  $DOM.append("<td>"+mem['filter']+"</td>");
			  
			  if(parseInt(mem['master'])){
				$DOM.append("<td>-</td>");  
			  }else{
				$DOM.append("<td><button type='button' class='act_leave_group cancel'><i class='mark16 pic_group_leave'></i></button></td>");  
			  }
			  $DOM.appendTo('#member_list');
			});
			
			
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){  }
	  }).done(function(r) {   system_loading();   });
	  
	  $('.group_setting_area').show();
	});
	
	
	
	// change admin groups
	$('#group_selecter').change(function(){
	  
	  $('#group_members,#member_list').empty();
	  
	  var target_group = $(this).find('option:selected');
	  var group_code = $(this).val();
	  
	  
	  if(group_code=='_new_group'){
		// insert group meta
		$('#group_name').val('').focus();
		$('#group_info').val('');
		$('#group_code').val('').prop('readonly',false);    
	  }else{
		// insert group meta
        $('#group_name').val(target_group.data('name'));
        $('#group_info').val(target_group.data('info'));
        $('#group_code').val(target_group.data('code')).prop('readonly',true);
	  }
	  
	  // Update DB
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Staff/gmember'},
		beforeSend: function(){  system_loading(); },
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			
			var now_group  = '';
			var now_member = {};
			
			// insert member select
			var mbr = response.data.members;
			$.each(mbr,function(gpc,mbrs){
			  if(gpc == $('#group_selecter').val()){
				now_member = mbrs; 
			  }else{
				var gpcode = '';  
				$.each(mbrs,function(i,mbr){
				  if(gpcode != mbr['gid']){
					$('#group_members').append("<optgroup label='"+gpc+"' >");  
					gpcode = mbr['gid'];
				  }
				  var $DOM = $("<option/>").val(mbr.uno).html(mbr.user_id+'/'+mbr.user_name);
				  $DOM.appendTo('#group_members');
				});  
			  }
			});
			
			// insert group members
			$.each(now_member,function(i,mem){
			  var $DOM = $("<tr/>").addClass('gmember').attr('no',mem['user_id']);
			  $DOM.append("<td>"+mem['user_id']+"</td>");  
			  $DOM.append("<td>"+mem['user_name']+"</td>");
			  $DOM.append("<td>"+mem['roles'].join(',')+"</td>");
			  $DOM.append("<td>"+mem['filter']+"</td>");
			  if(parseInt(mem['master'])){
				$DOM.append("<td>-</td>");  
			  }else{
				$DOM.append("<td><button type='button' class='act_leave_group cancel'><i class='mark16 pic_group_leave'></i></button></td>");  
			  }
			  $DOM.appendTo('#member_list');
			});
			
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){  }
	  }).done(function(r) {   system_loading();   });  
	  
	  $('.group_setting_area').show();
	});
	
	
	// group leave function
	$(document).on('click','.act_leave_group',function(){
	  var user   = $(this).parents('tr.gmember').attr('no');
      var record = $(this).parents('tr.gmember');
	  var group = $('#group_selecter').val();
	  
      $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Staff/gpdef/'+user+'/'+group},
		beforeSend: function(){  system_loading(); },
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			record.remove();
			system_message_alert('alert',"使用者已移出群組");
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){  }
	  }).done(function(r) {   system_loading();   });
	  
	  // remove 
		
	});
	
	// group add function
	$('#act_addto_group').click(function(){
	  
	  var user  = $('#group_members').val();
	  var group = $('#group_selecter').val();
	  var role  = {};
	  $("input[name='add_role']").each(function(){
		role[$(this).val()] = $(this).prop('checked') ? 1 : 0;   
	  });
	  
	  // check user
	  if( !user ){
	    system_message_alert('',"尚未選擇成員");
		return false;
	  }	
	  
	  // check role
	  if( !$("input[name='add_role']:checked").length ){
	    system_message_alert('',"尚未設定角色");
		return false;
	  }	
	  
	  var passer_data    = encodeURIComponent(Base64.encode(JSON.stringify(role)));
	  var member_qualify = encodeURIComponent($('#member_qualify').val());
	  
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Staff/gpadd/'+user+'/'+group+'/'+passer_data+'/'+member_qualify},
		beforeSend: function(){  system_loading(); },
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			var mem = response.data;  
			if($("tr.gmember[no='"+mem['user_id']+"']").length){
			  $("tr.gmember[no='"+mem['user_id']+"']").children('td:nth-child(4)').html(mem['roles'].join(','));
			}else{
			  var $DOM = $("<tr/>").attr('no',mem['user_id']);
		      $DOM.append("<td>"+mem['user_id']+"</td>");  
		      $DOM.append("<td>"+mem['user_name']+"</td>");
		      $DOM.append("<td>"+mem['roles'].join(',')+"</td>");
		      $DOM.append("<td>"+mem['filter']+"</td>");
		      if(mem['master']){
			    $DOM.append("<td> - </td>");  
		      }else{
			    $DOM.append("<td><button type='button' class='act_leave_group cancel'><i class='mark16 pic_group_leave'></i></button></td>");  
		      }
		      $DOM.appendTo('#member_list');	
			}
			system_message_alert('alert',"帳號加入成功");
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){
		  $('#add_member').val('');
		  $("input[name='add_role']").prop('checked',false);
		}
	  }).done(function(r) {   system_loading();   });
	  
	  
	});
	
	
	//-- save group meta
    $('#act_save_group').click(function(){
	  var target_group = $('#group_selecter').find('option:selected');
	  var group_code   = $('#group_selecter').val();
	  
	  var group = {}
	  
	  if( !$('#group_name').val().length || !$('#group_code').val().length){
		system_message_alert('','群組名稱與代號不可空白');  
		return false;
	  }
	  
	  group['name'] = $('#group_name').val();
	  group['info'] = $('#group_info').val();
	  group['code'] = $('#group_code').val();
	  
	  var passer_data    = encodeURIComponent(Base64M.encode(JSON.stringify(group)));
	  
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Staff/gsave/'+passer_data},
		beforeSend: function(){  system_loading(); },
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			if(group_code=='_new_group'){
			  var $DOM = $("<option/>").val(response.data).html(group['name'] + ' - ' +group['info'] ).attr({'data-name':group['name'],'data-info':group['info'],'data-code':group['code'] });
			  $DOM.appendTo('#group_queue');
			  system_message_alert('alert',"群組新增成功");
			  $('#group_selecter').find("option[value='"+response.data+"']").data({'name':group['name'],'info':group['info']}).html(group['name'] + '-' +group['info']);
			}else{
			  system_message_alert('alert',"群組更新成功");  	
			}
			$('#group_selecter').val(response.data).trigger('change');
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){}
	  }).done(function(r) {   system_loading();   });
	
	});	
	
	//-- save group meta
    $('#act_dele_group').click(function(){
	  var target_group = $('#group_selecter').find('option:selected');
	  var group_code   = $('#group_selecter').val();
	  
	  if( !group_code.length && group_code != '_new_group'){
		system_message_alert('','尚未選擇群組');  
		return false;
	  }
	  
	  // check group members 
	  if($('tr.gmember').length){
		system_message_alert('','請先將所有群組成員移除!!');  
		return false;  
	  }
	  
	  // confirm to admin
	  if(!confirm("確定要刪除 [ "+target_group.html()+" ] ?")){
	    return false;  
	  }
	  
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Staff/gdele/'+group_code},
		beforeSend: function(){  system_loading(); },
		error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		success: 	function(response) {
		  if(response.action){
			system_message_alert('alert',"群組已移除");
			$('#group_selecter').find("option[value='"+response.data+"']").remove();
			$('#group_selecter').val('adm').trigger('change');
		  }else{
			system_message_alert('',response.info);
		  }
		},
		complete:	function(){}
	  }).done(function(r) {   system_loading();   });
	
	});	
	
	
	//-- Close Project Setting & cancal now
    $('#close_setter').click(function(){
	  $('._setinit').empty().val('');
	  $("input[name='add_role']").prop('checked',false);	  
      $('.group_setting_area').hide();   
    });
	
	//-- 設定分頁
	$('.record_view').val(10).trigger('change');
	
  });	
  
  
  