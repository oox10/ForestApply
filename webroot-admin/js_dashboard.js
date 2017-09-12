/* [ Admin Dashboard Function Set ] */
	
  
  $(window).load(function () {   //  || $(document).ready(function() {		
	 
	 
	 $('#act_sync_now').click(function(){
	  
        if(!confirm("確定要立刻執行同步作業?")){
		  return false;  
	    }
	  
        $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Main/sync'},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  location.href = 'index.php?act=Record/dbtask/sync';
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	    }).done(function() { system_loading();   });
	 });
	 
	 
	 $('#act_dump_now').click(function(){
	  
        if(!confirm("確定要立刻執行資料庫備份作業?")){
		  return false;  
	    }
	    $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Main/dump'},
		  beforeSend: 	function(){ system_loading();  },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){  
			  location.href = 'index.php?act=Record/dbtask/dump';
		    }else{
			  system_message_alert('',response.info);
		    }
	      },
		  complete:		function(){   }
	    }).done(function() { system_loading();   });	  

		 
	 });
	 
	 
	 
	 
	 $('#amount_chart').highcharts({
        chart: {
            renderTo: 'amount_chart',
			type: 'bar'
        },
        title: {
            text: '數量統計'
        },
        subtitle: {
            text: '2016-12-19 '
        },
        xAxis: {
            categories: ['客家歷史照片', '客家歷史影片', '文物數位典藏', '活動照片紀實'],
            title: {
                text: null
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: '統計',
                align: 'high'
            },
            labels: {
                overflow: 'justify'
            }
        },
        tooltip: {
            valueSuffix: ' '
        },
        plotOptions: {
            bar: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: 0,
            y: -5,
            floating: true,
            
            backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
            
        },
        credits: {
            enabled: false
        },
        series: [{
            name: '資料筆數',
            data: [32509 , 0 , 0, 0]
        }, {
            name: '檔案數量',
            data: [88660, 0, 0, 0]
        }]
    });
	
	
	
	
  });	