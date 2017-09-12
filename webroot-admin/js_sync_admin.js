/* [ Admin Staff Function Set ] */
	
  
  $(window).load(function () {   //  || $(document).ready(function() {		
	
	//-- datepicker initial
	$("#date_start,#date_end").datepicker({
	    dateFormat: 'yy-mm-dd',
	});
	
	// 查詢日期範圍
	$('#search_by_date').click(function(){
	  var act = $('.func_activate.inthis').attr('id')
	  location.href = 'index.php?act='+act+'/index/'+$('#date_start').val()+'/'+$('#date_end').val();
	});
    
	
	//-- admin sync active right now
	$(document).on('click','#act_sync_active',function(){
	  // confirm to admin
	  if(!confirm("確定要立刻執行資料庫同步?")){
	    return false;  
	  }
	  
	  $.ajax({
		  url: 'index.php',
		  type:'POST',
		  dataType:'json',
		  data: {act:'Sync/active'},
		  beforeSend: 	function(){ system_loading();  },
		  error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		  success: 		function(response) {
			if(response.action){  
			  location.reload();  
			}else{
			  system_message_alert('',response.info);
			}
		  },
		  complete:		function(){   }
	  }).done(function() { system_loading(); });
	});
    
	
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
	
	
	
  });	
  
  
  