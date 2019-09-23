/* [ Admin Staff Function Set ] */
	
  
  $(window).load(function () {   //  || $(document).ready(function() {		
	
	 
	
	//-- 繼續填寫
	$('.act_evaluate_continue').click(function(){
      location.href='index.php?act=Evaluation/mett/'+$(this).attr('no')+'/'+$('#evaluate_history').val();
	})
	
	//-- 切換章節
	$('.section_switch > li.session').click(function(){
	  var switch_to = $(this).attr('dom');	
	  if($('#'+switch_to).length){
		$('.data_record_block').hide();  
		$('#'+switch_to).show();  
	    $('li.session.active').removeClass('active');
		$(this).addClass('active');
	  }
	})
	
	//-- 問題導覽
	$('.guide_area > li').click(function(){
	  var block_id = $(this).attr('dom');
	  location.hash = '#'+block_id;
	  $('.guide_area > li').removeClass('active');
	  $(this).addClass('active');
	});
	
	
	//-- 手動擴充內容
	$('.act_add_value').click(function(){
	  var main_dom = $(this).parents('.data_col');	
	  var uldom    = main_dom.find('ul.increase_form'); 
	  if(main_dom.find('li.pattern').length){
		var listrecordnum = uldom.find('li.listrecord:not(.pattern)').length;
		var newrecordid   = uldom.attr('id')+'add'+(listrecordnum+1);
		var new_input = main_dom.find('li.pattern').clone();
        new_input.removeClass('pattern');
		new_input.attr('id',newrecordid);
		new_input.appendTo(uldom);
	  }
	});
	
	//-- 手動擴充內容
	$(document).on('click','.act_remove_element',function(){
	  var main_dom = $(this).parents('.data_col');	
	  var uldom    = main_dom.find('ul.increase_form'); 
	  var record_dom = $(this).parents('li.listrecord');	
	  record_dom.remove();
	});
	
	
	//-- 複製評論
	$('.act_copy_emedescrip').click(function(){
	  var main_dom = $(this).parents('h2');
	  const lastdescrip = main_dom.next().html();
	  const thisdescrip = main_dom.prev().val()
	  main_dom.prev().val(thisdescrip+lastdescrip).trigger('change');
	})
	
	//-- 計算面積
	$(document).on('change','input[name="emd0800_area"]',function(){
	  var area_total = parseInt($('#emd0501').val())+parseInt($('#emd0502').val())	
	  var area_owner = parseInt($(this).val());
	  var area_count = (area_owner/area_total*100).toFixed(2);
	  $(this).next('input[name="emd0800_proportion"]').val(area_count);
	});
	
	//-- 計算總預算
	$(document).on('focus','input[name="emd1502"]',function(){
	  var total_found = $(this).val();
	  if(!parseInt(total_found)){
		var total_count = $('input[name="emd1300_funding"]').map(function(){if($(this).val()){return parseInt($(this).val()); }}).get().reduce(function (a, b) {return a + b;}, 0);
        $(this).val(total_count).addClass('_modify');
	  }
	});
	
	
	
	
	
	
	//-- 點td即點input
	/*
	$('td.checked').click(function(){
	  if( $(this).children('input._variable').length){
		  
		var form_element = $(this).children('input._variable');
		if(form_element.attr('type')=='radio'){
		  form_element.prop('checked',true);	
		}else{
		  var change = form_element.prop('checked') ? false : true;
		  form_element.prop('checked', change);	
		}
	  }
	})
	*/
	
	
	//-- 開關進一步
	$('._furthercheck').change(function(){
	  var effect_area   = $(this).parents('tbody');
	  var further_check = effect_area.find('input._furthercheck:checked').map(function(){return parseInt($(this).val());}).get();
	  if(further_check.reduce(function (a, b) { return a + b; }, 0)){
		effect_area.next().show();  
	    effect_area.find('.furtherdescrip').show().focus();
	  }else{
		effect_area.next().hide();
		effect_area.find('.furtherdescrip').hide()
	  }
	});
	
	//-- 左右版面關聯
	$('._variable._update').on('focus',function(){
	  var block_dom = $(this).parents('._hashentry');
	  if($("li[dom='"+block_dom.attr('id')+"']").length){
		$('ul.guide_area li.active').removeClass('active');
		$("li[dom='"+block_dom.attr('id')+"']").addClass('active'); 		
	  }
	});
	
	
	
	//-- 評量區域選擇
	$('#record_area').change(function(){
		
		let target_area = $(this).val();
		
		if($(this).val()){
			$('#evaluate_history').val('').find('option').each(function(){  if(target_area == $(this).attr('area')){ $(this).show(); }else{$(this).hide();} })	
		}else{
			$('#evaluate_history').val('').find('option').show();
		}
		
	});
	
	
	
	//-- 刪除評量資料
	$('.act_evaluate_delete').click(function(){
	   
	  var record_id  = $(this).attr('no');
	  var record_dom = $(this).parents('li');
	  
	  if(!record_id){
		system_message_alert("","資料尚未建立，請先完成訪談基本資料!!");  
	    return false;
	  }
	  
	  if(!confirm("確定要刪除本評量資料?!")){
		return false;  
	  } 
	    
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Evaluation/delrecord/'+record_id},
		beforeSend: function(){  system_loading();  }
      }).done(function(response) {
	    
		if(!response.action){
		  system_message_alert('',response.info);
		  return true;
		}
		
		record_dom.remove();
		
	  }).fail(function(xhr, ajaxOptions, thrownError) {
        console.log( ajaxOptions+" / "+thrownError);
	  }).always(function(r){
        system_loading();
      });
		  	
	});
	
	
	
	//-- 檢查評量資料
	$('.act_check_input').click(function(){
		 
		if(!$('li.session.active').length) return true;
		
		let target_table = $('li.session.active').attr('dom');
		
		switch(target_table){
			case 'questionnaire_mettdata':
			    $('#questionnaire_tendency').find('._need').removeClass('_need');
				
				
				
				
				
				
				break;
			case 'questionnaire_tendency':
			    $('#questionnaire_tendency').find('._need').removeClass('_need');
				$('.tendency_block').each(function(){
					if($(this).find('input._variable._update._furthercheck:checked').length){
					    $(this).find('input._variable._update._furthercheck:checked').each(function(){
					        if(parseInt($(this).val())){
							    let table_dom = $(this).parents('.tendency_block');
                                if(!table_dom.find('input[name$="descrip"]').val())  table_dom.find('input[name$="descrip"]').addClass('_need');
								table_dom.find('.optionset').each(function(){
									if(!$(this).find('input[type="radio"]:checked').length) $(this).addClass('_need');
								})
						    }
						})
				    }else{
						$(this).parents('.data_col').addClass('_need');
					}	
				});
				break;
				
			case 'questionnaire_evaluate':
				
				$('#questionnaire_evaluate').find('._need').removeClass('_need');
				
				$('#questionnaire_evaluate').find('tbody').each(function(){
					if($(this).find('input._variable._update[type="radio"]').length && !$(this).find('input._variable._update[type="radio"]:checked').length){
					    $(this).addClass('_need');
					}
				});
				
				$('#questionnaire_evaluate').find('input._descrip:checked').each(function(){
					let descdom = '';
					if($(this).parents('div.exclude_record').find('input[type="text"]').length){
					  	descdom = $(this).parents('div.exclude_record').find('input[type="text"]');
					}else if($(this).parents('tr').find('td.descrip').find('input[type="text"]').length){
						descdom = $(this).parents('tr').find('td.descrip').find('input[type="text"]') ;
					}else if($(this).parents('tbody').find('td.descrip').find('textarea').length){
						descdom = $(this).parents('tbody').find('td.descrip').find('textarea');
					}
					
					if(!descdom.val()){
						descdom.addClass('_need')
					}else{
						descdom.removeClass('_need');
					}
				})
			    break;
		}
		
		if($('._need').length){
			system_message_alert('',"仍有"+$('._need').length+"位置資料未填!!!!");
		    return false;
		}else{
			system_message_alert('alert',"本區已填寫完畢");
		}
		
	})
	
	
	
	
	
	
	//-- 遞交評量
	$('#act_submit_evaluation').click(function(){
	   
	  var record_id 	= $('#record_id').attr('no');
	  var field_dom		= $(this);
	  
	  if(!record_id){
		system_message_alert("","資料尚未建立，請先完成訪談基本資料!!");  
	    return false;
	  }
	  
	  
	    // 檢查表單
		$('.system_content_area').find('._need').removeClass('_need');
		
	    
		$('.tendency_block').each(function(){
			if($(this).find('input._variable._update._furthercheck:checked').length){
				$(this).find('input._variable._update._furthercheck:checked').each(function(){
					if(parseInt($(this).val())){
						let table_dom = $(this).parents('.tendency_block');
						if(!table_dom.find('input[name$="descrip"]').val())  table_dom.find('input[name$="descrip"]').addClass('_need');
						table_dom.find('.optionset').each(function(){
							if(!$(this).find('input[type="radio"]:checked').length) $(this).addClass('_need');
						})
					}
				})
			}else{
				$(this).parents('.data_col').addClass('_need');
			}	
		});
		if($('._need').length){
		    system_message_alert('',"(二) 保護區的壓力表仍有"+$('._need').length+"位置尚未填寫，請補填後再次遞交!");  
	        return false;
	    }
	 	
		
		$('#questionnaire_evaluate').find('tbody').each(function(){
			if($(this).find('input._variable._update[type="radio"]').length && !$(this).find('input._variable._update[type="radio"]:checked').length){
				$(this).addClass('_need');
			}
		});
		
		$('#questionnaire_evaluate').find('input._descrip:checked').each(function(){
			let descdom = '';
			if($(this).parents('div.exclude_record').find('input[type="text"]').length){
				descdom = $(this).parents('div.exclude_record').find('input[type="text"]');
			}else if($(this).parents('tr').find('td.descrip').find('input[type="text"]').length){
				descdom = $(this).parents('tr').find('td.descrip').find('input[type="text"]') ;
			}else if($(this).parents('tbody').find('td.descrip').find('textarea').length){
				descdom = $(this).parents('tbody').find('td.descrip').find('textarea');
			}
			
			if(!descdom.val()){
				descdom.addClass('_need')
			}else{
				descdom.removeClass('_need');
			}
		})
	   
	    if($('._need').length){
		    system_message_alert('',"(三) 經營管理評量表仍有"+$('._need').length+"位置尚未填寫，請補填後再次遞交!");  
	        return false;
	    }
	   
	  
	  if(!confirm("確定完成本次的評量?!")){
		return false;  
	  } 
	    
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Evaluation/finish/'+record_id},
		beforeSend: function(){  system_loading();  }
      }).done(function(response) {
	    
		if(!response.action){
		  system_message_alert('',response.info);
		  return true;
		}
		
		location.href = 'index.php?act=Evaluation/index';
		
	  }).fail(function(xhr, ajaxOptions, thrownError) {
        console.log( ajaxOptions+" / "+thrownError);
	  }).always(function(r){
        system_loading();
      });
		  	
	});
	
	//-- 下載評量資料
	$('#act_download_evaluation').click(function(){
	  window.open("index.php?act=Evaluation/download")
	})
	
	
	//-- 讀取資料
	$('#act_get_record').click(function(){
	  var record_id 	= $('#record_id').attr('no');	
	  load_record_to_page(record_id,''); 
	});
	
	//-- 建立評量
	$('#act_create_record').click(function(){
	  
	  var session_dom = $('#questionnaire_information'); 
	  var record_data = {};
	  var record_check=true;
	  
	  session_dom.find('._error').removeClass('_error');
	  session_dom.find('._update').each(function(){
		if(!$(this).val()){
		  $(this).addClass('_error')	
		  system_message_alert('','請填寫必要資訊!!');
		  record_check = false;
		  return false;
		} 
		record_data[$(this).attr('name')] = $(this).val();
	  });
	  
	  if(!record_check){
		return false;  
	  }
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(record_data)));
	  
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Evaluation/create/'+passer_data},
		beforeSend: function(){  system_loading(); }
      }).done(function(response) {
	    
		if(!response.action){
		  system_message_alert('',response.info);
		  return true;
		}
		
		// 輸入資料
		$('#record_id').attr('no',response.data.newa).html(response.data.newa);
		
		
		// 開啟介面
		$('li.session.active').next().trigger('click');
		
		// 修正網址
		history.replaceState({}, "", 'index.php?act=Evaluation/mett/'+response.data.newa);
		
		//開始評量
		load_record_to_page(response.data.newa,$('#evaluate_history').val());
		
		
	  }).fail(function(xhr, ajaxOptions, thrownError) {
        console.log( ajaxOptions+" / "+thrownError);
	  }).always(function(r){
        system_loading();
      });  
	  
	});
	
    
	//-- 儲存資料
	$(document).on('change','._variable._update',function(){
	  
	  var area_dom 		= $(this).parents('.data_record_block');
	  var record_id 	= $('#record_id').attr('no');
	  var table_name 	= area_dom.data('table');
	  var field_dom		= $(this);
	  var record_data 	= {};
	  if(!record_id){
		system_message_alert("","資料尚未建立，請先完成訪談基本資料!!");  
	    return false;
	  }
	  
	  if(!table_name){
		system_message_alert("","錯誤的評量區塊，請重新建立訪談記錄!!");  
	    return false;  
	  }
	  
	  if($(this).val() === $(this).data('meta')){
		return true;  
	  }
	  
	  // oth 欄位應轉換為主體欄位
	  if($(this).hasClass('_expend') && typeof $(this).attr('partof')!='undefined'){
		field_dom = $('._update[name="'+$(this).attr('partof')+'"]');
	  }else if($(this).hasClass('_element') && typeof $(this).attr('partof')!='undefined'  ){
		field_dom = $(this).parents($(this).attr('partof'));
	  }
	  
	  
	  // 取得內容
	  if(field_dom.prop("tagName")=='INPUT'){
        var field_name  = field_dom.attr('name');
        var input_value = '';
		if(field_dom.attr('type')=='checkbox'){
		  input_value = $('input[name="'+field_name+'"]:checked').map(function(){ return $(this).val();}).get().join(';');
		}else if(field_dom.attr('type')=='radio'){
		  input_value = $('input[name="'+field_name+'"]:checked').val();	
		}else if(field_dom.attr('type')=='text'){
		  input_value = field_dom.val();
		}
	    record_data[field_name] = input_value;
	  
	  }else if(field_dom.prop("tagName")=='TEXTAREA' || field_dom.prop("tagName")=='SELECT'){
		var field_name  = field_dom.attr('name');
        var input_value = field_dom.val();
        record_data[field_name] = input_value;
		
	  }else if(field_dom.prop("tagName")=='UL' && field_dom.hasClass('increase_form')){
		// 結構資料儲存 
		var field_name = field_dom.attr('id');
		var input_value={};
		field_dom.find('li.listrecord:not(.pattern)').each(function(){
		  var value_set_id = $(this).attr('id');
		  input_value[value_set_id] = {};
		  $(this).find('._update').each(function(){  
		    input_value[value_set_id][$(this).attr('name')] = $(this).val();
		  });
		}); 
		record_data[field_name] = input_value;
		
	  }else{
		  
	  }
	  
	  
	  // 項目相關動作
	  if(typeof field_dom.attr('bind') != 'undefined' && $(field_dom.attr('bind')).length){
		var relate_dom =  $(field_dom.attr('bind'));
        switch(field_dom.attr('effect')){
		  case 'disabled': relate_dom.prop('disabled',true).val('');  record_data[relate_dom.attr('name')]='';  break;
          case 'editable': relate_dom.prop('disabled',false).focus(); break;
		  case 'together': // 如果有內容就打開
		   if(field_dom.val()){
			  relate_dom.prop('disabled',false).focus();  	
			}else{
			  relate_dom.prop('disabled',true).val('');  record_data[relate_dom.attr('name')]='';	
			}
		    break; 
		  
		  case 'switch'	 : // 如果勾選就打開 
		    if(field_dom.prop('checked')){
			  relate_dom.prop('disabled',false).focus();  	
			}else{
			  relate_dom.prop('disabled',true).val('');  record_data[relate_dom.attr('name')]='';	
			}
		    break;
			
          case 'bundle':
		    if(relate_dom.val()){
			  record_data[field_name] +=';'+relate_dom.val();	
			}
		    break;
		  default:break;;
		}
	  }
	  
	  console.log(record_data)
	  
	  
	  // encode data
	  var passer_data  = encodeURIComponent(Base64M.encode(JSON.stringify(record_data)));
	  
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Evaluation/update/'+record_id+'/'+table_name+'/'+passer_data},
		beforeSend: function(){   }
      }).done(function(response) {
	    
		if(!response.action){
		  system_message_alert('',response.info);
		  return true;
		}
		
		if(field_dom.hasClass('_modify')){
		  field_dom.removeClass('_modify').find('._modify').removeClass('_modify');;
		}
		
		if(field_dom.find('._modify').length){
		  field_dom.find('._modify').removeClass('_modify');	
		}
		
		field_dom.removeClass('_need');
		
		
	  }).fail(function(xhr, ajaxOptions, thrownError) {
        console.log( ajaxOptions+" / "+thrownError);
	  }).always(function(r){
        //system_loading();
      });
		
	});
	
	
	//-- 帶入上一次填寫資料 (僅適用於保護區資料表)
	$('#act_import_lastone').click(function(){
	  
	  var area_dom 	= $(this).parents('.data_record_block');
	  var record_id = $('#record_id').attr('no');
	  var table_name= area_dom.data('table');
	  var field_dom	= $(this);
	  
	  if(!record_id){
		  system_message_alert("","資料尚未建立，請先完成訪談基本資料!!");  
	      return false;
	  }
	    
	  if(!table_name){
		  system_message_alert("","錯誤的評量區塊，請重新建立訪談記錄!!");  
	      return false;  
	  }
	  
	  // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Evaluation/bringin/'+record_id},
		beforeSend: function(){   }
      }).done(function(response) {
	     
		
		if(!response.action){
		  system_message_alert('',response.info);
		  return true;
		}
		
		load_record_to_page(record_id,$('#evaluate_history').val());  
		
	  }).fail(function(xhr, ajaxOptions, thrownError) {
        console.log( ajaxOptions+" / "+thrownError);
	  }).always(function(r){
        //system_loading();
      });
		
	});
	
	
	
	
	//-- 讀取資料到頁面 
    function load_record_to_page(record_id,history_id){  	       
      if(!record_id){
		system_message_alert("","資料尚未建立，請先完成訪談基本資料!!");  
	    return false;
	  }  	   
       
      // active ajax
	  $.ajax({
		url: 'index.php',
		type:'POST',
		dataType:'json',
		data: {act:'Evaluation/read/'+record_id+'/'+history_id},
		beforeSend: function(){  system_loading(); }
      }).done(function(response) {
	    
		if(!response.action){
		  system_message_alert('',response.info);
		  return true;
		}
		var data_obj = response.data.record;
		
		data_obj.target
		data_obj.history
		
		//將資料輸入系統
		$.each(data_obj.target,function(table,meta){
		  
		  var main_form = $('#'+table);
		  $.each(meta,function(mf,mv){
            
			var   form_set   = main_form.find('._update[name="'+mf+'"]');
			const form_count = form_set.length;
			
			if(form_count == 1){ // 單向資料

			  if(mv){
				
				if(form_set.prop('tagName')=='UL' && form_set.hasClass('increase_form') ){
				  
				  var recordgroup = JSON.parse(mv)
				  $.each(recordgroup,function(recordid,valueset){
					if($('#'+recordid).length){
					  var lidom = $('#'+recordid);
					}else{
					  if(form_set.find('li.pattern').length){
						var lidom = form_set.find('li.pattern').clone();
						lidom.removeClass('pattern');
						lidom.attr('id',recordid);
						lidom.appendTo(form_set);
					  }	 
					}
					$.each(valueset,function(fname,fvalue){
						lidom.find('._update[name="'+fname+'"]').val(fvalue)
					})
				  });
				  
				  
			    }else if(form_set.prop('tagName')=='INPUT' && form_set.attr('type')=='checkbox' ){
				  form_set.prop('checked',(parseInt(mv) ? true: false));
			    }else{
				  form_set.val(mv).data('meta',mv);
			      if(form_set.prop('disabled')) form_set.prop('disabled',false);
				}
			  
			    //開關對應欄位
				if(typeof form_set.attr('bind') != 'undefined' && $(form_set.attr('bind')).length){
					var relate_dom =  $(form_set.attr('bind'));
					switch(form_set.attr('effect')){
					  case 'disabled': relate_dom.prop('disabled',true).val('');  break;
					  case 'editable': relate_dom.prop('disabled',false); break;
					  case 'switch'	 : // 如果勾選就打開 
						if(form_set.prop('checked')){
						  relate_dom.prop('disabled',false);  	
						}else{
						  relate_dom.prop('disabled',true).val('');	
						}
						break;
					  default:break;;
					}
				}
			  
			  
			  }else{
				if(form_set.prop('tagName')=='INPUT' && form_set.attr('type')=='checkbox' ){
				  form_set.prop('checked',false);  
			    }else{
				  form_set.val('').data('meta','');
				}
			  }
			   
			}else if((form_count > 1)){ // 多項資料
              
			  if(!mv) mv = ''; 
			   
			  var value_set = mv.split(';');
			  form_set.each(function(){
				$(this).prop('checked',false);  
				const search = value_set.indexOf($(this).val());
				if(search != -1 ){
				  
				  $(this).prop('checked',true);
				  value_set[search] = '';
				  
				  //開關對應欄位
				  if(typeof $(this).attr('bind') != 'undefined' && $($(this).attr('bind')).length){
					var relate_dom =  $($(this).attr('bind'));
					switch($(this).attr('effect')){
					  case 'disabled': relate_dom.prop('disabled',true).val('');  break;
					  case 'editable': relate_dom.prop('disabled',false).focus(); break;
					  case 'switch'	 : // 如果勾選就打開 
						if($(this).prop('checked')){
						  relate_dom.prop('disabled',false).focus();  	
						}else{
						  relate_dom.prop('disabled',true).val('');	
						}
						break;
					  default:break;;
					}
				  }
				  
				}	
			  })
			  
			  var value_other = value_set.filter(function(el) { return el; });
			  if(main_form.find('._update[name="'+mf+'oth"]').length){
				main_form.find('._update[name="'+mf+'oth"]').val(value_other.join(';'));  
			  }
			}
			
		  })
		  
		});
		
		// 檢核 further table 
		$('tbody.furthermain').each(function(){
          var effect_area   = $(this);
		  var further_check = effect_area.find('input._furthercheck:checked').map(function(){return parseInt($(this).val());}).get();
		  if(further_check.reduce(function (a, b) { return a + b; }, 0)){
			effect_area.next().show();  
			effect_area.find('.furtherdescrip').show().focus();
		  }else{
			effect_area.next().hide();
			effect_area.find('.furtherdescrip').hide()
		  }		
		});
		
		console.log(data_obj.history)
		
		// 載入歷史資料
		$.each(data_obj.history,function(hisindex,recordset){
		  $.each(recordset,function(rtable,rmeta){
            $.each(rmeta,function(mf,mv){
			  
			  var formset  = $('._variable._history[name="H-'+mf+'"][hisindex="'+hisindex+'"]');
			  if(!formset.length) return true;
			  var formtype = formset.prop('tagName')+(typeof formset.attr('type')!='undefined' ? formset.attr('type') : '');   
               
			  switch(formtype){
				case 'INPUTtext'	:  mv ? formset.val(mv) : formset.val('未填'); break;  
				case 'INPUTradio'	:  formset.each(function(){ if($(this).val()==mv) $(this).prop('checked',true); });  break;  
                case 'DIV'			: 
			    case 'SPAN'			:	
				  if($('input[name="'+mf+'"][type="radio"]').length){
					if(parseInt(mv) && $('input[name="'+mf+'"][type="radio"]').eq((parseInt(mv)-1)).length ){
						formset.html( mv + '('+$('input[name="'+mf+'"][type="radio"]').eq((parseInt(mv)-1)).val()+')')
					}else{
					    if(mv){
							formset.html(mv)
						}else{
							formset.html('未填')
						} 	
					}
				  }else{
					    if(mv){
							formset.html(mv)
						}else{
							formset.html('未填')
						} 	
				  }
				  break;  
				case 'INPUTcheckbox': //還沒有
				default:break; 
			  }
			  
		    });
		  });	
		  
		  $('.history_record').html(recordset['questionnaire_information']['record_id']+'-'+recordset['questionnaire_information']['record_year']+' 年度由 '+recordset['questionnaire_information']['user_name']+' 填寫')
		  
		  
		  return true;
		})
		
		
		
		
		
		// 填入DOM資料
		$('#record_id').attr('no',record_id).html(record_id)
		
		// 顯示通知 
		system_message_alert('alert',"讀取評量資料："+record_id); 
		
		
		
	  }).fail(function(xhr, ajaxOptions, thrownError) {
        console.log( ajaxOptions+" / "+thrownError);
	  }).always(function(r){
        system_loading();
      });
	  
	};
	
	 
    //帶資料之網址
	if(document.location.href.match(/Evaluation\/mett\/(METT\d{5})/)){
	  var record = document.location.href.match(/Evaluation\/mett\/(METT\d{5})#?/)
      load_record_to_page(record[1],'');
	  
	}
	 
	
	
	
  });	
  
  
  