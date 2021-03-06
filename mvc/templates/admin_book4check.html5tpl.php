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
	<link rel="stylesheet" type="text/css" href="theme/css/css_book_checker.css" />
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_book_checker.js"></script>
	
	<!-- PHP -->
	<?php
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	
	// 可接觸區域
	$data_area  	= isset($this->vars['server']['data']['areas'] ) 	? $this->vars['server']['data']['areas'] : array();
	 
	// 目前資料
	$data_list  	= isset($this->vars['server']['data']['records']['list']) 	? $this->vars['server']['data']['records']['list'] : array();
	$data_count 	= count($data_list);
	
	
	$data_list  	= isset($this->vars['server']['data']['records']['list']) 	? $this->vars['server']['data']['records']['list'] : array();
	
	
	$data_type  	= isset($this->vars['server']['data']['records']['type']) 	? $this->vars['server']['data']['records']['type'] : array('NP','NR','AP');
	$data_limit  	= isset($this->vars['server']['data']['records']['limit']) 	? $this->vars['server']['data']['records']['limit'] : '';
	$data_filter 	= isset($this->vars['server']['data']['records']['filter']) 	? $this->vars['server']['data']['records']['filter'] : array();
	
	$page_info 		= isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';  
	$user_roles     = array();
	
	
	?>
  </head>
  
  <body>
	<div class='system_main_area'>
	  
	  <div class='system_content_area'>
        <div class='tool_banner' >
		  <h1>
		    <img src='theme/image/mark_forest.png' />
			<div class='system_title'>
			  林務局保護留區申請進入查詢系統
			</div>
		  </h1>
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
		  <div class='record_filter'>
		    <ul class='filter_set' >
			  <li class='filter_group'>
			    <span>
			      <label>申請區域：</label>
				  <select class='apply_area' id='filter_apply_area' >
				    <option value=''>全部區域</option>
				    <?php foreach($data_area as $area): ?>
				    <option value='<?php echo $area['ano']?>' <?php echo isset($data_filter['apply_area']) && $data_filter['apply_area']==$area['ano'] ? 'selected':''; ?> > 
				      <?php echo $area['area_name']; ?> 
				    </option>
				    <?php endforeach; ?>
				  </select>
				</span>
				<span> 
				  <label>申請單號：</label>
				  <input type='text' id='filter_apply_code'  value='<?php echo isset($data_filter['apply_code']) ? $data_filter['apply_code']:''; ?>' placeholder='申請單號' /> 
			    </span>
			  </li>
			  <li  class='filter_group'> 
				<span>
				  <label>進入日期：</label>
				  <span class='input_date' ><input type='text' id='search_date_start' placeholder='日期-起' size='10' value='<?php echo isset($data_filter['range_start']) ? $data_filter['range_start'] : '';  ?>' /><i class="fa fa-calendar" aria-hidden="true"></i></span>
				  <span class='input_date' ><input type='text' id='search_date_end'   placeholder='日期-迄' size='10' value='<?php echo isset($data_filter['range_end']) ? $data_filter['range_end'] : '';  ?>' /><i class="fa fa-calendar" aria-hidden="true"></i></span> 
			    </span>
				<span>
				  <label>申請資料：</label>
				  <input type='text' id='filter_search_terms'  value='<?php echo isset($data_filter['apply_search']) ? $data_filter['apply_search']:''; ?>' placeholder='搜尋申請資料' />
			    </span>
			  </li>
			</ul>
			<span class='filter_option' >  
			  <button id='filter_submit'  type='button' class='active'><i class="fa fa-search" aria-hidden="true"></i> 篩選 </button> 
			  <a id='reset_filter' class='option' > <i class="fa fa-refresh" aria-hidden="true"></i> 清空條件</a>
			</span>
		  </div> 
		</div>
		
		<div class='main_content' >
		  <!-- 資料列表區 -->
		  <div class='data_record_block' id='record_selecter' >
		    <div class='record_header'>
			  <span class='record_name'>申請清單 / 共 <span> <?php echo $data_count; ?></span>  筆</span>
			  <span class='record_option'>
                
			  </span>
			</div> 
			<div class='record_body'>
			  <table class='record_list'>
		        <tr class='data_field'>
			      <td title='編號'		>no.</td>
				  <td title='申請單號'	>申請單號</td>
				  <td title='申請區域'	>申請區域</td>
				  <td title='進入期間'	>進入期間</td>
				  <td title='申請人'	>申請人</td>
				  <td style='text-align:center;' title='人數'		>人數</td>
				  <td style='text-align:center;' >目前狀態</td>
			    </tr>
			    <tbody class='data_result' mode='list' >   <!-- list / search--> 
			    <?php foreach($data_list as $i=> $data): ?>  
			      <tr class='data_record _data_read' no='<?php echo $data['r_apply_code'];?>' page='' >
                    <td field='no'  ><?php echo $i+1; ?> </td>
			        <td field='' 	><?php echo $data['r_apply_code'];   ?></td>
				    <td field=''	><?php echo $data['r_apply_area'];   ?></td>
					<td field=''	><?php echo $data['r_apply_period']; ?></td>
					<td field=''	><?php echo $data['r_apply_user'];   ?></td>
				    <td field=''	>共<?php echo $data['r_countmbr'];   ?>人</td>
				    <td ><?php echo $data['r_status']; ?> </td>
				  </tr> 
			    <?php endforeach; ?>
			      <tr class='data_field'>
			        <td title='編號'		>no.</td>
				    <td title='申請單號'	>申請單號</td>
				    <td title='申請區域'	>申請區域</td>
				    <td title='進入期間'	>進入期間</td>
				    <td title='申請人'	>申請人</td>
				     <td style='text-align:center;' title='人數'		>人數</td>
				    <td title='狀態'	style='text-align:center;'>目前狀態</td>
			      </tr> 
				</tbody>
				<tbody class='data_target'></tbody>
			  </table>
			  
		    </div>
		  </div>
		</div>
		
		
		<!-- 申請資料編輯  --> 
		<div class='data_record_block' id='record_editor' formtype='' >
		  <div class='editor_container'>  
			
			<div class='record_header' id='edit_form_header' >
			  <span class='apply_info'>
			    <span class='record_name'>申請資料：</span>
			    <span  class='_variable' id='apply_code'     >code</span>
				, 申請日期：<span class='_variable' id='apply_date' ></span>
			    , 陳核長官：
				<span id='apply_review' review=0 >
				  <i class="fa fa-check-square-o" aria-hidden="true"></i>
                  <i class="fa fa-square-o" aria-hidden="true"></i>
				</span>
			  </span>
			  <span>
			    <?php if(isset($user_info['user']['user_roles']['R00']) || (isset($user_info['user']['user_roles']['R03']) && $user_info['user']['user_roles']['R03'] > 1 )) :?>
				<?php endif; ?>
				<i class='sysbtn data_trival' id='act_apply_prev' title='上一筆'  > 上一筆 </i>
			    <i class='sysbtn data_trival' id='act_apply_next' title='下一筆'  > 下一筆 </i>
			  </span>
			  <span class='record_option'>
                <a class='option' id='editor_reform'  title='關閉申請資料'>  <i class="fa fa-times" aria-hidden="true"></i>  </a>
			  </span>
			</div> 
		    
			<div class='record_body' id='record_form_block'>  
			  
			  <h1> 申請與審核設定 </h1>
			  <div class='form_block ' id='apply_setting' > 
				
				<table id='apply_process_table'>
				  <tr class='process_header' >
				    <td >申請審核</td>
					<td >初始階段</td>
					<td >抽籤階段</td>
					<td >審查階段</td>
					<td >等待階段</td>
					<td >最後階段</td>
				  </tr>
				  
				  <tr class='process_task' id='client' > 
				    <td class='role' >申請人: <span class='_variable' id='applicant_name' ></span></td>
				    <td class='stage1 _variable ' > </td>
				    <td class='stage2 _variable ' > </td>
				    <td class='stage3 _variable ' > </td>
				    <td class='stage4 _variable ' > </td>
					<td class='stage5 _variable ' > </td>
				  </tr>
				  <?php if(isset($user_info['permission']['interface_mask']['admin_book.html5tpl.php']['review']) && intval($user_info['permission']['interface_mask']['admin_book.html5tpl.php']['review'])): ?> 
				  <tr class='process_task' id='review' > 
				    <td class='role' >管理人:<span class='_variable' id='apply_checker' ></span></td>
				    <td class='stage1' ></td>
				    <td class='stage2 _variable' > </td>
				    <td class='stage3' ></td>
				    <td class='stage4 _variable' > </td>
					<td class='stage5' >
					  <button class='act_submit_review' mode='apply_status' value='申請註銷' >申請註銷</button>
					</td>
				  </tr>
				  <?php endif; ?>
				  <tr class='process_task' id='boss' > 
				    <td class='role' ></td>
				    <td class='step1' colspan=2></td>
				    <td class='step3' >
					</td>
				    <td class='step4' colspan=2></td>
				  </tr>
				</table>
				
			  </div>
			  <div class='form_block' id='comment' >
			    <div class='data_col '> 
				  <label class='data_field '> 審核註記 </label>
				  <div class='data_value'> 
				    <textarea type='text' class='_variable _update' id='check_note' ></textarea> 
				  </div> 
				</div>
			  </div>
			    
			  <h1> 申請資料 </h1>  
			  <div class='form_block' id='apply_license' >
			  </div>
			  
			  <h1> 申請附件 </h1>  
			  <ul class='form_block apply_attachment' >
			    <li class='attach_field' >
				  <span class='upltime' >上傳時間</span>
				  <span class='uplname' >檔案名稱</span>
				  <a class='upllink' >下載檔案</a>
				</li>
			  </ul>
			  <ul class='form_block apply_attachment _variable' id='apply_attachment_record' ></ul>
			  
			  <h1> 申請人資料與紀錄 </h1>  
			  <div class='form_block' id='applicant_form' >
			    <div class='applicant_meta' >
				  <div class='data_col '> <label class='data_field _necessary'> 申請姓名 </label><div class='data_value'> <input type='text' class='_variable _update' id='applicant_name' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field _necessary'> 身分證號 </label><div class='data_value'> <input type='text' class='_variable _update' id='applicant_userid' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field _necessary'> 申請email </label><div class='data_value'> <input type='text' class='_variable _update' id='applicant_mail' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field '> 出生日期 </label><div class='data_value'> <input type='text' class='_variable _update' id='applicant_birthday' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field '> 服務單位 </label><div class='data_value'> <input type='text' class='_variable _update' id='applicant_serviceto' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field '> 聯繫市話 </label><div class='data_value'> <input type='text' class='_variable _update' id='applicant_phonenumber' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field '> 聯繫手機 </label><div class='data_value'> <input type='text' class='_variable _update' id='applicant_cellphone' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field '> 傳真號碼 </label><div class='data_value'> <input type='text' class='_variable _update' id='applicant_faxnumber' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field '> 通訊地址 </label><div class='data_value'> <input type='text' class='_variable _update' id='applicant_mailaddress' default='readonly' /> </div> </div>
				  <div class='data_col '> <label class='data_field '> 戶籍地址 </label><div class='data_value'> <input type='text' class='_variable _update' id='applicant_regaddress' default='readonly' /> </div> </div>		  
				</div>
				<div class='applicant_record'>
				  <h2> 領隊申請紀錄 </h2>
				  <ul class='applied_list _variable' id='apply_history'></ul>
				  </ul>
				</div>
			  </div>
			</div>
			
		  </div>  <!-- end editor_container -->
		
		</div> <!-- end of data_record_block -->
		
		
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