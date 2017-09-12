/* [ Admin Lotto Function Set ] */
	
  $(window).load(function () {   //  || $(document).ready(function() {		
	
	//-- datepicker initial
	$("#search_date_start,#search_date_end").datepicker({
	    dateFormat: 'yy-mm-dd',
	});
	
	
	//-- area filter
	$('#area_lotto_selecter').change(function(){
	  location.href='index.php?act=Lotto/search/'+$(this).val();	
	});
	
	//-- date filter
	$('#search_date_start').change(function(){
	  location.href='index.php?act=Lotto/search/'+$('#area_lotto_selecter').val()+'/'+$(this).val();	
	});
	
	
	
    //-- admin book get data
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
	    
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Lotto/read/'+data_no},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){
			  
			  dom_record.addClass('_target');
			  
			  // insert booking
			  $.each(response.data.record.booking,function(i,book){ 
			    var DOM = $("<tr/>").addClass("booking");
			    DOM.attr({'lotto':book.lotto,'accept':book.accept});
				DOM.append("<td><a href='index.php?act=Booking/index/ALL#"+book.code+"' target=_self >"+book.code+"</a></td>");
				DOM.append("<td>"+book.date+"</td>");
				DOM.append("<td>"+book.leader+"</td>");
				DOM.append("<td>"+book.people+"</td>");
				DOM.append("<td>"+book.review+"</td>");
				DOM.append("<td class='lotto'><i class='mark24 pic_account_status0'></i><i class='mark24 pic_account_status5'></i></td>");
				
			    DOM.appendTo("#area_booking_today");
		      });
			  
			  // insert process
			  $.each(response.data.record.process,function(i,logs){ 
			    var DOM = $("<li/>").addClass("process");
			    DOM.append("<span>"+logs.time+"</span>");
			    DOM.append("<span>"+logs.type+"</span>");
				DOM.append("<span>"+logs.info+"</span>");
			    DOM.appendTo("#lotto_process_logs");
		      });
			  
			  
			   // change _data_read area
			  $dom = dom_record.clone().removeClass('_data_read');
			  $('#record_selecter').find('.record_control').hide();
			  $('#record_selecter').find('.record_list').children('.data_result').hide();
			  $('#record_selecter').find('.record_list').children('.data_target').empty().append( $dom).show();
			  $('#record_editor').find('a.view_switch').trigger('click');
			  
			  // set foot print 
			  active_header_footprint_option('record_selecter',dom_record.find("td[field='date_tolot']").text()+' '+dom_record.find("td[field='area_name']").text(),'_return_list');
			  
			  location.hash = data_no // hash the address
			  
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
	
	});
    
    
	
	//-- built lotto
	$('#act_built_lotto').click(function(){
	  
	  // active ajax
      $.ajax({
	    url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Lotto/built' },
	    beforeSend: function(){  system_loading() },
	    error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			if(parseInt(response.data)){
			  alert('偵測到本日抽籤區域'+ response.data +'筆，頁面即將重整');
			  window.location.reload(true);	
			}else{
			  system_message_alert('alert',"未偵測到本日應抽籤資料");	
			}
			
	      }else{
			system_message_alert('',response.info);
		  }
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading()  });
	})
	
	
	//-- active lotto
	$('#act_lotto_active').click(function(){
	  
	  var data_no   =  $('._target').length? $('._target').attr('no') : '';
	  
	  // active ajax
	  if( ! data_no ){
	    system_message_alert('',"資料錯誤");
		return false;
	  }
	  
	  // active ajax
      $.ajax({
	    url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Lotto/active/'+data_no },
	    beforeSend: function(){  system_loading() },
	    error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			window.location.reload(true);
	      }else{
			system_message_alert('',response.info);
		  }
	    },
		complete:	function(){  }
      }).done(function(r) {  system_loading()  });
	})
	
	
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
  
  