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
	
	<!-- froala editer-->
	<link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
	<link href="tool/froala_editor/css/froala_editor.min.css" rel="stylesheet" type="text/css" />
    <link href="tool/froala_editor/css/froala_style.min.css" rel="stylesheet" type="text/css" />
	<!-- Include Editor Plugins style. -->
    <link rel="stylesheet" href="tool/froala_editor/css/plugins/char_counter.css">
    <link rel="stylesheet" href="tool/froala_editor/css/plugins/code_view.css">
    <link rel="stylesheet" href="tool/froala_editor/css/plugins/colors.css">
    <link rel="stylesheet" href="tool/froala_editor/css/plugins/file.css">
    <link rel="stylesheet" href="tool/froala_editor/css/plugins/fullscreen.css">
    <link rel="stylesheet" href="tool/froala_editor/css/plugins/image.css">
    <link rel="stylesheet" href="tool/froala_editor/css/plugins/image_manager.css">
    <link rel="stylesheet" href="tool/froala_editor/css/plugins/line_breaker.css">
    <link rel="stylesheet" href="tool/froala_editor/css/plugins/table.css">
	
	<script type="text/javascript" src="tool/froala_editor/js/froala_editor.min.js"></script>
	<script type="text/javascript" src="tool/froala_editor/js/froala_editor.pkgd.min.js"></script>
	<!-- Include Language file if we'll use it. -->
    <script type="text/javascript" src="tool/froala_editor/js/languages/zh_tw.js"></script>
	
	
	<!-- Self -->
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_main.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_post_admin.css" />
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_post_admin.js"></script>
	
	
	<!-- PHP -->
	<?php
	$field_conf  	= isset($this->vars['server']['data']['config']['field']) 	? $this->vars['server']['data']['config']['field'] : array();  
	$group_list  	= isset($this->vars['server']['data']['config']['group']) 	? $this->vars['server']['data']['config']['group'] : array();  
	
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	
	$data_list  	= isset($this->vars['server']['data']['posts']) 	? $this->vars['server']['data']['posts'] : array();  
	$data_count 	= count($data_list);
	
	$page_info 		= isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';  
	
	/*
	//＊ note : adm || forest 可以使用所有群組名義發布公告
	
	*/
	
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
				    <option value='<?php echo $gset['id']; ?>' <?php echo $gset['now']?'selected':'' ?> > <?php echo $gset['name']; ?></option>
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
		    <div class='topic_title'> 最新消息管理 </div>
			<div class='topic_descrip'> 新增、修改與發佈消息 </div>
		  </div>
		</div>
		
		<div class='main_content' >
		  <!-- 資料列表區 -->
		  <div class='data_record_block' id='record_selecter' >
		    <div class='record_header'>
			  <span class='record_name'>公告清單</span>
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
			      篩選 : 
				  <input type='radio' name='record_type' value='inuse' checked> 期限中
				  <input type='radio' name='record_type' value='over'  > 已過期
			    </span>
			    <span class='record_search'>
			      搜尋 : <input class='search_input' type=text >
			    </span>
			  </div>
			  <table class='record_list'>
		        <tr class='data_field'>
			      <td title='編號'	>no.</td>
				  <td title='類型'	>類型</td>
				  <td title='公告來源'	>公告來源</td>
			      <td title='公告位置'	>公告位置</td>
				  <td title='等級'	>等級</td>
				  <td title='標題'	>標題</td>
				  <td title='開始日期'	>開始日期</td>
				  <td title='結束日期'	>結束日期</td>
				  <td style='text-align:center;' ><i class='sysbtn btn_plus' id='act_post_new' title='新增公告'> + </i> </td>
			    </tr>
			    <tbody class='data_result' mode='list' >  <!-- list / search-->
			    <?php foreach($data_list as $num => $data): ?>  
			      <tr class='data_record _data_read <?php echo in_array('over',$data['@list_filter']) ? 'hide':''; ?>' no='<?php echo $data['pno'];?>' page='' filter='<?php echo join(' ',$data['@list_filter']); ?>' status='<?php echo join(' ',$data['@list_status']); ?>' >
                    <td field='no'  	   ></td>
			        <td field='post_type'  ><?php echo $data['post_type']; ?></td>
				    <td field='post_from'  ><?php echo $data['post_from']; ?></td>
				    <td field='post_to'    ><?php echo $data['post_to']; ?></td>
				    <td field='post_level' ><?php echo $data['post_level']; ?></td>
				    <td field='post_title'  ><?php echo $data['post_title']; ?></td>
				    <td field='post_time_start' ><?php echo substr($data['post_time_start'],0,10); ?></td>
				    <td field='post_time_end'   ><?php echo substr($data['post_time_end'],0,10); ?></td>
				    <td ><i class='mark24 pic_post_display_<?php echo $data['post_display'];?>'></i></td>
				  </tr> 
			    <?php endforeach; ?>
			      <tr class='data_field'>
			        <td title='編號'	>no.</td>
				    <td title='類型'	>類型</td>
				    <td title='來源'	>來源</td>
					<td title='公告位置'	>公告位置</td>
			        <td title='等級'	>等級</td>
				    <td title='標題'	>標題</td>
				    <td title='開始日期'	>開始日期</td>
				    <td title='結束日期'	>結束日期</td>
					<td></td>
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
			  <span class='record_name'>公告資料</span>
			  <span class='record_option'>
                <i class='sysbtn' id='act_post_save'><a class='btn_mark pic_save'  ></a></i>
				<a class='option view_switch' id='editor_switch' >  +  </a>
				<a class='option' id='editor_reform'  >  &times;  </a>
			  </span>
			</div> 
		    <div class='record_body tr_like' id='record_form_block'>  
			  <div class='form_block float_cell' id='meta_input'>
			      <div class='data_col '> 
				    <label class='data_field _necessary'> 公告位置 </label>
				    <div class='data_value'> 
				      <select class='_variable _update' id='post_to'>
					  <?php if(isset($field_conf['post_to'])): ?>
					  <?php foreach($field_conf['post_to']['default'] as $option ):?>
					  <option value='<?php echo $option; ?>'><?php echo $option; ?></option>
					  <?php endforeach; ?>
					  <?php endif; ?>
					  </select>
				    </div> 
				  </div>
				  <div class='data_col '> 
				    <label class='data_field _necessary'> 公告類型 </label>
				    <div class='data_value'> 
				      <select class='_variable _update' id='post_type'>
					  <?php if(isset($field_conf['post_type'])): ?>
					  <?php foreach($field_conf['post_type']['default'] as $option ):?>
					  <option value='<?php echo $option; ?>'><?php echo $option; ?></option>
					  <?php endforeach; ?>
					  <?php endif; ?>
					  </select>
				    </div> 
				  </div>
				  <div class='data_col '> 
				    <label class='data_field _necessary'> 公告來源 </label>
				    <div class='data_value'> 
				      <select class='_variable _update' id='post_from'>
						<option value='<?php echo $user_info['user']['user_name']; ?>'> 個人：<?php echo $user_info['user']['user_name']; ?> </option>
						<optgroup label="群組">
						<?php if($user_info['permission']['group_code']=='adm' || $user_info['permission']['group_code']=='forest' ): ?>
						<?php foreach($group_list as $group): ?>
						  <option value='<?php echo $group; ?>'> <?php echo $group; ?> </option>
						<?php endforeach; ?>
						<?php else: ?>
						  <option value='<?php echo $user_info['permission']['group_code']; ?>'> <?php echo $user_info['permission']['group_name']; ?> </option>
						<?php endif; ?>
						</optgroup> 
					  </select>
				    </div> 
				  </div>
				  
				  <div class='data_col '> 
				    <label class='data_field _necessary'> 公告等級 </label>
				    <div class='data_value'> 
				      <select class='_variable _update' id='post_level'>
						<option value='1'> LV.1 ( 一般顯示，無特別標示 )</option>
						<option value='2'> LV.2 ( 星號標示 )</option>
					    <option value='3'> LV.3 ( LV2 + 置頂</option>
					    <option value='4'> LV.4 ( LV3 + 永遠顯示) </option>
					  </select>
				    </div> 
				  </div>
				  
				  <div class='data_col '> 
				    <label class='data_field _necessary'> 公告期限 </label>
				    <div class='data_value mutile_fields'> 
					  <input type='text' class='datetime _variable _update' id='post_time_start' /> - <input type='text' class='datetime _variable _update' id='post_time_end' />
					</div>
				  </div>
			  </div>
			  
			  <div class='form_block float_cell' id='status_input'>
			    
				<div class='data_col '> 
				  <label class='data_field _necessary'> 公告標題 </label>
				  <div class='data_value'> <input type='text' class='_variable _update' id='post_title' /></div>
				</div>
				<div class='data_col '> 
				  <label class='data_field _necessary'>公告內容</label>
				  <div class='data_value' style='display:block;'> <textarea  class='_variable _update' id='post_content'></textarea></div>
				</div>
				<div class='data_col  action_col'> 
				  <label class='data_field'> 其他功能 </label>
				  <div class='data_value'> 
				    <select class='form_function _reset' id='execute_function_selecter' >
					  <option value='' disabled selected> 可執行公告 - 1.開起 2.關閉 或 3.刪除等功能 </option>
					  <optgroup class='_normal' label='[ 公告開關功能 ]' >
					    <option value='show' > - 開啟公告 </option>
						<option value='mask' > - 關閉公告 </option>
					  </optgroup>	
					  <optgroup class='_attention' label='[ 公告移除功能 ]' >	
						<option value='dele'> - 刪除公告 </option>
					  </optgroup>
					</select> 
				    <i class='sysbtn btn_activate' id='act_func_execute'> 執行 </i>
				  </div> 
				</div>
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