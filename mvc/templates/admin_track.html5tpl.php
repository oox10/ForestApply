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
	
	<!-- Self -->
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_main.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_track_admin.css" />
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_track_admin.js"></script>
	
	
	<!-- PHP -->
	<?php
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	$user_mode      = isset($this->vars['server']['mode']) ? $this->vars['server']['mode'] : '';  
	
	$data_list  	= isset($this->vars['server']['data']['tracks']) ? $this->vars['server']['data']['tracks'] : array();  
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
		    <div class='topic_title'> 系統回報管理 </div>
			<div class='topic_descrip'> 管理者處理回報，使用者查閱回報進度 </div>
		  </div>
		  <div class='lunch_option'> 
		    
		  </div>
		</div>
		
		<div class='main_content' >
		  <!-- 資料列表區 -->
		  <div class='data_record_block' id='record_selecter' >
		    <div class='record_header'>
			  <span class='record_name'>回報清單</span>
			  <span class='record_option'>
			    <a class='option view_switch' >  −  </a>
			  </span>
			</div> 
			<div class='record_body'>
		      <div class='record_control'>
			    <span class='record_limit'>  
			      顯示 : <select class='record_view' ><option value=1> 1 </option><option value=5> 5 </option><option value=10> 10 </option><option value='all' selected> All </option></select> 筆 / 頁
			    </span>
				
			    <span class='record_search'>
			      搜尋 : <input class='search_input' type=text >
			    </span>
			  </div>
			  <table class='record_list'>
		        <tr class='data_field'>
			      <td title='時間'	>時間</td>
				  <td title='類型'	>類型</td>
				  <td title='群組'	>群組</td>
				  <td title='帳號'	>帳號</td>
			      <td title='描述'	>描述</td>
				  <td title='狀態'	>狀態</td>
			    </tr>
			    <tbody class='data_result'>
			    <?php foreach($data_list as $data): ?>  
			      <tr class='data_record _data_read' no='<?php echo intval($data['fno']);?>' >
                    <td field='fb_time' 	><?php echo $data['fb_time']; ?></td>
					<td field='fb_type'    	><?php echo $data['fb_type']; ?></td>
			        <td field='fb_group' 	><?php echo $data['fb_group']; ?></td>
				    <td field='user_account'  ><?php echo $data['user_account']; ?></td>
				    <td >
					  <div field='fb_content'><?php echo $data['fb_content']; ?></div>
					  <div field='fb_treatment' ><?php echo $data['fb_treatment']; ?></div>
					</td>
				    <td ><i class='mark24 pic_report_status<?php echo $data['fb_status'];?>' title='<?php echo $data['fb_status_info'];?>'></i></td>
				  </tr> 
			    <?php endforeach; ?>
			      <tr class='data_field'>
			        <td title='時間'	>時間</td>
				    <td title='類型'	>類型</td>
				    <td title='群組'	>群組</td>
				    <td title='帳號'	>帳號</td>
			        <td title='描述'	>描述</td>
				    <td title='狀態'	>狀態</td>
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
			  <span class='record_name'>回報資料</span>
			  <span class='record_option'>
			    <?php if(in_array('admin',$user_mode)): ?> <i class='sysbtn' id='act_report_save'><a class='btn_mark pic_save'  ></a></i> <?php endif; ?>
				<a class='option view_switch' id='editor_switch' >  +  </a>
				<a class='option' id='editor_reform'  >  &times;  </a>
			  </span>
			</div> 
		    <div class='record_body tr_like' id='record_form_block'>  
			  <div class='form_block float_cell' id='meta_input'>
			      <div class='data_col '> <label class='data_field '> 時間 </label><div class='data_value'> <input type='text' class='_variable' id='fb_time' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field '> 帳號 </label><div class='data_value'> <input type='text' class='_variable' id='user_account' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field '> 網址 </label><div class='data_value'> <input type='text' class='_variable' id='fb_url' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field '> 類型 </label><div class='data_value'> <input type='text' class='_variable' id='fb_type' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field '> 截圖 </label><div class='data_value'> <span class='_variable' name='groups' id="fb_preview" ></span> </div> </div>
			  </div>
			  <div class='form_block float_cell' id='status_input'>
			    <div class='data_col '> <label class='data_field '> 回報內容 </label><div class='data_value'> <textarea class='_variable' id='fb_content' default='readonly' ></textarea> </div> </div>
				<div class='data_col '>
				  <label class='data_field '> 處理紀錄 </label>
				  <div class='data_value'>  
					<div class='data_value feedback_process_note'>  
					  <input    type='text' class='' id='user_massage' id='client_submit' placeholder="填寫處理紀錄" />
					  <button type='button' class='active' id='submit_fb_note'>送出</button>
				    </div>
					<div class='_variable' id='fb_note' default='readonly' ></div>
				  </div>
				</div>
				<div class='data_col '> <label class='data_field '> 處理結果 </label><div class='data_value'> <textarea class='_variable _update' id='fb_treatment' <?php if(!in_array('admin',$user_mode)): ?> default='readonly' <?php endif; ?> ></textarea> </div> </div>
				
				<?php if(in_array('admin',$user_mode)): ?>
				<div class='data_col  action_col'> 
				  <label class='data_field '> 回報功能 </label>
				  <div class='data_value'> 
				    <select class='form_function _reset' id='execute_function_selecter' >
					  <option value=''> - </option>
					  <optgroup label='[ 回報處理 ]'>
					    <option value='endfb' > - 結案 </option>
					    <option value='request' > - 提為交改版需求 </option>
					  </optgroup>
					</select> 
				    <i class='sysbtn btn_activate' id='act_func_execute'> 執行 </i>
				  </div> 
				</div>
			    <?php endif; ?>
				
			  </div>
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