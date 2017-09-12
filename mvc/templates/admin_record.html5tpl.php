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
	<link rel="stylesheet" type="text/css" href="theme/css/css_record_admin.css" />
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_record_admin.js"></script>
	
	
	<!-- PHP -->
	<?php
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	
	$data_filter  	= isset($this->vars['server']['data']['statistics']['filter']) 	? $this->vars['server']['data']['statistics']['filter'] : array('date_start'=>date('Y-01-01'),'date_end'=>date('Y-12-31'));  
	$data_select  	= isset($this->vars['server']['data']['statistics']['select']) 	? $this->vars['server']['data']['statistics']['select'] : array();  
	
	$data_list  	= isset($this->vars['server']['data']['statistics']['record']) 	? $this->vars['server']['data']['statistics']['record'] : array();  
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
		    <div class='topic_title'> 資料紀錄 </div>
			<div class='topic_descrip'> 管理者轄下區域每月申請統計資料 </div>
		  </div>
		  
		</div>
		
		<div class='main_content' >
		  <!-- 資料列表區 -->
		  <div class='data_record_block' id='record_selecter' >
		    <div class='record_header'>
			  <span class='record_name'>紀錄清單</span>
			  <span >
			    <select id='area_record_selecter' >
				  <option value='_all'>顯示全部</option> 
				  <?php foreach($data_select as $area_code => $area_name):?>
				  <option value='<?php echo $area_code; ?>'><?php echo $area_name; ?></option> 
				  <?php endforeach; ?>
			    </select>
			    <input type='checkbox' id='area_record_detail' checked=true /> 顯示各月統計細目  
			  </span>
			  <span class='record_option'>
			    <i class='sysbtn' id='act_record_export' title='下載統計紀錄'><a class='btn_mark pic_excel_file_s'  ></a></i>
			  </span>
			</div> 
			<div class='record_body'>
		      <div class='record_control'>
			    <span class='record_limit'>  
			      顯示轄下 <?php echo count($data_select); ?> 區，<?php echo $data_filter['date_start'];?> ~ <?php echo $data_filter['date_end'];?> 統計紀錄
			    </span>
			    <span class='record_search'>
			      紀錄起訖：
				  <input type='text' class='record_filter' id='date_start' value='<?php echo $data_filter['date_start'];?>'> - 
				  <input type='text' class='record_filter' id='date_end'   value='<?php echo $data_filter['date_end'];?>'>
				  <span class='sysbtn' id='search_by_date' >查詢</span>
			    </span>
			  </div>
			  
			  
			  <?php foreach($data_list as $area_type => $area_list): ?>  
			  
			  <h1 class='area_type'><?php echo $area_type; ?></h1>
			  <table class='record_list'>
		        <tr class='data_field'>
			      <td title='時間'		>區域名稱</td>
				  <td title='類型'		>申請件數</td>
				  <td title='資料表'	>核准件數</td>
			      <td title='目標'		>未完成件數</td>
				  <td title='目標'		>核准人數</td>
				  <td title='紀錄'	    >案件核准比例<br/>申請案件/核准案件</td>
				  <td title='結果'	    >人次進入比例<br/>申請人次/核准人次</td>
			      <td title='結果'	    >平均每日進入人次</td>
				</tr>
				
				<?php foreach($area_list as $area_id => $area_data): ?>  
				<tbody class='data_result' mode='list' area='<?php echo $area_data['area']['area_code'];?>' >   <!-- list / search--> 
			      <tr class='data_record data_summary'>
			        <td ><?php echo $area_data['area']['area_name'];?></td>
				    <td ><?php echo $area_data['total']['apply_case'];?></td>
					<td ><?php echo $area_data['total']['accept_case'];?></td>
					<td ><?php echo $area_data['total']['undone_case'];?></td>
					<td ><?php echo $area_data['total']['accept_member'];?></td>
					<td ><?php echo intval($area_data['total']['apply_case']) ? round(intval($area_data['total']['accept_case'])/intval($area_data['total']['apply_case']),4)*100  : 0 ; ?>%</td>
					<td ><?php echo intval($area_data['total']['apply_case']) ? round(intval($area_data['total']['accept_member'])/intval($area_data['total']['apply_member']),4)*100  : 0 ; ?>%</td>
					<td ><?php echo count($area_data['total']['apply_dates']) ?  round(intval($area_data['total']['accept_member'])/count($area_data['total']['apply_dates'])) : 0 ; ?> 人/日</td>
			      </tr> 
				  <?php foreach($area_data['table'] as $date_index => $data): ?>  
			      <tr class='data_record data_detail'  >
                    <td  ><?php echo $data['year'].' 年 '.$data['month'].' 月'; ?></td>
			        <td  ><?php echo $data['apply_case']; ?></td>
				    <td  ><?php echo $data['accept_case']; ?></td>
				    <td  ><?php echo $data['undone_case']; ?></td>
					<td  ><?php echo $data['accept_member']; ?></td>
					<td  ><?php echo intval($data['apply_case']) ?  round(intval($data['accept_case'])/intval($data['apply_case']),4)*100  : 0 ; ?>%</td>
				    <td  ><?php echo intval($data['apply_case']) ?  round(intval($data['accept_member'])/intval($data['apply_member']),4)*100  : 0 ; ?>%</td>
					<td  ><?php echo count($data['apply_dates']) ?  round(intval($data['accept_member'])/count($data['apply_dates'])) : 0 ; ?> 人/日</td>
				  </tr> 
			      <?php endforeach; ?>
				</tbody>
				<?php endforeach; ?>
				
			  </table>
			  <?php endforeach; ?>
			  <div class='record_control'></div>
		    </div>
		  </div>
		  
		</div>
	  </div>
	</div>
	
	
	<!-- 框架外結構  -->
	<div class='system_message_area'>
	  <div class='message_block'>
		<div id='message_container'>
		  <div class='msg_title'><?php echo $page_info; ?></div>
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