/*    
  
  javascrip use jquery 
  rcdh 10 javascript pattenr rules v1
  
*/
 
  /***************************************************   
	IISPhoArchive # Photo List & Admin     
  ***************************************************/
  	
  $(window).load(function () {   //  || $(document).ready(function() {	
	
	$(window).resize(function() {
	  insert_object_to_page(meta_orl.file_type,meta_orl.objsrc);			
	});
	
	/***--  區塊設定  --***/

    if($('.group_condinter').length){
	  // 設定 jScrollPane
	  $('.group_condinter').scrollbar(); 
    }	
	
	//-- 分類模式切換
	$('.mode_sel').click(function(){
	  var open_dom = $(this).attr('dom');	
	  $('.data_group').hide();
	  $('#'+open_dom).show();
	  $('.this_mode').removeClass('this_mode');	
	  $(this).addClass('this_mode');	
	});
	
	//-- Lazy Load
	/*
	$( ".work_continder" ).scroll(function() {
	  //$(window).trigger('resize');	
	  $(this).lazyLoadXT();
	});
	$(".work_continder").lazyLoadXT({edgeY:300});
	*/
	
	//-- 介面標示與動作
	var path = location.search.replace(/\?act=/,'');
	var action = path.split('/');
	
	switch(action[1]){
	  case 'tags':
	  case 'folder':
		$(".mode_sel[dom='byfolder']").trigger('click');
	    break;
	  
	  case 'level':
		$('li#classify-'+action[2]).addClass('LevelTarget');
		$(".mode_sel[dom='bylevel']").trigger('click');
	    break;
	  
      case 'search':
        var query = JSON.parse(decodeURIComponent(action[2]))
		
		$.each(query,function(i,qstring){
		  var qset = qstring.split(':');
		  $(".search_block[qset="+i+"]").children('select.search_field').val(qset[0]);
		  $(".search_block[qset="+i+"]").children('input.search_string').val(qset[1]);
		});
		
		if($('.rslItem').length > 1){
		  $('#search_mode option#narrow').prop('selected',true);
		  $(".mode_sel[dom='byfilter']").trigger('click');
		}
		
	    break;	  
	  default:break;
	}
	
	//-- Level Switch層次開關   
	$(document).on('click','.level_switch',function(){
	  var control_dom = $(this).attr('dom');	
	  var control_btn = $(this).text();
	  $(this).html(function(){
		if(control_btn=='*'){
		  return control_btn;
		}else{
		  return control_btn=='+' ? "−" : "+";		
		}
	  });
	  $('#'+control_dom).toggle();
	});
	
	
	//-- 篩選項目開關
	$('h1.filter_border').click(function(){
      $(this).next().toggle();	  
	});
	
	
	//-- 搜尋重新設定
	$('#search_mode').change(function(){
	  if($(this).val() == ''){
		$('.search_field').val('kwds');  
	  }	
	});
		
	//-- 輸入搜尋空白
	
	$('#empty_value').click(function(){
	  if($('#search_field').val()=='kwds'){
		system_message_alert('',"搜尋空白內容必須指定欄位條件"); 
	  }
      	  
	  $('#search_string').val('.none');
	});
	
	
	//-- 檢索
	var query_data = [];
	$('#act_search').click(function(){
	  
	  var field  = $('#search_field').val() ? $('#search_field').val() : 'kw';
	  var search = $('#search_string').val();
	  
	  if($(this).prop('disabled')){
	    alert("系統正在查詢中，請稍候..");
		return false;  
	  }
	  
	  if(!search.length){
		system_message_alert('error',"請輸入檢索條件");
        $('#search_string').focus();		
	    return false;
	  }
	  $(this).prop('disabled',true);
      
	  var accnum = $('#search_mode').val();
	  query_data.push(field+':'+search);
	  
	  // encode data
	  var passer_data  = encodeURIComponent(JSON.stringify(query_data));
	  location.href = "index.php?act=Archive/search/"+passer_data+'/'+accnum;
	});
	
	
	//-- Query Lazy Load 
	/*
	$('#marker').on('lazyshow', function () {
        var slot  = $(this).attr('slot');
		var accno = $(this).attr('accno');
		
		if(!parseInt(accno)  || slot=='-' ){
		  return false;	
		}
		loadQueryResultToSystem(accno,slot);
    }).lazyLoadXT({visibleOnly: false});
	*/
	
	//-- load Query Result Solt to system
	var xhr;
	function loadQueryResultToSystem(AccNo,Slot){
		
	  var rebindlazy = false;
	  if(!Slot){
		$('#marker').attr({'accno':AccNo,'slot':Slot}).off('lazyshow');   // 防止篩選清空後連送兩次lazyload 
		$('#resultPage div.rslItem').remove();
		rebindlazy = true;
	    //xhr.abort();
	  }
	  xhr = $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/loading/'+AccNo+'/'+Slot},
		  beforeSend: 	function(){ },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){
              $.each( response.data.records , function( no , doc ){
				  $DOM = $("div.rslItem[no='sample']").clone();
				  $DOM.find('.rno').text((no+1)+'.');
			      $DOM.find('.fa').addClass('fa-file-'+doc.file_type+'-o');
			      $DOM.find('.rtitle').text(doc['@title'][0]);
			      
				  $DOM.find('.typeblock').text(doc.doc_type);
				  $DOM.find('.yearblock').text(doc.PQ_YearNum);
				  $DOM.find('.utime').text(doc.upload_time);
				  $DOM.find('.uuser').text(doc.upload_user);
				  
				  $('.contenttags').empty();
				  $.each(doc['@rarea'],function(i,tag){
					$('<li/>').addClass('rarea').html(tag).appendTo($('.contenttags'));
				  });
				  
				   $.each(doc['@rdomain'],function(i,tag){
					$('<li/>').addClass('rdomain').html(tag).appendTo($('.contenttags'));
				  });
				  
				   $.each(doc['@rmethod'],function(i,tag){
					$('<li/>').addClass('rmethod').html(tag).appendTo($('.contenttags'));
				  });
				  
				  $DOM.drop("start",function(){ $( this ).addClass("active");});
				  $DOM.drop(function( ev, dd ){ $( this ).children('div.rslItem:not(.selected)').trigger('click');});
				  $DOM.drop("end",function(){ $( this ).removeClass("active"); });
				  
				  $DOM.insertBefore('#marker');
				  
			  });
              
			  $('#marker').attr('slot',response.data.slot);
			  
			  
			  $(window).lazyLoadXT({edgeY:300});
			  if( rebindlazy){
				$('#marker').on('lazyshow', function () {
				  var slot  = $(this).attr('slot');
				  var accno = $(this).attr('accno');
					
				  if(!parseInt(accno)  || slot=='-' ) return false;	
				  loadQueryResultToSystem(accno,slot);
				}).lazyLoadXT({visibleOnly: false});	
			  }else{
				$('#marker').lazyLoadXT({visibleOnly: false, checkDuplicates: false});   
			  }
			  
			  
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() {  });
	}
	
	
	//-- 資料夾選取
	$(document).on('click','.act_folder',function(){
	  var folder_no = $(this).attr('no');	
	  location.href = 'index.php?act=Archive/folder/'+folder_no;
	});
	
	//-- 標籤選取
	$('.act_tags').click(function(){
	  var tag_term = encodeURIComponent($(this).attr('term'));	
	  location.href = 'index.php?act=Archive/tags/'+tag_term;
	});
	
	
	//-- Insert Post Query
	if($('#byfilter').length){
		
	  var access_code = $('#byfilter').attr('accnum');	
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/filter/'+access_code},
		  beforeSend: 	function(){ },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){
			  $.each( response.data , function( filter , pqset ){
				$('#'+filter).html('');
				
				if(pqset.terms.length){
				  $.each(pqset.terms,function(i,term){
				    var query = new Array(pqset.field+':'+term.name);
                    var DOM = $("<li/>");	
				    DOM.append("<input type='checkbox' class='act_pquery' field='"+pqset.field+"' value='"+term.name+"' />");	
				    DOM.append("<span class='act_pquery_this' title='"+term.name+"'>"+term.view+"</span>")
				    DOM.append("<i>"+term.count+"</i>");
				    $('#'+filter).append(DOM); 
				  });	
					
				}else{
				  $('#'+filter).html('<li> - </li>'); 	
				}
			  });
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() {  }); 
	}
	
	
	//-- 設定後分類 單選
	$(document).on('click','.act_pquery_this',function(){
	  $('.act_pquery:checked').prop('checked',false);
	  $(this).prev().trigger('click');
	});
	
	//-- 設定後分類 多選
	$(document).on('click','.act_pquery',function(){
		
	  system_message_alert('尚未開放');	
	  return false;	
		
	  var access_code = $('#byfilter').attr('accnum');	
	  
	  // collect filter
	  var filter = {};
	  $('.act_pquery:checked').each(function(){
		if( typeof filter[$(this).attr('field')] === "undefined" ) filter[$(this).attr('field')] = [];
		filter[$(this).attr('field')].push($(this).val());
	  });	
	  
      // built condition
	  var condition = [];	  
	  var filtercrumbs = 0;
	  $.each(filter,function(f,qset){
		var query = f+':'+qset.join('|');  
		filtercrumbs+= qset.length ;
		condition.push(query);  
	  });
		
	  // encode data
	  var passer_data  = encodeURIComponent(JSON.stringify(condition));
	  if(condition.length){
		$.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/pquery/'+passer_data+'/'+access_code},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){
			   
			  // 讀取現在結果
              loadQueryResultToSystem(response.data.result.access_num,0);
			  
			  // 加入麵包屑
              addSystemBreadcrumbs("篩選 : "+filtercrumbs+'項條件',response.data.result.result_num,'','filter',1);
			}else{
			  system_message_alert('error',response.info);
			}
		  },
		  complete:	function(){ }
	    }).done(function() { system_loading(); });   
	    
	  }else{
		// 取得原本的結果
        loadQueryResultToSystem(access_code,0);
		// 移除麵包屑 
		addSystemBreadcrumbs('',0,'','filter',false);
	  }
	  
	});
	
	
	// 加入 Breadcrumbs
	function addSystemBreadcrumbs(name,value,link,type,add){
	  var crumb = $('<li/>').addClass('breadcrumb').addClass(type);
	  crumb.html("<a href='"+link+"'>"+name+" ("+value+") </a>")
	  $('li.'+type).remove();
	  if(add){
		crumb.appendTo('#system_breadcrumbs');  
	  }
	}
	
	
	//-- change sort type
	$('.sort_type').click(function(){
	  $('.act_pquery:checked').prop('checked',false);
	  addSystemBreadcrumbs('',0,'','filter',false);	
	  var sort = {};
	  var access_code = $('#byfilter').attr('accnum');	
	  var dom = $(this);
	  sort['field'] = $('.sort_field').val();
	  sort['type']  = $(this).attr('name')=='desc' ? 'asc':'desc'; 
	  var passer_data  = encodeURIComponent(JSON.stringify(sort));
	    $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/order/'+passer_data+'/'+access_code},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){
			  dom.attr('name',sort['type']); //標示排序設定
	          loadQueryResultToSystem(response.data.result.access_num,0);
			}else{
			  system_message_alert('error',response.info);
			}
		  },
		  complete:	function(){ }
	    }).done(function() { system_loading(); });  
	});
	
	//-- change sort field
	$('.sort_field').change(function(){
	  $('.act_pquery:checked').prop('checked',false);
	  addSystemBreadcrumbs('',0,'','filter',false);
	  
	  var sort = {};
	  var access_code = $('#byfilter').attr('accnum');	
	  var dom = $(this);
	  sort['field'] = $(this).val();
	  sort['type']  = $(this).prev().attr('name')=='desc' ? 'desc':'asc'; 
	  var passer_data  = encodeURIComponent(JSON.stringify(sort));
	    $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/order/'+passer_data+'/'+access_code},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    if(response.action){
			  $('.sort_type').attr('name',sort['type']); //標示排序設定
	          loadQueryResultToSystem(response.data.result.access_num,0);
			}else{
			  system_message_alert('error',response.info);
			}
		  },
		  complete:	function(){ }
	    }).done(function() { system_loading(); });  
	  
	});
	
	
	
	//-- change display image size
	/*
	$('#size_set').change(function(){
	  $('#size_val').text($(this).val());	
      $('.imgblock').css('height',$(this).val()+'px');	
      $('.imgblock').each(function(){
		if( $(this).find('img').width() > 225 ){
		  $(this).width($(this).find('img').width());	
		}
	  });
	});
	*/
	
	
	//-- select target photo 
	var photo_selected = {};  // save user select photo
	
	$(document).on('click','.rslItem',function(){
	  var meta_no = $(this).attr('no');
	  if( $(this).hasClass('selected') ){
		$(this).removeClass('selected');  
	    delete photo_selected[meta_no];
	  }else{
		$(this).addClass('selected');  
	    photo_selected[meta_no] = 1;
	  }
	  $('#user_selected_count').text(parseInt(Object.keys(photo_selected).length));
	  if(parseInt(Object.keys(photo_selected).length)){
		$('#act_unselect').css('visibility','visible');  
	  }else{
		$('#act_unselect').css('visibility','hidden');  
	  }
	  
	});
	
	//-- unselect all
	$('#act_unselect').click(function(){
      if(!confirm("確定要取消所有勾選?")){
		return false;  
	  }	  
	  $('.selected').removeClass('selected');
	  $('#user_selected_count').text(0);
	  photo_selected = {};
	  $(this).css('visibility','hidden');  
	});
	
	
	/**-- [ Photo Meta Setting ] --**/  //-- 設定區塊 
	
	
	//-- Cancel Setter  取消設定
    $('#photo_set_cancel,#close_setter').click(function(){
	  
	  if( $('._modify').length ){
		if(!confirm("確定要取消設定? 這將會清除尚未儲存的設定")){
		  return false;	
		}  
      }
      
	  $('._modify').removeClass('_modify');	
	  
	  //execute cancel process     
      $('.meta._metaval').val('');    
      $('.user_tags').empty();
	  
      //close   
	  $('.meta_modify_option').hide();
	  $('#act_delete_accept').prop('checked',false);
	  $('#photo_display').empty();
      $('.display_area').hide(); 
	  $('._edit._meta').prop('disabled',true);	
	  $('._edit._meta').prop('contenteditable',false);
	  
	});	
	
	//-- 開啟編輯區 can edit
	$('#act_edit_on').click(function(){
	  $('._edit._meta').prop('disabled',false);	
	  $('._edit._meta').prop('contenteditable',true);
      $('.meta_modify_option').show();	  
	});
	
	
	//-- 開啟刪除按鈕
	$('#act_delete_accept').click(function(){
	  var delete_option = $(this).prop('checked') ? "1":"0";
	  $('#act_meta_dele').attr('on',delete_option);	
	});
	
	//-- 檢查編輯項目
	$('._edit._meta').keyup( function(){ 
	  var field = $(this).data('field');
	  if( $(this).val() !== meta_orl[field] ){
		$(this).addClass('_modify');  
	  }else{
		$(this).removeClass('_modify');    
	  }
	});
	
	//-- open edit area : 讀取照片檔案 
	var meta_orl = {};
	$(document).on('dblclick','.rslItem',function(){
      
	  var item_id = $(this).attr('no');
	  
	  if(!item_id.length){
		system_message_alert('error',"影像參數錯誤");  
	    return false;
	  }
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/meta/'+item_id},
		  beforeSend: 	function(){ system_loading() },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    
			if(response.action){
			  $('.display_area').show();	
				
			  meta_orl = response.data.read.meta;
			  insert_object_to_page(response.data.read.meta.file_type,response.data.read.meta.objsrc);
			  insert_meta_to_page(meta_orl);
			  //score_mark(response.data.read.meta.score);
			  //tags_display(response.data.read.meta.tags); // setted tags
			  
			}else{
			  system_message_alert('error',response.info);
			}
			
	      },
		  complete:	function(){ }
	  }).done(function() { system_loading() }); 
	  
	});
	
	// 點選讀取照變
	$(document).on('click','.act_viewpho',function(){
      
	  var item_id = $(this).parents('.rslItem').attr('no');
	  
	  if(!item_id.length){
		system_message_alert('error',"影像參數錯誤");  
	    return false;
	  }
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/meta/'+item_id},
		  beforeSend: 	function(){ system_loading() },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
		    
			if(response.action){
			  $('.display_area').show();	
				
			  meta_orl = response.data.read.meta;
			  insert_object_to_page(response.data.read.meta.file_type,response.data.read.meta.objsrc);
			  insert_meta_to_page(meta_orl);
			  score_mark(response.data.read.meta.score);
			  tags_display(response.data.read.meta.tags); // setted tags
			  
			}else{
			  system_message_alert('error',response.info);
			}
			
	      },
		  complete:	function(){ }
	  }).done(function() { system_loading() }); 
	  
	});
	
	
	
	
	/**[ 分數設定與顯示 ]**/
	//-- score set
	$('#score li').click(function(){
	  var photo_score = $(this).data('score');	
	  $("._metaval[data-field='score']").val(photo_score);
	  score_mark(photo_score);
	});
	
	//-- mark score
	function score_mark(score){
	  // setted score
	  var photo_score = parseInt(score);
	  $('#score li').removeClass('active');
	  $('#score li').each(function(){
		if(parseInt($(this).data('score')) <= photo_score ){
		  $(this).addClass('active');	
		} 
	  });
	}
	
	
	/**[ 標籤設定與顯示 ]**/
	//-- tags set
	$('#score li').click(function(){
	  var photo_score = $(this).data('score');	
	  $("._metaval[data-field='score']").val(photo_score);
	  score_mark(photo_score);
	});
	
	//-- mark tags
	function tags_display(photo_tags){
	  // setted tags
	  $('.user_tags').empty();
	  
	  var photags = photo_tags.split(';');
	  
	  if(!photags.length) return false  
	  
	  $.each(photags,function(i,tset){
		if(tset){
		  var t = tset.split(':');		
	      $('<li/>').attr({'tag':t[0],'user':t[1],'keep':'1'}).html(t[2]).appendTo('.user_tags');	
		  
		  // 檢查是否為新增標籤
		  var taghas = false;
		  $.each(tagrefer,function(i,tr){
			if(t[0] == tr.utno) taghas=true;
	      });
		  
		  if(!taghas){  // 新標籤加入參考陣列
			tagrefer.unshift({"utno":t[0],"user":t[1],"tag_term":t[2]});
		  }
		
		}
	  });
	  
	  // setted quick
	  $('#tags_used').empty();
	  $.each(tagrefer,function(i,t){
		if( i > 10 ) return false;
		$("<li>").addClass('t_add').attr({"tag":t.utno,"user":t.owner,"keep":1}).html(t.tag_term).appendTo('#tags_used');
	  });
	  
	}
	
	//-- 標籤編輯器
	$('#act_edit_tags').click(function(){
	  $('#tags_queue').html($('#tag_display').html());
	  $('#tags_editer').show();
	});
	
	//-- 關閉標籤編輯器
	$('#act_tags_edit_close').click(function(){
	  
	  var tags = [];
	  $('#tag_display').html($('#tags_queue').html());
	  $('#tag_display li').each(function(){
		var tag  = $(this).attr('tag');  
		var user = $(this).attr('user');  
		if(parseInt($(this).attr('keep'))){
		  tags.push(tag+':'+user+':'+$(this).text())
	    }
	  })
	  
	  if(tags.length){
		$("._metaval[data-field='tags']").val(tags.join(';')+';');  
	  }else{
		$("._metaval[data-field='tags']").val('');    
	  }
	  
	  if($("._metaval[data-field='tags']").val() != meta_orl.tags){
		$("._metaval[data-field='tags']").addClass('_modify');  
	  }
	  
	  $('.tag.selecter,#tags_queue').empty();
      $('#tag_getter').val('');
	  $('#tags_editer').hide();
	});
	
	
	//-- 標記刪除既有標籤
	$(document).on('click','#tags_queue li',function(){
	  if( $(this).attr('keep') == '1' ){
		$(this).attr('keep','0');   
	  }else{
		$(this).attr('keep','1');  
	  }	
	});
	
	
	//-- 依據輸入取得標籤建議
	var tagrefer = {};
	var tgrefer = [];
	if($('#tags_reference').text().length){
	  tagrefer = JSON.parse($('#tags_reference').text());	
	  $.each(tagrefer,function(i,tag){
		tgrefer[i] = {};
		tgrefer[i]['name'] = tag['tag_term'];
		tgrefer[i]['code'] = tag['utno'];
		tgrefer[i]['owner'] = tag['owner'];
	  })
	}
	
	if($('#tag_getter').length){
		document.getElementById("tag_getter").addEventListener("input", function() {
		  var content  =  $(this).val();
		  var keyinset = content.split(/;|；/);	  
		  var nowkeyin = keyinset.pop();	
		  if(!nowkeyin.length || keyinbuffer==nowkeyin ){  return false; }
		  keyinbuffer=nowkeyin;
		  
		  var pattern=nowkeyin.split(' ');
		  var re = new RegExp(pattern.join('.*?'),"g");
		  $('#tags_suggest').empty();
		  $.each(tagrefer,function(i,tgset){
			if(tgset['tag_term'].match(re)){
			  $('#tags_suggest').append("<li class='t_add' tag='"+tgset['utno']+"'  user='"+tgset['owner']+"' keep=1 >"+tgset['tag_term']+"</li>");
			}
		  });
		}, false)
	}
	
	//-- 選擇建議標籤
	$(document).on('click','.tags.selecter li',function(){
      var newTag = $(this);
	  if(!$("#tags_queue li[tag='"+newTag.attr('tag')+"']").length){
		$(this).clone().appendTo('#tags_queue');  
	  }else{
		system_message_alert('','重複的標籤');   
	  }
	});
	
	//-- 加入新標籤
	$('#act_add_new_tags').on('click',function(){
      var content  = $('#tag_getter').val();
	  var keyinset = content.split(/;|；/);
	  $.each(keyinset,function(i,newtag){ 
		if( newtag.length && !$("#tags_queue li:contains('"+newtag+"')").length ){
		  var tag = 'new';
		  var user = '';
		  $.each(tagrefer,function(i,tgset){
		    if(tgset['tag_term'] == newtag){
		       tag  = tgset['utno'];  
			   user = tgset['owner'];
		       return true;
			}
	      });
		  $('<li/>').addClass('t_add').attr({'tag':tag,'user':user,'keep':'1'}).html(newtag).appendTo('#tags_queue');	
		}
	  });
	  $('#tag_getter').val('');
	});
	
	
	
	$('#act_meta_save').click(function(){
		
	  // initial	  
	  var image_id    =  $("._metaval[data-field='identifier']").text().length? $("._metaval[data-field='identifier']").text() : '';
	  var modify_data = {};
	  var roles_data  = {};
	  var act_object  = $(this);
	  var checked = true;
	  
	  // option active checked  // 檢查按鈕是否在可執行狀態
	  if( act_object.prop('disabled') ){
	    return false;
	  }
	  
	  // check process data
	  if( !image_id.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  } 
	  
	  // get value
	  $('._meta._edit').each(function(){
	    
		if($(this)[0].tagName=='DIV'){
		  var field_name = $(this).data('field');
		  var field_value= $(this).text();
		}else{
		  var field_name  = $(this).data('field');
	      var field_value = $(this).val();
		}
		
		if( $(this).hasClass('_necessary') && field_value==''  ){  
		  $(this).focus();
		  system_message_alert('',"請填寫必要欄位 ( * 標示)");
		  checked = false;
		  return false;
		}
		
		if(meta_orl[field_name] !== field_value){
		  modify_data[field_name] = field_value;
		}
		
	  });
	  if(!checked){ return false; }
	  
	  if(!Object.keys(modify_data).length){
		system_message_alert('',"資料未變更");
	    return false;
	  }
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(modify_data)));
	  
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Archive/save/'+image_id+'/'+passer_data},
		beforeSend: function(){  active_loading(act_object,'initial'); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  
		  if(response.action){
			var dataObj = response.data.read.meta;
			meta_orl = dataObj;
			//insert data
			insert_meta_to_page(dataObj);
			tags_display(dataObj.tags)
		    system_message_alert('alert','資料更新成功');
			
			$('._edit._meta').prop('disabled',true);	
	        $('._edit._meta').prop('contenteditable',false);	
			$('._modify').removeClass('_modify');	
			
		  }else{
			system_message_alert('',response.info);
	      } 
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action ); $('.meta_modify_option').hide(); });
	});
	
	
	
	
	//-- 刪除照片
	$('#act_meta_dele').click(function(){
		
	  // initial	  
	  var image_id    =  $("._metaval[data-field='identifier']").text().length? $("._metaval[data-field='identifier']").text() : '';
	  var act_object  = $(this);
	  
	  // option active checked  // 檢查按鈕是否在可執行狀態
	  if( act_object.attr('on')!='1'){
		system_message_alert('',"刪除按鈕鎖定中");  
		return false;
	  } 
	  
	  // check process data
	  if( !image_id.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  }
	  
	  if(!confirm("確定要刪除照片?")) return false;
	  
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Archive/dele/'+image_id},
		beforeSend: function(){  active_loading(act_object,'initial'); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			
			// 重計檔案數量
			$.each(response.data.folders,function(i,f){
			  var orl_count = $("li.act_folder[no='"+f+"']").find('i.count').text();
			  $("li.act_folder[no='"+f+"']").find('i.count').text(parseInt(orl_count)-1);
			});
			
			$(".rslItem[no='"+image_id+"']").remove();
			$('._modify').removeClass('._modify'); 
			$('#close_setter').trigger('click');
			
			system_message_alert('alert',"資料刪除成功");
			
		  }else{
			system_message_alert('',response.info);
	      } 
	    },
		complete:	function(){  }
      }).done(function(r) {  active_loading(act_object , r.action );  });
	  $('#act_delete_accept').prop('checked',false);
	});
	
	
	//-- resize image use whell
	$('#photo_display').mousewheel(function(event, delta){
	  /*
	   event.deltaY - :往下捲  縮小
 	   event.deltaY + :往上捲  放大
	  */
	    var max_scale = 500;
		var min_scale = 100;
		var scale_num = 10;
		
        var scale = parseInt($('#photo_display').data('is'));
	    var rate  = 0;
	   
	    if(event.deltaY > 0){
		  rate= scale+scale_num;	
		}else if(event.deltaY < 0){
		  rate= scale-scale_num*2;		
		}
	      
		if( rate === 0 || rate < min_scale || rate > max_scale) return false;
		
	    if(Math.ceil(scale/10) != Math.ceil(rate/10)){
		   $('#photo_display img').css('transform',"scale("+rate/100+")");   
	    }
	   
	    if(parseInt(rate) > 110){
		    if(typeof $('#photo_display img').data('ui-draggable')  == 'undefined')  
		        $('#photo_display img').draggable();
	    }else{
		    if(typeof $('#photo_display img').data('ui-draggable')  != 'undefined') 
	            $('#photo_display img').draggable( "destroy" ).removeAttr('style'); 
        }
	   
	    $('#photo_display').data('is',rate);
	});
	
	
	//-- 放入圖片
	var img;
	function insert_object_to_page(objtype,objsrc){
	  
	  
	  switch(objtype){
		case 'jpg': case 'png':
          //$('.image_loading').show();
		  // cancle 上個load
		  //cancel_pre_action();
		  var area_h = $('#photo_display').height()-20;
		  var area_w  = $('#photo_display').width();
		  
		  img = new Image();  // 必須使用  new Image()  否則  firefox 下會由cache內取得image  造成  skip onload event
		   
		  img.onload = function(){ 
			var x = ( area_h / img.height ); 
			var img_h  = parseInt(img.height * x);
			var img_w  = parseInt(img.width * x);
			
			while( img_w > area_w ){
			  x-=0.01;
			  img_h = parseInt(img.height * x);
			  img_w = parseInt(img.width * x);
			}
			img.height = img_h;
			img.width = img_w;
			
			$('#photo_display').empty().append(img);
			$('#photo_display').data({"ih":img_h,"iw":img_w,"is":100});
			
			//$('.image_loading').hide();
		  }
		  img.src = objsrc;   		
		  
		  break;
		
		case 'pdf':
		  
          $('#photo_display').empty().append("<object data='"+objsrc+"' type='application/pdf' width='100%' height='100%'></object>"); 		
		  break;
		  
		default:break;; 
		
	  }
	  
	}
	
	
	// insert meta
	function insert_meta_to_page(doc_meta){
	  $.each( doc_meta , function( pfld , pval ){
		$("._metaval[data-field='"+pfld+"']").each(function(){
		  if($(this).hasClass('_edit')){
			if($(this)[0].tagName=='DIV'){
			  $(this).html(pval);
			}else{
			  $(this).val(pval);
			}
		  }else{
			$(this).html(pval);
		  }
		});
	  });
	  
      // setting check status
      var target = $(".rslItem[no='"+doc_meta.identifier+"']").find('.imgblock');
	  if(target.hasClass('selected')){
		$('#act_photo_selected').addClass('selected');  
	  }else{
		$('#act_photo_selected').removeClass('selected');   
	  }
	  
	  // setting editer
	  if(!parseInt(doc_meta.editable)){
		$('.data_option').hide();  
	  }else{
		$('.data_option').show();    
	  }
	  
	}
	
	//-- 圖片下載
	$('#act_object_download').click(function(){
      var image_id    =  $("._metaval[data-field='identifier']").text().length? $("._metaval[data-field='identifier']").text() : '';
	  
	  // check process data
	  if( !image_id.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  }
     
	  location.href='index.php?act=Archive/download/'+image_id;
	  $("._metaval[data-field='download']").html(parseInt($("._metaval[data-field='download']").text())+1);
	
	});
	
	//-- 切換圖片
	$('#act_photo_prev').click(function(){
	  var now_id = $("._metaval[data-field='identifier']").text();
      if(!now_id.length || !$(".rslItem[no='"+now_id+"']").length){
		system_message_alert('',"尚未選擇檔案"); 
		return false;
	  }
	  if(!$(".rslItem[no='"+now_id+"']").prev().length){
		system_message_alert('',"已於第一張圖片");   
	    return false;
	  }
	  $(".rslItem[no='"+now_id+"']").prev().find('.imgblock').trigger('dblclick');
	});
	
	$('#act_photo_next').click(function(){
	  var now_id = $("._metaval[data-field='identifier']").text();
      if(!now_id.length || !$(".rslItem[no='"+now_id+"']").length){
		system_message_alert('',"尚未選擇檔案"); 
		return false;
	  }
	  if(!$(".rslItem[no='"+now_id+"']").next().hasClass('rslItem')){
		if($('#marker').attr('slot') == '-'){
		  system_message_alert('',"已於最後一張圖片");   
	      return false;
		}else{
		  /* trigger lazyLoadXT */ 	
		  $('#marker').trigger('lazyshow');
		}
	  }
	  $(".rslItem[no='"+now_id+"']").next().find('.imgblock').trigger('dblclick');
	});
	
	
	//-- 旋轉圖片 #20170208 updated
	$('.photo_rotate').click(function(){
	  
	  // initial	  
	  var image_id    =  $("._metaval[data-field='identifier']").text().length? $("._metaval[data-field='identifier']").text() : '';
	  var act_object  = $(this);
	  
	  // check process data
	  if( !image_id.length ){
	    system_message_alert('',"尚未選擇資料");
	    return false;
	  }
	  
	  var rotate_mode = $(this).attr('id')=='act_rotate_left' ? 'left' : 'rigth'
	  
      $.ajax({
        url: 'index.php',
	    type:'POST',
	    dataType:'json',
	    data: {act:'Archive/rotate/'+image_id+'/'+rotate_mode},
		beforeSend: function(){ system_loading(); },
        error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	    success: 	function(response) {
		  if(response.action){
			system_message_alert('alert',"照片已旋轉");
		    $("._metaval[data-field='orlimgwh']").html(response.data);
			insert_object_to_page(meta_orl.file_type,meta_orl.objsrc);
		  }else{
			system_message_alert('',response.info);
	      } 
	    },
		complete:	function(){  }
      }).done(function(r) { system_loading(); });
	  
	});
	
	
	//-- 檢視模式勾選圖片
	$('#act_photo_selected').click(function(){
	  var now_id = $("._metaval[data-field='identifier']").text();	
	  var target = $(".rslItem[no='"+now_id+"']").find('.imgblock');
	  target.trigger('click');  
	  if(target.hasClass('selected')){
		$(this).addClass('selected');  
	  }else{
		$(this).removeClass('selected');   
	  }
	});
	
	
	
	/***-- ARCHIVE PHOTO SELECT DELETE --***/
	
	//-- 執行批次刪除
	$('#act_delete_selected').click(function(){
	  
	  
      if( !Object.keys(photo_selected).length   ){
		system_message_alert('',"尚未選擇檔案");  
	    return false;
	  }
	  
	  var remove = {};
	  remove['data']  = Object.keys(photo_selected); 
	  var passer_data  = encodeURIComponent(JSON.stringify(remove));
      
	  if(!confirm("確定要『刪除!!』所選資料?")) return false;
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/trash/'+passer_data},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
			if(response.action){		
			  //alert("已成功刪除"+response.data.remove.count+"筆資料，視窗關閉後將重新整理頁面");
		      window.location.reload();
			  //system_message_alert('alert','');
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() { system_loading(); }); 
	  
	});
	
	
	
	
	
	
	
	/***-- ARCHIVE PHOTO SELECT TO FOLDER --***/
	
	//-- 打開匯出設定區
	$('#act_folder_selected').click(function(){
	  $('.foldersel_area').css('display','flex');
	});
	
	//-- 關閉匯出設定區
	$('#act_foldersel_close').click(function(){
      $('.foldersel_area').css('display','none');	  
	});
	
	//-- 重新設定
	$('#act_folder_sel_reset').click(function(){
	  $('.set_folder').val('').next().val('').prop('readonly',false);   ;
	  $('#folder_set_pool').empty();
	});
	
	//-- 新增資料夾設定
	$('#act_add_put_folder').click(function(){
	  if($('.folder_setter').length > 2){
		system_message_alert('',"同時間最多設定三個資料夾")  
	    return false;
	  }
	  var DOM = $('#folder_put_templete .folder_setter').clone();
	  DOM.find('.set_folder,.set_value').val('');
	  DOM.find('.set_value').prop('readonly',false);
	  DOM.find('.set_option').empty();
	  DOM.appendTo('#folder_set_pool');
	});
	
	//-- set_folder select
	$(document).on('change','.set_folder',function(){
	  if($(this).val()){
		$(this).next().val($(this).find('option:selected').text()).prop('readonly',true);  
	  }else{
		$(this).next().val('').prop('readonly',false);   
	  }	
	});
	
	//-- 執行放到資料夾
	$('#act_folder_sel_active').click(function(){
	  console.log(photo_selected);
      var dom = $(this);	  
		
      if( !Object.keys(photo_selected).length   ){
		system_message_alert('',"尚未選擇檔案");  
	    return false;
	  }
	  
	  var folder = {};
	  folder['list']  = [];
	  folder['data']  = Object.keys(photo_selected); 
	  
	  // get forder
	  $('.set_folder').each(function(){
		if( $(this).val() ){
		  folder['list'].push($(this).val());
		}else if($(this).next().val()){
		  folder['list'].push($(this).next().val());
		} 
	  });
	  
	  if(!folder['list'].length){
		system_message_alert('',"尚未設定資料夾");  
	    return false;  
	  }
	 
	  var passer_data  = encodeURIComponent(JSON.stringify(folder));
      
	  if(!confirm("確定要將資料加入 "+folder['list'].length+" 個資料夾?")) return false;
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/putin/'+passer_data},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
			if(response.action){
		      $.each(response.data,function(fno,fname){
                var DOM = $("<li/>").addClass('act_folder').attr('no',fno);
				DOM.append("<span class='mark32 pic_myfolder' ></span>");
				DOM.append("<span>"+fname+"</span>");
				DOM.appendTo('#myfolder');
			  });
			  system_message_alert('alert','檔案已加入');
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() { system_loading(); }); 
	  
	});
	
	
	
	
	/***-- ARCHIVE PHOTO SELECT EXPORT --***/
	
	//-- 打開匯出設定區
	$('#act_export_selected').click(function(){
	  $('.exportsel_area').show();
	});
	
	
	//-- 關閉匯出設定區
	$('#act_exportsel_close').click(function(){
      $('.exportsel_area').hide();	  
	});
	
	//-- 重新設定
	$('#act_reset_sel_exp').click(function(){
	  $('textarea#exp_descrip').val('');
	  $("input[name='exp_type']").prop('checked',false);
	});
	
	//-- 執行資料打包
	$('#act_active_sel_exp').click(function(){
	  
      var dom = $(this);	  
		
      if( !Object.keys(photo_selected).length   ){
		system_message_alert('',"尚未選擇檔案");  
	    return false;
	  }
	  
	  if( !$("input[name='exp_type']:checked").length){
		system_message_alert('',"尚未選擇匯出類型");  
	    return false;    
	  }
	  
	  var download = {};
	  download['desc']  = $('textarea#exp_descrip').val();
	  download['type']  = $("input[name='exp_type']:checked").val();
	  download['data']  = Object.keys(photo_selected); 
	  var passer_data  = encodeURIComponent(JSON.stringify(download));
      
	  if(!confirm("確定要打包匯出所選資料?")) return false;
	  
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/export/'+passer_data},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
			if(response.action){
		      system_message_alert('alert','資料匯出打包中..');
			  system_event_regist('package',response.data);
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() { system_loading(); }); 
	  
	});
	
	//-- 資料打包下載
	$(document).on('click','.download_link',function(){
	  window.location.assign($(this).data('href'));
      $(this).hide(); 	  
	});
	
	
	/***-- ARCHIVE PHOTO SELECT EDITER --***/
	
	//-- 打開選擇編輯區
	
	$('#act_edit_selected').click(function(){
	  $('.editsel_area').css('display','flex');	
	  
	});
	
	$('#act_editsel_close').click(function(){
	  $('.editsel_area').css('display','none');
	  //$('#').trigger('click');
	});
	
	
	//-- 新增修改欄位 
	$('#act_add_mdf_filed').click(function(){
	  var DOM = $('#meta_set_templete .meta_setter').clone();
	  DOM.find('.set_field,.set_value').val('');
	  DOM.find('.set_option').empty().append("<i class='mark24 option pic_meta_field_del act_del_mdf_field' ></i>");
	  DOM.appendTo('#meta_set_pool');
	  
	});
	
	//-- 刪除增加的修改欄位
	$(document).on('click','.act_del_mdf_field',function(){
	  $(this).parents('.meta_setter').remove();
	});
	
	//-- 變更修改欄位
	$(document).on('change','.set_field',function(){
	  $(this).next().val('');	
	});
	
	
	//-- 更新專題 
	$('#act_upd_sel_cllv').click(function(){
	  
	  if( !Object.keys(photo_selected).length   ){
		system_message_alert('',"尚未選擇檔案");  
	    return false;
	  }
	  
	  var update  = {};
	  var MainDom = $(this).parent();
	  update[$(this).data('update')] = {};
 	  update[$(this).data('update')]['mode']  = MainDom.find("input[type='radio']:checked").val();
	  update[$(this).data('update')]['unify'] = MainDom.find("input.overwrite").prop('checked') ? 1 : 0 ;
	  update[$(this).data('update')]['value'] = $('#mdf_classlevel').text();
	  update[$(this).data('update')]['data']  = Object.keys(photo_selected); 
	  
	  var passer_data  = encodeURIComponent(JSON.stringify(update));
      
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/update/'+passer_data},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
			if(response.action){
		      system_message_alert('alert','已完成更新 '+response.data.update+' 項檔案');
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() { system_loading(); }); 
	  
	});
	
	//-- 更新標籤 
	$('#act_upd_sel_tags').click(function(){
	  
	  if( !Object.keys(photo_selected).length   ){
		system_message_alert('',"尚未選擇檔案");  
	    return false;
	  }
	  
	  var update  = {};
	  var MainDom = $(this).parent();
	  update[$(this).data('update')] = {};
 	  update[$(this).data('update')]['mode']  = MainDom.find("input[type='radio']:checked").val();
	  update[$(this).data('update')]['unify'] = MainDom.find("input.overwrite").prop('checked') ? 1 : 0 ;
	  update[$(this).data('update')]['value'] = $('#mdf_tags').text();
	  update[$(this).data('update')]['data']  = Object.keys(photo_selected); 
	  
	  var passer_data  = encodeURIComponent(JSON.stringify(update));
      
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/update/'+passer_data},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
			if(response.action){
		      system_message_alert('alert','已完成更新 '+response.data.update+' 項檔案');
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() { system_loading(); }); 
	  
	});
	
	
	//-- 更新欄位 
	$('#act_upd_sel_meta').click(function(){
	  
	  
	  if( !Object.keys(photo_selected).length   ){
		system_message_alert('',"尚未選擇檔案");  
	    return false;
	  }
	  
	  var update  = {};
	  var empey_alert = 0;
	  $('.meta_setter').each(function(){
		var field = $(this).find('.set_field').val(); 
		var value = $(this).find('.set_value').val(); 
		
		if(field){
		  update[field]	= {};
		  update[field]['value'] = $(this).find('.set_value').val(); 
		  update[field]['mode']  = '+';
		  update[field]['unify'] =  1;
		  update[field]['data']  = Object.keys(photo_selected); 
		
		  if(!value.length){
		    empey_alert++;	
		  } 
		}
	  });	
	  
	  if(empey_alert){
		system_message_alert('error',"注意!!! 有"+empey_alert+"項修改內容是空的，若更新將會清空原本內容!!!" );  
	  }
	  
	  var passer_data  = encodeURIComponent(JSON.stringify(update));
      
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/update/'+passer_data},
		  beforeSend: 	function(){ system_loading(); },
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
			if(response.action){
		      system_message_alert('alert','已完成更新 '+response.data.update+' 項檔案');
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() { system_loading(); }); 
	  
	});
	
	/***-- ARCHIVE PHOTO UPLOAD SET --***/
	
	//-- 打開上傳區
	
	var upload_process_fleg = false;
	
	$('#act_open_upload').click(function(){
	  $('.upload_area').css('display','flex');	
	  if ($('#upload_dropzone').is(':empty')){
        $('#upload_dropzone').addClass('dropzone_sign');
      }
	  
	  var upload_folder = '';
	  if( $('#upl_folder').is(':empty') ){
        if($('#upl_folder').data('cache')){
		  upload_folder = $('#upl_folder').data('cache');	
		}else{
		  var now = new Date(Date.now());
	      var month = parseInt(now.getMonth())+1;
		  month = month.toString().length==2 ? month : '0'+month.toString();
		  upload_folder = now.getFullYear()+"-"+month+"-"+now.getDate()+"_"+$('.acc_name').text();  
		}		 
		$('#upl_folder').val(upload_folder);
	  }
	  
	});
	
	
	$('#act_upload_close').click(function(){
	  $('.upload_area').css('display','none');
	  $('#act_clean_upload').trigger('click');
	});
	
	
	var $dropZone =  $("div#upload_dropzone").dropzone({
	  autoProcessQueue:false,
	  createImageThumbnails:false,
	  parallelUploads:1,
	  maxFiles:100,
	  url: "index.php?act=Archive/uplpho/", 
	  clickable: "#act_select_file",
	  paramName: "file",
	    init: function() {
		  this.on("addedfile", function(file) {
			  
			//file.fullPath
		    $('#complete_time').html('…');
			$('#act_active_upload').prop('disabled',false);
			$('#upload_dropzone').removeClass('dropzone_sign');
			
			/***-- 建立刪除按鈕 --***/
			// Create the remove button
			var removeButton = Dropzone.createElement("<i class='mark16 pic_photo_upload_delete option upl_delete' title='刪除'></i>");

			// Capture the Dropzone instance as closure.
			var _this = this;

			// Listen to the click event
			removeButton.addEventListener("click", function(e) {
			  // Make sure the button click doesn't submit the form:
			  e.preventDefault();
			  e.stopPropagation();

			  // Remove the file preview.
			  _this.removeFile(file);
			  // If you want to the delete the file on the server as well,
			  // you can do the AJAX request here.
			});

			// Add the button to the file preview element.
			file.previewElement.appendChild(removeButton);
		  });  
		},
		maxfilesreached: function(file){
		  system_message_alert("","到達上傳資料上限 100，若要增加檔案請清空後再重新加入");
		  //this.removeFile(file);
		},
		maxfilesexceeded: function(file){
		  this.removeFile(file);	
		},
	    sending: function(file, xhr, formData) {
		  // Will send the filesize along with the file as POST data.
		  formData.append("lastmdf", file.lastModified);
		  
		},
	    success: function(file, response){
		  result = JSON.parse(response);
		  if(result.action){
		    $(file.previewElement).addClass('dz-success');
		    $('#num_of_upload').html($('.dz-success').length);			
		  }else{
		    $(file.previewElement).addClass('dz-error');	
		    $(file.previewElement).find('.dz-error-message').children().html(result.info);
		  }
	    },
	    complete: function(file){
		  //-- maxfilesreached maxfilesexceeded 等超過檔案上限也會觸發
		  if( upload_process_fleg && this.getQueuedFiles().length){
		    this.processQueue();
		  }
		},
	    queuecomplete:function(){
		  
		  if(!upload_process_fleg){
			return false;  
		  }
		  
		  // finish folder upload state  
		  $.ajax({
		    url: 'index.php',
		    type:'POST',
		    dataType:'json',
		    data: {act:'Archive/uplend/'+$('#act_active_upload').data('folder')},
		    beforeSend: 	function(){ },
		    error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
		    success: 		function(response) {
			  if(response.action){
			    system_message_alert('alert',"資料上傳已經完成，檔案轉置中");	
				system_event_regist('import',response.data);
			  }else{
				system_message_alert('error',response.info);	  
			  }
		    },
		    complete:	function(){ }
		  }).done(function() { }); 
		  
		  var now = new Date(Date.now());
		  var formatted = now.getFullYear()+"/"+(parseInt(now.getMonth())+1)+"/"+now.getDate()+' '+now.getHours()+':'+now.getMinutes()+':'+now.getSeconds();   
		  $('#complete_time').html(formatted); 
		  
		  clearInterval(timer); 	// 關閉計時器
		  upload_button_freeze(0); 	// 打開上傳按鈕
		  upload = {};   			// 清空上傳暫存資料
		  upload_process_fleg = false 	// 關閉 fleg
		  
		}
	});
	
	//-- 啟動上傳
	var now_folder;
	var upload = {};
	$('#act_active_upload').click(function(){
	  
	 
	  
	  var button = $(this);
	  
	  if($(this).prop('disabled')){
		system_message_alert('','資料上傳中...');  
		return false;  
	  }	
	  
	  // 檢查上傳資料夾
	  if(!$('#upl_folder').val()){
		system_message_alert('','上傳資料夾不可為空');
		$('#upl_folder').focus();
        return false;		
	  }
	  
	  upload = {};
	  upload['folder']    = $('#upl_folder').val();
	  upload['list']      = [];
	  
	  $.each($dropZone[0].dropzone.getQueuedFiles(),function(i,file){
		var f={};
		f['name'] = file.name;
		f['type'] = file.type;
		f['size'] = file.size;
		f['lastmdf'] = file.lastModified;
		upload['list'][i] = f;
	  });
	  
	  
	  
	  
	  // 待上傳檔案不可為空
	  if(!upload['list'].length){
		system_message_alert('','待上傳檔案不可為空');
	    return false;	
	  }
	  
	  $('#num_of_queue').html(upload['list'].length);
	  var passer_data  = encodeURIComponent(JSON.stringify(upload));
	  
	   
	  
	  //先與server溝通上傳資料以及檢測資料檔案
	  $.ajax({
          url: 'index.php',
	      type:'POST',
	      dataType:'json',
	      data: {act:'Archive/uplinit/'+passer_data},
		  beforeSend: 	function(){ upload_button_freeze(1); upload_process_fleg=true;},
          error: 		function(xhr, ajaxOptions, thrownError) {  console.log( ajaxOptions+" / "+thrownError);},
	      success: 		function(response) {
			
			if(response.action){

              $('#act_active_upload').data('folder',response.data.upload.folder+'-'+response.data.upload.tmflag);	
			  
			  // 檢查是否重複檔案
			  $.each(response.data.upload.check,function(i,pho){
				if(pho.check=='double'){
				  $(".dz-preview:nth-child("+(i+1)+")").find('.dz-size').append("<span class='upl_double' title='重複'> - 重複</span>");
				}
			  });
			  
			  if($('.upl_double').length){
				if(confirm("發現重複檔案,請問是否要調整上傳清單?")){
				  upload_button_freeze(0);
				  return 1;  
				}   
			  }
			  process_files_upload();
			}else{
			  system_message_alert('error',response.info);
			}
	      },
		  complete:	function(){ }
	  }).done(function() { }); 
	  
	});
	
	
	//-- 執行上傳檔案
	function process_files_upload(){
	  upload['creater']   = $('#upl_creater').val();
	  upload['classlv']   = $('#upl_classlevel').text(); 
	  upload['list'] 	  = [];
	  $dropZone[0].dropzone.options.url="index.php?act=Archive/uplpho/"+$('#act_active_upload').data('folder')+'/'+encodeURIComponent(Base64M.encode(JSON.stringify(upload)));
	  $dropZone[0].dropzone.processQueue();
	  upload_timer();
	}
	
	
	//-- 清空上傳清單
	$('#act_clean_upload').click(function(){
	  $dropZone[0].dropzone.removeAllFiles( true );
	  $("#num_of_upload,#num_of_queue").html('0');
	  $("#execute_timer").html('0');
	  $("#complete_time").html('…');
	  $("#upload_dropzone").addClass('dropzone_sign').empty();
	  $('#act_active_upload').prop('disabled',true);
	});
	
	
	//-- 上傳計時器
	var timer;
	var totalSeconds = 0;
	function upload_timer(){
	  totalSeconds = 0;
      timer = setInterval(setTime, 1000);
      function setTime(){
        ++totalSeconds;
        $('#execute_timer').html(pad(parseInt(totalSeconds/60))+':'+pad(totalSeconds%60));
      }
      function pad(val){
        var valString = val + "";
        if(valString.length < 2){
          return "0" + valString;
        }else{
          return valString;
        }
      }
	}
	
	//-- 上傳按鈕凍結
	function upload_button_freeze(option){
      if(option){
		$('#act_active_upload,#act_select_file').prop('disabled',true);  
	  }else{
		$('#act_active_upload,#act_select_file').prop('disabled',false);    
	  }
	}
	
	//-- 清空已上傳檔案
	$('#act_select_file' ).click(function(){
	  var dzObject = $dropZone[0].dropzone;
	  $.each(dzObject.getAcceptedFiles(),function(i,file){
		if(file.status == 'success'){
		  dzObject.removeFile(file);	  
		}
	  });
	});
	
	
	
	/* 編輯API */
	// install suggest resource : class level
	var lvrefer = [];
	$('.LevelTerm').each(function(i){
	  lvrefer[i] = {};
	  lvrefer[i]['name'] = $(this).attr('level');
	  lvrefer[i]['info'] = $(this).attr('info');
	  lvrefer[i]['code'] = $(this).attr('code');
	});
	
	
	//-- meta suggest dom initial
	$('._suggest').focus(function(){
	  var main_field = $(this).attr('id');
	  
	  if( main_field == $('#meta_suggest').attr('support')){
		return true;
	  }
	  $('#meta_suggest').empty().hide();
	  var position   = $(this).offset();  
	  var location_right = $(window).width()-position.left;
	  $('#meta_suggest').attr('support',main_field).css({'top':position.top+'px','right':(location_right+20)+'px'});
	});
	
	var keyinbuffer = '';
	var keyinset    = [];
	
	function classlv_suggest(DomObj,Refer){
	  var content  =  DomObj.text();
          keyinset = content.split(/;|；/);	  
	  var nowkeyin = keyinset.pop();	
	  var suggest  = [];	
	  var target_dom = DomObj;
	  
	  if(!nowkeyin.length){ $("#meta_suggest").hide(); }
	  if(!nowkeyin.length || keyinbuffer==nowkeyin ){  return false; }
	  keyinbuffer=nowkeyin;
	  
	  var pattern=nowkeyin.split(' ');
	  var re = new RegExp(pattern.join('.*?'),"g");
	  $("#meta_suggest").empty();
	  $.each(Refer,function(i,rfset){
		if(rfset['name'].match(re)){
		  $("#meta_suggest[support='"+DomObj.attr('id')+"']").append("<li code='"+rfset['info']+"'>"+rfset['name']+"</li>");
		  suggest.push(rfset);
		}
	  });
	  
	  if(suggest.length){
		$("#meta_suggest").show();  
	  }
	}
	
	$(document).on('click','#meta_suggest li',function(){
	  keyinset.push($(this).text());
	  var support_dom = $(this).parent().attr('support');
	  $('#'+support_dom).html(jQuery.unique(keyinset).join(';')+';');
	  placeCaretAtEnd( document.getElementById(support_dom) );
	  $(this).parent().empty().hide();  
	});
	
	
	// 游標置尾
	function placeCaretAtEnd(el) {
		el.focus();
		if (typeof window.getSelection != "undefined"
				&& typeof document.createRange != "undefined") {
			var range = document.createRange();
			range.selectNodeContents(el);
			range.collapse(false);
			var sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);
		} else if (typeof document.body.createTextRange != "undefined") {
			var textRange = document.body.createTextRange();
			textRange.moveToElementText(el);
			textRange.collapse(false);
			textRange.select();
		}
	}

	//-- 掛載建議  API
	var bindEvent = iedetect() ? "DOMCharacterDataModified" : "input";
	
	
	if($('#upl_classlevel').length){
	  document.getElementById("upl_classlevel").addEventListener(bindEvent, function() {
		classlv_suggest($(this),lvrefer);
	  }, false);
	}
	
	if($('#mdf_classlevel').length){
	  document.getElementById("mdf_classlevel").addEventListener(bindEvent, function() {
		classlv_suggest($(this),lvrefer);
	  }, false);
	}
	
	
	if($('#meta_classlevel').length){
	  document.getElementById("meta_classlevel").addEventListener(bindEvent, function() {
		classlv_suggest($(this),lvrefer);
	  }, false);
	}
	
	if($('#mdf_tags').length){
	  document.getElementById("mdf_tags").addEventListener(bindEvent, function() {
		classlv_suggest($(this),tgrefer);
	  }, false);
	}  	
	
	//--  button loading 
	function action_loading(DOM){
      sysloading = new CanvasLoader('sysloader');
      sysloading.setColor('#449dc7'); // default is '#000000'
      sysloading.setShape('spiral'); // default is 'oval'
      sysloading.setDiameter(30); // default is 40
      sysloading.setDensity(30); // default is 40
      sysloading.setRange(0.7); // default is 1.3
      sysloading.setFPS(20); // default is 24
      sysloading.hide(); // Hidden by default
	}
	
	
	
	//-- 綁定框選 ( lazeload 也有需要綁定 drop ....)
	$('.work_block' )
		.drag("start",function( ev, dd ){
			return $('<div class="selection" />')
				.css('opacity', .65 )
				.appendTo( document.body );
		})
		.drag(function( ev, dd ){
			$( dd.proxy ).css({
				top: Math.min( ev.pageY-10, dd.startY-10 ),  //cover 高一點才不會遮到檔案讀取
				left: Math.min( ev.pageX, dd.startX ),
				height: Math.abs( ev.pageY - dd.startY ),
				width: Math.abs( ev.pageX - dd.startX )
			});
		})
		.drag("end",function( ev, dd ){
			$( dd.proxy ).remove();
		});
		
	$('.rslItem')
		.drop("start",function(){
			$( this ).addClass("active");
		})
		.drop(function( ev, dd ){
			//$( this ).toggleClass("dropped");
			$( this ).children('div.imgblock:not(.selected)').trigger('click');
			
		})
		.drop("end",function(){
			$( this ).removeClass("active");
		});
	
	$.drop({ multi: true });
	
	
	//-- 綁定快速鍵功能
	var fastKeyFunctionFlag = 0; 
	var fastKeyAllSelectFlag   = 0;
	
	$('#act_function_key').mousedown(function(){
	  $(this).addClass('press');
	  $('.funckey_area').show();
	}).mouseup(function(){
	  $(this).removeClass('press');
	  $('.funckey_area').hide();
	});
	
	
	$('.work_block').mouseenter(function(){
	  fastKeyFunctionFlag = 1; 
	}).mouseleave(function(){
	  fastKeyFunctionFlag = 0; 
	});
	
	// fast key function //快速鍵功能
	$(document).keydown(function(event){
	  if(!fastKeyFunctionFlag){
	    return true;
	  }
	  
	  switch(event.keyCode){
	    case 18: $('#act_function_key').trigger('mousedown'); break; // alt 開啟快速鍵功能
	    case 65: fastKeyAllSelectFlag=1; break; // a 目前資料全部選擇
		case 83: // s 視區全選  
          
		  if(!event.altKey) return false;
		  
		  $(".rslItem[no!='sample']").each(function(){
		    var imgdom = $(this).children('.imgblock');
			if( imgdom.hasClass('selected') ){
			  return true;
			}
			if(fastKeyAllSelectFlag){
			  imgdom.trigger('click');
			}else{
			  switch(imageBlockIsVisible($(this))){
			    case 'in': imgdom.trigger('click');  break;
				case 'over': return false; break;
				default:break;
			  }
			}
		  });
          break;
	  }
	});
	
	$(document).keyup(function(event){
      switch(event.keyCode){
	    case 18: $('#act_function_key').trigger('mouseup'); break; //關閉快速鍵功能 
		case 65: fastKeyAllSelectFlag=0; break; 
		default: return true;
	  }
	});
	
	function imageBlockIsVisible($el) {
      var winTop = $('.work_block ').scrollTop();
      var winBottom = winTop + $('.work_block ').height();
      var elTop = $el.offset().top-80;
      var elBottom = elTop + $el.height();
	  
	  if(((elTop<= winBottom) && (elTop >= winTop))){
	    return 'in';
	  }else if( elTop > winBottom ){
	    return 'over';
	  }else{
	    return 'out';
	  }
    }  
	
	
	
	
	
  });  //-- end of initial --//
  
  
  
  
  


  
  
  
  
  
  
  
  


