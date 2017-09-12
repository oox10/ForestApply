

$(window).load(function () {   //  || $(document).ready(function() {

    //-- datepicker initial
	$("#apply_inter_date").datepicker({
	    dateFormat: 'yy-mm-dd',
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
		  
		  if(response.action){
			var calindex = 0;
            $.each(response.data.date,function(month,dates){
				
			  var calendar = $('.calendar[area="'+areaid+'"]:eq('+calindex+')');
			  $('.date_ynm').data('month',month).text(month.substr(0,4)+' 年 '+month.substr(5,2)+' 月');
			  
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
			
			//insert info
			$('.descrip > div').empty();
			if(response.data.info.area.refer.image){
			  $.each(response.data.info.area.refer.image,function(imgindex,imgmeta){
				$('.descrip > .photo_container').append("<img src='photo.php?src="+areaid+'/'+imgindex+"' />");  
				$('.descrip > .area_descrip').html(response.data.info.area.area_descrip);  
			    return false;
			  });
			}
			
			
		  }else{
		    system_message_alert('error',response.info);
		  }
		},
		complete:		function(){ }
	  }).done(function(r) { system_loading() });		
	}
	
	// change apply calendar
	$('.date_change').click(function(){
      var main = $('#area_calendar');	 
	  var mode = $(this).attr('mode');
	  var areaid  = main.attr('area');
	  var calnow  = $('.date_ynm').data('month');
	  var calnext = (mode=='+') ? moment(calnow+'-01').add(1, 'month').format('YYYY-MM') : moment(calnow+'-01').add(-1, 'month').format('YYYY-MM');  
	  var calnums = 0 + parseInt(mode+($(".calendar[area='"+areaid+"']").length));
	  
	  if(!areaid.length){
		system_message_alert('','尚未選擇區域');  
	    return false;
	  }
	  reform_calendar(areaid,calnext,calnums);
	});
	
	// area info change 
	$('#area_into_selecter').change(function(){
	  var main = $('#area_calendar');	 
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
	
	
	//-- admin book get data
	$(document).on('click','#applied_search',function(){
	  
	  // initial	  
	  var area_code  = $('#area_into_selecter').val();
	  var inter_date = $('#apply_inter_date').val(); 
	  
	  // active ajax
	  if( ! area_code ){
	    system_message_alert('',"尚未選擇區域");
		return false;
	  }
	  
	  // active ajax
	  if( ! inter_date ){
	    system_message_alert('',"尚未選擇日期");
		return false;
	  }
	  
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Landing/lotto/'+area_code+'/'+inter_date},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    
			if(response.action){
			  
			  var search_month = inter_date.substr(0,4)+'-'+inter_date.substr(5,2);
			  
			  reform_calendar(area_code,search_month,1);
			  
			  $('#area_booking_today').empty();
			  
			  if( !Object.keys(response.data.lotto.booking).length  ){
				system_message_alert('','本日沒有抽籤紀錄');  
			    return false;
			  }
			  
			  // insert booking
			  $.each(response.data.lotto.booking,function(i,book){ 
			    var DOM = $("<tr/>").addClass("booking");
			    DOM.attr({'lotto':book.lotto,'accept':book.accept});
				DOM.append("<td>"+book.code+"</a></td>");
				DOM.append("<td>"+book.date+"</td>");
				DOM.append("<td>"+book.leader+"</td>");
				DOM.append("<td>"+book.people+"</td>");
				DOM.append("<td>"+book.review+"</td>");
				DOM.append("<td class='lotto'><i class='mark24 iconv_lotto_O'></i><i class='mark24 iconv_lotto_X'></i></td>");
				DOM.appendTo("#area_booking_today");
		      });
			  system_message_alert('alert',inter_date+' 抽籤結果如下');
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	  }).done(function() { system_loading();   });
	
	});
    
	// click date to search applied
	$(document).on('click',"div.date_slot[data-date!='']",function(){
	  var target_date = $(this).data('date');
	  $('#apply_inter_date').val(target_date);
	  $('#applied_search').trigger('click');
	});	
	
	// area initial
	if($('#area_into_selecter').length){
	  var default_area = $('#area_into_selecter').find('option[value="c6261eb8"]').val();	
	  $('#area_into_selecter').val(default_area).trigger('change');
	}
	
	

});