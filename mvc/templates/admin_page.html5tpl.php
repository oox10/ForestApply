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
	<link rel="stylesheet" type="text/css" href="theme/css/css_page_admin.css" />
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_page_admin.js"></script>
	
	
	<!-- PHP -->
	<?php
	$group_list  	= isset($this->vars['server']['data']['config']['group']) 	? $this->vars['server']['data']['config']['group'] : array();  
	$data_list  	= isset($this->vars['server']['data']['records']) 	? $this->vars['server']['data']['records'] : array();  
	$data_count 	= count($data_list);
	
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
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
		    <div class='topic_title'> 網站內容管理 </div>
			<div class='topic_descrip'> 修改網站文字資訊 </div>
		  </div>
		</div>
		
		<div class='main_content' >
		  <!-- 資料列表區 -->
		  <div class='data_record_block' id='record_selecter' >
		    <div class='record_header'>
			  <span class='record_name'>文字區塊</span>
			  <span class='record_option'>
			    <a class='option view_switch' >  −  </a>
			  </span>
			</div> 
			<div class='record_body'>
		      <div class='record_control'>
			    <span class='record_limit'>  
			      顯示 : <select class='record_view' ><option value=1> 1 </option><option value=5> 5 </option><option value=10> 10 </option><option value='all' selected> All </option></select> 筆 / 頁
			    </span>
			  </div>
			  <table class='record_list'>
		        <tr class='data_field'>
			      <td title='編號'		>no.</td>
				  <td title='類型'		>類型</td>
				  <td title='適用群組'	>適用群組</td>
			      <td title='顯示位置'	>顯示位置</td>
				  <td title='標題'		>內容名稱</td>
				  <td title='更新日期'	>更新日期</td>
				  <td style='text-align:center;' ><i class='sysbtn btn_plus' id='act_page_new' title='新增'> + </i> </td>
			    </tr>
			    <tbody class='data_result' mode='list' >  <!-- list / search-->
			    <?php foreach($data_list as $num => $data): ?>  
			      <tr class='data_record _data_read ' no='<?php echo $data['spno'];?>' page='' filter='' status='' >
                    <td field='no'  	   ></td>
			        <td field='page_type'  ><?php echo $data['page_type']; ?></td>
				    <td field='page_owner'  ><?php echo $data['page_owner']; ?></td>
				    <td field='page_show'    ><?php echo $data['page_show']; ?></td>
				    <td field='page_title' ><?php echo $data['page_title']; ?></td>
				    <td field='_timeupdate'  ><?php echo $data['_timeupdate']; ?></td>
				    <td ><i class="fa fa-check" aria-hidden="true"></i></td>
				  </tr> 
			    <?php endforeach; ?>
			      <tr class='data_field'>
			        <td title='編號'		>no.</td>
				    <td title='類型'		>類型</td>
				    <td title='適用群組'	>適用群組</td>
			        <td title='顯示位置'	>顯示位置</td>
				    <td title='標題'		>內容名稱</td>
				    <td title='更新日期'	>更新日期</td>
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
			  <span class='record_name'>內容編輯</span>
			  <span class='record_option'>
                <i class='sysbtn' id='act_page_save'><a class='btn_mark pic_save'  ></a></i>
				<a class='option view_switch' id='editor_switch' >  +  </a>
				<a class='option' id='editor_reform'  >  &times;  </a>
			  </span>
			</div> 
		    <div class='record_body tr_like' id='record_form_block'>  
			   
			      <div class='data_col '> 
				    <label class='data_field _necessary'> 內容類型 </label>
				    <div class='data_value'> 
				      <select class='_variable _update' id='page_type'>
					    <option value='html'>頁面資訊</option>
					    <option value='mail'>信件內容</option>
					  </select>
				    </div> 
				  </div>
				  <div class='data_col '> 
				    <label class='data_field _necessary'> 內容適用範圍 </label>
				    <div class='data_value'> 
				      <select class='_variable _update' id='page_owner'>
						<option value='_ALL'> 網站 </option>
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
				    <label class='data_field _necessary'> 內容顯示位置 </label>
				    <div class='data_value'> 
					  <input type='text' class='datetime _variable _update' id='page_show' /> 
					</div>
				  </div>
			  
			    
				<div class='data_col '> 
				  <label class='data_field _necessary'> 內容名稱 </label>
				  <div class='data_value'> <input type='text' class='_variable _update' id='page_title' /></div>
				</div>
				<div class='data_col '> 
				  <label class='data_field _necessary'>文字內容</label>
				  <div class='data_value' style='display:block;'> <textarea  class='_variable _update' id='page_content'></textarea></div>
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