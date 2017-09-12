/* [ Admin Record Function Set ] */
	
  $(window).load(function () {   //  || $(document).ready(function() {		
	
	
	$('.record_filter').datepicker({
		dateFormat: 'yy-mm-dd',
	    onClose: function(dateText, inst) { 
	      //if(/\d{4}-\d{2}-\d{2}$/.test(dateText)){
		  //  $(this).val(dateText+' 00:00:01');
		  //}
	    }
	});
	
	
	//-- area record selecter
	$('#area_record_selecter').change(function(){
	  
	  var target_area = $(this).val();
	  if( target_area == '_all' ){
		$('tbody.data_result').show();
	  }else{
		$('tbody.data_result').hide();
       	$("tbody.data_result[area='"+target_area+"']").show();
		$('#area_record_detail').prop('checked',true).trigger('change');
	  }
	});
	
	
	//-- area detail record display
	$('#area_record_detail').change(function(){
	  if($(this).prop('checked')){
		$('tr.data_detail').show();    
	  }else{
		$('tr.data_detail').hide();     
	  }
	});
	
	
	//-- record filter
    $('#search_by_date').click(function(){
      var record_date_start = $('#date_start').val();
	  var record_date_end   = $('#date_end').val();
	  if( !record_date_start.length || !record_date_end.length ){
		system_message_alert('','日期範圍輸入不完整');    
		return false;   
	  }
	  location.href = 'index.php?act=Record/search/'+record_date_start+'/'+record_date_end;
	});
	
	//-- record filter
    $('#act_record_export').click(function(){
      var record_date_start = $('#date_start').val();
	  var record_date_end   = $('#date_end').val();
	  if( !record_date_start.length || !record_date_end.length ){
		system_message_alert('','日期範圍輸入不完整');    
		return false;   
	  }
	  window.open('index.php?act=Record/export/'+record_date_start+'/'+record_date_end);
	});
	
	
	
  });	