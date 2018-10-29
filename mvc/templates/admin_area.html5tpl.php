<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" >
	<title><?php echo defined('_SYSTEM_HTML_TITLE') ? _SYSTEM_HTML_TITLE : 'RCDH System'; ?></title>
	
	<!-- CSS -->
	<link type="text/css" href="tool/jquery-ui-1.11.2.custom/jquery-ui.structure.min.css" rel="stylesheet" />
	<link type="text/css" href="tool/jquery-ui-1.11.2.custom/jquery-ui.theme.min.css" rel="stylesheet" />
	<link type="text/css" href="tool/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" />
	
	<!-- JS -->
	<script type="text/javascript" src="tool/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="tool/jquery-ui-1.11.2.custom/jquery-ui.min.js"></script>
	<script type="text/javascript" src="tool/canvasloader-min.js"></script>	
    <script type="text/javascript" src="tool/html2canvas.js"></script>	  
	
	<!-- Tool -->
	<script type="text/javascript" src="tool/jonthornton-jquery-timepicker/jquery.timepicker.min.js"></script>
	<link rel="stylesheet" type="text/css" href="tool/jonthornton-jquery-timepicker/jquery.timepicker.css" />
	
	
	<!-- Self -->
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_main.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_area_admin.css" />
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_area_admin.js"></script>
	
	
	<!-- PHP -->
	<?php
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	$data_list  	= isset($this->vars['server']['data']['areas']) 	? $this->vars['server']['data']['areas'] : array();  
	$field_conf  	= isset($this->vars['server']['data']['config']['field']) 	? $this->vars['server']['data']['config']['field'] : array();  
	$group_list  	= isset($this->vars['server']['data']['config']['group']) 	? $this->vars['server']['data']['config']['group'] : array();  
	
	$data_count 	= count($data_list);
	
	$page_info 		= isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';  
	
	?>
  </head>
  
  
  <body>
	<div class='system_main_area'>
	  <div class='system_manual_area'>
	  <?php include('area_admin_manual.php'); ?>
	  </div>
	  <div class='system_content_area'>
        <div class='tool_banner' >
		  <ol id='system_breadcrumbs' typeof="BreadcrumbList" >
		  </ol>
		  <span class='account_option tool_right'>
		    <div class='account_info'>
			  <span id='acc_mark'><i class='m_head'></i><i class='m_body'></i></span>
			  <span id='acc_string'> 
			    <i class='acc_name'><?php echo $user_info['user']['user_name']; ?></i>
			    <i class='acc_group'><?php echo $user_info['user']['user_group']; ?></i>
			  </span>
			  <span id='acc_option'><a class='mark16 pic_more'></a> </span>
			</div>
		    <div class='account_control arrow_box'>
			  <ul class='acc_option_list'>
			    <li >
				  <label title='目前群組'> <i class="fa fa-university" aria-hidden="true"></i> 群組 </label>
				  <select id='acc_group_select'>
				    <?php foreach($user_info['group'] as $gset): ?>  
				    <option value='<?php echo $gset['id']?>' <?php echo $gset['now']?'selected':'' ?> > <?php echo $gset['name']; ?></option>
				    <?php endforeach; ?>
				  </select>
				</li>
				<li> 
				  <label> <i class="fa fa-user-secret" aria-hidden="true"></i> 角色 </label>
				  <span>
				    <?php foreach($user_info['group'] as $gid => $gset): ?>  
				    <?php if($gset['now']) echo join(',',$gset['roles']); ?>
				    <?php endforeach; ?>
				  </span> 
				</li>
				<li>
				  <label> <i class="fa fa-clock-o" aria-hidden="true"></i> 登入</label>
				  <span> <?php echo $user_info['login']; ?></span>
				</li>
			  </ul>
			  <div class='acc_option_final'>
			    <span id='acc_logout'> 登出 </span>
			  </div>
		    </div>
		  </span>
		</div>
		
		<div class='topic_banner'>
		  <div class='topic_header'> 
		    <div class='topic_title'> 區域管理管理 </div>
			<div class='topic_descrip'> 編輯禁止時間、上傳區域影像、新增修改區域資料 </div>
		  </div>
		</div>
		
		<div class='main_content' >
		  <!-- 資料列表區 -->
		  <div class='data_record_block' id='record_selecter' >
		    <div class='record_header'>
			  <span class='record_name'>區域清單</span>
			  <span class='record_option'>
			    <a class='option view_switch' >  −  </a>
			  </span>
			</div> 
			<div class='record_body'>
		      <div class='record_control'>
			    <span class='record_limit'>  
			      顯示 : <select class='record_view' ><option value=1> 1 </option><option value=5> 5 </option><option value=10> 10 </option><option value='all' selected> All </option></select> 筆 / 頁
			    </span>
				、
				<span class='record_limit'>  
			      <?php if(isset($field_conf['area_type'])): ?>
				  篩選 : 
				  <?php foreach($field_conf['area_type']['default'] as $option ):?>
				  <input type='radio' name='area_type' value='<?php echo $option; ?>' > <?php echo $option; ?>
				  <?php endforeach; ?>
				  <?php endif; ?>
			    </span>
			    <span class='record_search'>
			      搜尋 : <input class='search_input' type=text >
			    </span>
			  </div>
			  <table class='record_list'>
		        <tr class='data_field'>
			      <td title='編號'	>no.</td>
				  <td title='類型'	>類型</td>
				  <td title='區域名稱'	>區域名稱</td>
			      <td title='申請上限'	>申請上限</td>
				  <td style='text-align:center;' ><i class='sysbtn btn_plus' id='act_area_new' title='新增區域'> + </i> </td>
			    </tr>
			    <tbody class='data_result' mode='list' >  <!-- list / search-->
			    <?php foreach($data_list as $num => $data): ?>  
			      <tr class='data_record _data_read ' no='<?php echo $data['area_code'];?>' page='' filter='<?php echo join(' ',$data['@list_filter']); ?>' status='<?php echo join(' ',$data['@list_status']); ?>' >
                    <td field='no'  	   ></td>
			        <td field='area_type'  ><?php echo $data['area_type']; ?></td>
				    <td field='area_name'  ><?php echo $data['area_name']; ?></td>
				    <td field='area_load'    ><?php echo $data['area_load']; ?></td>
				    <td ><i class='mark24 pic_area_display_<?php echo $data['_open'];?>'></i></td>
				  </tr> 
			    <?php endforeach; ?>
			      <tr class='data_field'>
			        <td title='編號'	>no.</td>
				    <td title='類型'	>類型</td>
				    <td title='區域名稱'	>區域名稱</td>
			        <td title='申請上限'	>申請上限</td>
					<td> 開放 </td>
			      </tr> 
				</tbody>
				<tbody class='data_target'></tbody>
			  </table>
			  <div class='record_control'>
			    <span class='record_result'>  
			      顯示 <span id='records_display_start'> 1 </span> - <span id='records_display_end'> 10 </span> /  共 <span id='records_display_count'> <?php echo $data_count; ?></span>  筆
			    </span>
				<span class='record_pages'>
				  <a class='page_tap page_to' page='prev' > &#171; </a>
				  <span class='page_select'></span>  
				  <a class='page_tap page_to' page='next' > &#187; </a>
				</span>
			  </div>
		    </div>
		  </div>
		  
		  <div class='data_record_block' id='record_editor'>
		    <div class='record_header'>
			  <span class='record_name'>區域資料編輯：</span>
			  <span class='record_option'>
                <i class='sysbtn' id='act_area_save'><a class='btn_mark pic_save'  ></a></i>
				<a class='option view_switch' id='editor_switch' >  +  </a>
				<a class='option' id='editor_reform'  >  &times;  </a>
			  </span>
			</div> 
		    <div class='record_body tr_like' id='record_form_block'>  
			  
			  
			  <!-- detail form -->
			  <h1 class='form_title'><a id='area_editor' >設定區域基本資料</a></h1>
			  <div class='form_block'>  
				<div class='data_col '> 
				  <label class='data_field _necessary'> 區域類型 </label>
				  <div class='data_value'> 
				    <select class='_variable _update' id='area_type'>
					  <?php if(isset($field_conf['area_type'])): ?>
					  <?php foreach($field_conf['area_type']['default'] as $option ):?>
					  <option value='<?php echo $option; ?>'><?php echo $option; ?></option>
					  <?php endforeach; ?>
					  <?php endif; ?>
					</select>
				  </div> 
				</div>
				
				<div class='data_col '> 
				  <label class='data_field _necessary'> 區域名稱 </label>
				  <div class='data_value'> <input type='text' class='_variable _update' id='area_name' /></div>
				</div>
				  
				<div class='data_col '> 
				  <label class='data_field'> 區域參數 </label>
				  <div class='data_value'>
					<table>
					  <tr>
						<th>人數上限</th>
						<th>最大申請日數</th>
						<th>最小申請日數</th>
						<th>補件天數</th>
						<th>最後註銷日</th>
						<th>統一遞補日</th>
						<th>候補隊伍數</th>
					  </tr>
					  <tr>
						<td> <input type='number'   class='_variable _update' id='area_load'      <?php echo isset($field_conf['area_load']) ? 'data-default='.$field_conf['area_load']['default'] :'';  ?>  value=0  /> 人</td>
						<td>前 <input type='number' class='_variable _update' id='accept_max_day' <?php echo isset($field_conf['accept_max_day']) ? 'data-default='.$field_conf['accept_max_day']['default'] :'';  ?>   value=0 /> 天</td>
						<td>前 <input type='number' class='_variable _update' id='accept_min_day' <?php echo isset($field_conf['accept_min_day']) ? 'data-default='.$field_conf['accept_min_day']['default'] :'';  ?>   value=0 /> 天</td>
						<td> <input type='number'   class='_variable _update' id='revise_day'     <?php echo isset($field_conf['revise_day']) ? 'data-default='.$field_conf['revise_day']['default'] :'';  ?>   value=0 /> 天</td>
						<td>前 <input type='number' class='_variable _update' id='cancel_day' <?php echo isset($field_conf['cancel_day']) ? 'data-default='.$field_conf['cancel_day']['default'] :'';  ?> value=0 /> 天</td>
						<td>前 <input type='number' class='_variable _update' id='filled_day' <?php echo isset($field_conf['filled_day']) ? 'data-default='.$field_conf['filled_day']['default'] :'';  ?> value=0 /> 天</td>
						<td><input type='number' class='_variable _update' id='wait_list' <?php echo isset($field_conf['wait_list']) ? 'data-default='.$field_conf['wait_list']['default'] :'';  ?>  value=0 /> 隊</td>
					  </tr>
					  <tr>
						<th>隊伍人數上限</th>
						<th>審核自動通過</th>
						<th></th>
						<th></th>
						<th>  </th>
						<th colspan=2>區域開關時間</th>
					  </tr>
					  <tr>
							<td><input type='number'   class='_variable _update' id='member_max'      <?php echo isset($field_conf['member_max']) ? 'data-default='.$field_conf['member_max']['default'] :'';  ?>  value=0  /> 人</td>
							<td>前 <input type='number'   class='_variable _update' id='auto_pass'      <?php echo isset($field_conf['auto_pass']) ? 'data-default='.$field_conf['auto_pass']['default'] :'';  ?>  value=0  /> 天</td>
						<td > </td>
					    <td > </td>
						<td >  </td>
						<td colspan=2> 
						  <input type='time' class='_variable _update' id='time_open' <?php echo isset($field_conf['time_open']) ? 'data-default='.$field_conf['time_open']['default'] :'';  ?>   value='' /> 
						  <input type='time' class='_variable _update' id='time_close' <?php echo isset($field_conf['time_close']) ? 'data-default='.$field_conf['time_close']['default'] :'';  ?>   value='' /> 
						</td>
					  </tr>
					</table>
	                <i>若設為空白或0，則使用系統預設值</i>
				  </div>
				</div>
				<div class='data_col'> 
				  <label class='data_field'> 關聯群組 </label>
				  <div class='data_value'>
				    <ol id='rela_groups_list' >
				      <li class='_default'> (預設) 管理群組  </li>
					  <li class='_default'> (預設) 林務局  </li>
					</ol>
				  </div>
				</div>
			  </div> 
			  
			  
			  <!-- subblock form -->
			  <h1 class='form_title'  ><a id='block_editor' >設定區域與子區域</a></h1>
			  <div class='form_block ' id='area_blocks'>
				
				<ul class='block_switch'>
				  <li class='blocksel selected' no='main' >全區設定</li>
				  <li class='' no='' id='act_new_block'><span class='option' title='新增' ><i class="fa fa-plus" aria-hidden="true"></i></span></li>
				</ul>
				
				<div class='area_block_config' id='main' view='1' >
				  <div class='data_col '> 
				    <label class='data_field '> 介紹描述 </label>
				    <div class='data_value'>
					  <textarea type='text' class='_variable _update' id='area_descrip' ></textarea>
					</div>
				  </div>
				  <div class='data_col '>
                    <label class='data_field '> 出入口設定 </label>
				    <div class='data_value'>
					  <input type='text' class='_variable _update' id='area_gates' placeholder='請使用分號 " ; " 隔開' />
					</div>
				  </div>
				  <div class='data_col '>
                    <label class='data_field '> 網站連結 </label>
				    <div class='data_value'>
					  <input type='text' class='_variable _update' id='area_link' placeholder='區域介紹網址' />
					</div>
				  </div>
				  <div class='data_col '> 
				    <label class='data_field '> 參考資料 </label>
				    <div class='data_value'>
					  <h2 class='area_refer_option'>
					    <span class='option' title='新增圖片' id='act_add_picture' ><i class="fa fa-picture-o" aria-hidden="true"></i></span>
					    <span class='option' title='新增地圖' id='act_add_mapedit' ><i class="fa fa-map" aria-hidden="true"></i></span>
					  </h2>
					  <ul class='area_refer_container'>
					    
					  </ul>
					  <iframe id="upload_target" name="upload_target" src="loader.php" ></iframe>
					</div>
					<!-- 
					<div class='data_value'>
					  <div class='map_editor'  id="gmap"  ></div>
					  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAhDxxjPOYq7gECT-QSggddQhOvx9ui2Yk&libraries=drawing" async defer></script> <?php //&callback=initMap ?>
					</div>
					-->
				  </div>  
				</div>
				
				<div class='area_block_config' id='area_block_template' view='0' >
				  <div class='data_col '> 
				    <label class='data_field '> 區域名稱 </label>
				    <div class='data_value'>
					  <input type='text' class='_variable' meta='block_name' />
					</div>
				  </div>
				  <div class='data_col '> 
				    <label class='data_field '> 區域描述 </label>
				    <div class='data_value'>
					  <textarea class='_variable' meta='block_descrip' ></textarea>
					</div>
				  </div>
				  <div class='data_col '> 
				    <label class='data_field '> 出入口設定 </label>
				    <div class='data_value'>
					  <input type='text' class='_variable' meta='block_gates' placeholder='請使用分號 " ; " 隔開' />
					</div>
				  </div>
				  <div class='data_col '> 
				    <label class='data_field'> 申請參數 </label>
				    <div class='data_value'>
					  <table>
						  <tr>
							<th>人數上限</th>
							<th>最大申請日數</th>
							<th>最小申請日數</th>
							<th>候補隊伍數</th>
						  </tr>
						  <tr>
							<td> <input type='number'   class='_variable' meta='area_load' value=0  /> 人</td>
							<td>前 <input type='number' class='_variable' meta='accept_max_day' value=0 /> 天</td>
							<td>前 <input type='number' class='_variable' meta='accept_min_day' value=0 /> 天</td>
							<td><input type='number' class='_variable' meta='wait_list' value=0 /> 隊</td>
						  </tr>
					  </table>
					</div>
				  </div>
				</div>
			  </div>
			  
			  <!-- stopdate form -->
			  <h1 class='form_title'><a id='stop_editor' >設定禁止申請日期</a></h1>
			  <div class='form_block'>  
				<div class='data_col' id='area_stop_function'> 
				    <label class='data_field'> 禁申日期 </label>
					<div class='data_value' > 
					  <ul id='stop_dates_list' >
						<li class='stop_day _default  ' no='' valid=1 ><span class='option' title='新增' id='act_new_stop_date'><i class="fa fa-plus" aria-hidden="true"></i></span></li>
						<li class='stop_day _default _template' no='' valid=1 >
						    <span class='option act_dele_stop_date' title='刪除'><i class="fa fa-trash-o" aria-hidden="true"></i></span>
							<input type='text' name='date_start' class='date_seleter' placeholder='0000-00-00' /> - 
							<input type='text' name='date_end'   class='date_seleter' placeholder='0000-00-00' />
							<span>理由</span>
							<input type='text' name='reason'     placeholder='輸入禁止申請理由' />
							<span>適用區域</span>
							<select class='effect_block' name='effect'>
							  <option value='main' >全區</option>
							</select>
							<div class='stop_option'>
							  <label class="switch" title='開關'>
							    <input type="checkbox" name='_active' checked>
							    <div class="slider round"></div>
							  </label>
							  <button class='option act_process_stop_date' title='停止並取消所有申請'><i class="fa fa-ban" aria-hidden="true"></i></button>
							  <button class='option act_save_stop_date' title='儲存修改'><i class="fa fa-save" aria-hidden="true"></i></button>
						    </div>
						</li>
				      </ul>
					</div>
				</div>
			  </div>
			  
			  <!-- bookin form -->
			  <h1 class='form_title'><a id='apply_editor' >設定申請填寫資料</a></h1>
			  <div class='form_block apply_form'>
			    
				<!-- 必備欄位  -->
				<h2  > 必填欄位 </h2>
				<div class='field_config' id='application_reason'>  
				  <div class='field_name'>
				    <i>1. </i>
					<label> 申請目的/項目 </label>
					<span>  模式：</span>
					<ul>
					  <li><input type='radio' name='input_type' value='radio'   checked /> 單選 </li>
					  <li><input type='radio' name='input_type' value='checkbox' /> 複選 </li>
					  <li>  <a class='option act_add_reason' title='新增選項'><i class="fa fa-plus" aria-hidden="true"></i> 新增選項</a> </li>
					</ul>
					<span class="option act_save_apply_form" id='form_config_main_save' title="儲存"><i class="fa fa-save" aria-hidden="true"></i></span>
				  </div>
				  <ol class='field_options' >
					  <li class='sel_term' id='sample_term'> 
						<a class='option act_del_reason'><i class="fa fa-trash-o" aria-hidden="true"></i></a>
					    <input type='text' class='field_term' name='option_name' value='' placeholder='新增申請目的' />
						<div class='term_conf'>
						  <span>設定：</span>
						  <span title='選項是否設定為預設值'><input type='checkbox'	 name='option_set'  value='default'  /> 預設 </span>
						  <span title='選項是否受申請人數限制'><input type='checkbox'	 name='option_set'  value='limit'    /> 名額，至少<input type='number' name='option_test' bind='limit'  value='' min=1 size='1' title='人數下限' readonly />人 </span>
						  <span title='選項是否需要附件'><input type='checkbox'		 name='option_set'  value='attach'   /> 附件，至少<input type='number' name='option_test' bind='attach' value='' min=1 size='1' title='附件數量' readonly />件 </span>
						  <span title='申請日期是否可跨日'><input type='checkbox'		 name='option_set'  value='crossday' /> 跨日 </span>
						  ，備註內容：<input type='text' class='term_note' name='option_note' value='' placeholder='..備註' />  
						</div>
					  </li>
				  </ol>
				  <i> NOTE:申請目之設定與名額、禁止日期、是否需要附件相關連 </i>
				</div>
				
				<!-- 其他欄位  -->
				<h2  > 自訂欄位 <a class='option act_add_form_field' title='新增選項'><i class="fa fa-plus" aria-hidden="true"></i></a></h2>
				<table class='field_config' id='sample_form_editor'>
				  <tr>
				    <td >
					  <a class='option act_del_form_field'><i class="fa fa-trash-o" aria-hidden="true"></i></a><span class='feno'>0</span> 
					</td>
					<td >
					  <div class='input_set'>
					    <ul class='input_mode'>
					      <li>填寫模式：</li>
						  <li><input type='radio' name='input_type_' value='text'     checked /> 單行輸入 </li>
					      <li><input type='radio' name='input_type_' value='textarea' /> 多行輸入 </li>
					      <li><input type='radio' name='input_type_' value='radio'    /> 單選 </li>
					      <li><input type='radio' name='input_type_' value='checkbox' /> 複選 </li>
					    </ul>
						<span class="option act_save_apply_form" title="儲存"><i class="fa fa-save" aria-hidden="true"></i></span>
					  </div>
					</td>
				  </tr>
				  <tr><td>隸屬項目：</td><td><input    class='input_form' name='field_class' type='text' value='' placeholder='' /></td></tr>
				  <tr><td>欄位標題：</td><td><input    class='input_form' name='field_label' type='text' value='' placeholder='' /></td></tr>
				  <tr><td>欄位內容：</td><td><input    class='input_form' name='field_value' type='text' value='' placeholder='預設欄位內容；若模式為選項則將選項設定於此並用分號隔開' /></td></tr>
				  <tr><td>欄位備註：</td><td><textarea class='input_note' name='field_notes' placeholder='項目備註'></textarea></td></tr>
				</table>
				
				
			  </div>
			  
			  
			  <div class='form_block'>
			    <div class='data_col  action_col'> 
				    <label class='data_field' > 其他功能 </label>
				    <div class='data_value'> 
				        <select class='form_function _reset' id='execute_function_selecter' >
					        <option value='' disabled selected> 可執行區域 - 1.開起 2.關閉 或 3.刪除等功能 </option>
					        <optgroup class='_normal' label='[ 區域開關功能 ]' >
					          <option value='show' > - 開啟區域 </option>
						      <option value='mask' > - 關閉區域 </option>
					        </optgroup>	
					        <optgroup class='_attention' label='[ 區域移除功能 ]' >	
						      <option value='dele'> - 刪除區域 </option>
					        </optgroup>
					    </select> 
				        <i class='sysbtn btn_activate' id='act_func_execute'> 執行 </i>
				    </div> 
				</div>
			  </div>
			  
			  
			  
			  
			  
			  
			  <!-- end form sets // form 群組結尾 -->
			  
			  
			</div>
		  </div>
		  
		  
		  
		  
		</div>
	  </div>
	</div>
	
	
	<!-- 框架外結構  -->
	<div class='system_message_area'>
	  <div class='message_block'>
		<div id='message_container'>
		  <div class='msg_title'></div>
		  <div class='msg_info'></div>
		</div>
		<div id='area_close'></div>
      </div>
	</div> 
	<!-- 系統report -->
      <div class='system_feedback_area'>
        <div class='feedback_block'>
        <div class='feedback_header tr_like' >
          <span class='fbh_title'> 系統回報 </span>
          <a class='fbh_option' id='act_feedback_close' title='關閉' ><i class='mark16 pic_close'></i></a>
        </div>
        <div class='feedback_body' >
          <div class='fb_imgload'> 建立預覽中..</div>
          <div class='fb_preview'></div>
          <div class='fb_areasel'>
            <span>回報頁面:</span>
            <input type='radio' class='feedback_area_sel' name='feedback_area' value='system_body_block'>全頁面
            <input type='radio' class='feedback_area_sel' name='feedback_area' value='system_content_area'>中版面
            <input type='radio' class='feedback_area_sel' name='feedback_area' value='system_edit_area'>右版面
            <input type='radio' class='feedback_upload_sel' name='feedback_area' value='user_upload'><input type='file'  id='feedback_img_upload' >
          </div>
          <div class='fb_descrip'>
            <div class=''>
              <span class='fbd_title'>回報類型:</span>
              <input type='checkbox' class='feedback_type' name='fbd_type' value='資料問題' ><span >資料問題</span>，
              <input type='checkbox' class='feedback_type' name='fbd_type' value='系統問題' ><span >系統問題</span>，
              <input type='checkbox' class='feedback_type' name='fbd_type' value='使用問題' ><span >使用問題</span>，
              <input type='checkbox' class='feedback_type' name='fbd_type' value='建議回饋' ><span >建議回饋</span>，
              <input type='checkbox' class='feedback_type' name='fbd_type' value='其他' >其他:<input type='text' class='fbd_type_other' name='fbd_type_other' value='' >
            </div>
            <div class='fbd_title'>回報描述:</div>
            <textarea  class='feedback_content'  name='fbd_content'></textarea>
          </div>
        </div>
        <div class='feedback_bottom tr_like' >
          <a class='sysbtn btn_feedback' id='act_feedback_cancel' > <i class='mark16 pic_account_off'></i> 取 消 </a>
          <a class='sysbtn btn_feedback' id='act_feedback_submit' > <i class='mark16 pic_account_on'></i> 送 出 </a>		
        </div>
        </div>
      </div>      
	<!-- 系統Loading -->
    <div class='system_loading_area'>
	  <div class='loading_block' >
	    <div class='loading_string'> 系統處理中 </div>
		<div class='loading_image' id='sysloader'></div>
	    <div class='loading_info'>
		  <span >如果系統過久無回應，請按[ Esc ] 關閉 loading 版面，並重新操作剛才的動作.</span>
	    </div>
	  </div>
	</div>
  
  </body>
</html>