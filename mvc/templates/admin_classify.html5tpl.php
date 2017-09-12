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
	<link rel="stylesheet" type="text/css" href="theme/css/css_classify_admin.css" />
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_classify_admin.js"></script>
	
	<!-- PHP -->
	<?php
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	
	$user_group 	= isset($this->vars['server']['data']['group']) ? $this->vars['server']['data']['group'] : array();
	$page_info 		= isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';
	
	
	$user_tags		= isset($this->vars['server']['data']['classify']['tags']) ? $this->vars['server']['data']['classify']['tags'] : array();  
	$user_folder	= isset($this->vars['server']['data']['classify']['folder']) ? $this->vars['server']['data']['classify']['folder'] : array();  
	$system_level	= isset($this->vars['server']['data']['classify']['level']) ? $this->vars['server']['data']['classify']['level'] : array();  
	
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
			<li class='breadcrumb' > <a href='/'>首頁</a> </li>
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
						<?php if($gset['now']) echo join(',',$gset['roles']); $user_roles=$gset['roles'];?>
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
		    <div class='topic_title'> 分類設定 </div>
			<div class='topic_descrip'> 使用者標籤、資料夾與專題分類設定</div>
		  </div>
		</div>
		
		<div class='main_content' >
		  <!-- 資料列表區 -->
		  <div class='data_record_block' id='user_tags_block'  >
		    <div class='record_header'>
			  <span class='record_name'>使用者標籤</span>
			  <span class='record_option'>
			    - <?php echo count($user_tags);?> 項
			  </span>
			</div> 
			<div class='record_body'>
		      <table class='term_table'>
			    <tr class='data_field'><td>刪除</td><td>名稱</td><td>修改</td></tr>
			    <?php foreach($user_tags as $uterm): ?>
			    <tr class='data_list' no='T_<?php echo $uterm['utno']?>' title='建立時間<?php echo $uterm['edit_time']?>'>
				  <td><i class='mark16 pic_term_delete option act_term_dele'></i></td>
				  <td><div class='term_edit' data-term='<?php echo $uterm['tag_term'];?>' ><?php echo $uterm['tag_term'];?></div></td>
				  <td><i class='mark16 pic_term_edit option act_term_edit'></i><i class='mark16 pic_term_save option act_term_save'></i></td>
				</tr>
			    <?php endforeach; ?> 
			  </table>
		    </div>
		  </div>
		  
		  <div class='data_record_block' id='user_folder_block'  >
		    <div class='record_header'>
			  <span class='record_name'>使用者資料夾</span>
			  <span class='record_option'>
			     - <?php echo count($user_folder);?> 項
			  </span>
			</div> 
			<div class='record_body'>
		      <table class='term_table' id='folder_table'>
			    <tr class='data_field'><td>刪除</td><td>名稱</td><td>檔案</td><td>修改</td></tr>
			    <?php foreach($user_folder as $uterm): ?>
			    
				<tr class='data_list user_folder' no='F_<?php echo $uterm['ufno']?>' title='建立時間<?php echo $uterm['createtime']?>'>
				  <td><i class='mark16 pic_term_delete option act_term_dele'></i></td>
				  <td><div class='term_edit' data-term='<?php echo $uterm['name'];?>'><?php echo $uterm['name']?></div></td>
				  <td><?php echo $uterm['files']?></td>
				  <td><i class='mark16 pic_term_edit option act_term_edit'></i><i class='mark16 pic_term_save option act_term_save'></i></td>
				</tr>
				
			    <?php endforeach; ?> 
			  </table>
		    </div>
		  </div>
		  <div class='data_record_block' id='system_level_block'  >
		    <div class='record_header'>
			  <span class='record_name'>專題分類</span>
			</div> 
			<div class='record_body'>
		      <table class='term_table' id='level_table'>
			    <tr class='data_field'><td>刪除</td><td>序號</td><td>專題名稱</td><td>新增</td></tr>
			    <?php foreach($system_level as $sterm): ?>
			    <tr class='data_list level_bind' no='L_<?php echo intval($sterm['lvno']);?>' data-site='<?php echo $sterm['site'];?>' >
				  <td>
					<?php if(isset($_SESSION[_SYSTEM_NAME_SHORT]['PERMISSION']['classify-Delete_Level']) && intval($_SESSION[_SYSTEM_NAME_SHORT]['PERMISSION']['classify-Delete_Level'])!=0 ): ?>
					<i class='mark16 pic_term_delete option act_level_dele'></i>
				    <?php endif; ?>
				  </td>
				  <td><?php echo $sterm['lvcode'];?></td>
				  <td><div class='term_edit' data-term='<?php echo $sterm['name'];?>' style='margin-left:<?php echo ($sterm['site']-1)*20;?>px;'><?php echo $sterm['name'];?></div></td>
				  <td><i class='mark24 pic_meta_field_add option act_level_add'></i><i class='mark16 pic_term_save option act_level_save'></i></td>
				</tr>
			    <?php endforeach; ?> 
			  </table>
		    </div>
		  </div>
		  
		</div> <!-- end of main content-->
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