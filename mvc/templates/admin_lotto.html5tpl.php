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
	<link rel="stylesheet" type="text/css" href="theme/css/css_lotto_admin.css" />
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_lotto_admin.js"></script>
	
	
	<!-- PHP -->
	<?php
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	
	$area_list      = isset($this->vars['server']['data']['areas'])     ? $this->vars['server']['data']['areas']    : array();  
	
	$data_list  	= isset($this->vars['server']['data']['records']['list']) 	? $this->vars['server']['data']['records']['list'] : array();  
	$data_count 	= count($data_list);
	
	$data_filter  	= isset($this->vars['server']['data']['records']['filter']) 	? $this->vars['server']['data']['records']['filter'] : array('area'=>'_all','date'=>'');  
	
	
	$page_info 		= isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';  
	$user_roles     = array();
	$ui_config      = isset($user_info['permission']['interface_mask']) ? $user_info['permission']['interface_mask'] : array();
	
	$ui_area_selecter_optgroup_flag = '';  // 介面 selecter optgroup
	
	
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
		    <div class='topic_title'> 申請抽籤 </div>
			<div class='topic_descrip'> 各區域申請單抽籤狀況 </div>
		  </div>
		  <div class='lunch_option'> 
		    <?php if(isset($ui_config['admin_lotto.html5tpl.php']['act_lotto_active']) && intval($ui_config['admin_lotto.html5tpl.php']['act_lotto_active'])): ?> 
			<button type="button" class='active' id='act_built_lotto'>重整本日抽籤清單</button>
			<?php endif ?>
		  </div> 
		</div>
		
		<div class='main_content' >
		  <!-- 資料列表區 -->
		  <div class='data_record_block' id='record_selecter' >
		    <div class='record_header'>
			  <span class='record_name'>抽籤列表</span>
			  <span class='record_option'>
			    <a class='option view_switch' >  −  </a>
			  </span>
			</div> 
			<div class='record_body'>
		      <div class='record_control'>
			    <span class='record_limit'>  
			      顯示 : <select class='record_view' ><option value=1> 1 </option><option value=5> 5 </option><option value=10 selected> 10 </option><option value='all' > ALL </option></select> 筆
			    </span>
			    <span class='record_search'>
			      搜尋 : 
				  <select id='area_lotto_selecter' >
				    <option value='_all' >所有轄下區域</option>
					<?php foreach($area_list as $area): ?>  
					<?php 
					  if($ui_area_selecter_optgroup_flag != $area['area_type']){
						$ui_area_selecter_optgroup_flag = $area['area_type'];
						echo '<optgroup label="'.$area['area_type'].'">  ';	
					  }
					?>
					<option value="<?php echo $area['area_code'];?>"  <?php echo isset($data_filter['area'])&&$data_filter['area']==$area['area_code'] ? 'selected':''; ?>  > <?php echo $area['area_name']; ?> </option>
					<?php endforeach; ?>  	
				  </select>
				  <span class='input_date' ><input type='text' id='search_date_start' placeholder='抽籤日期' size='10' value='<?php echo isset($data_filter['date']) ? $data_filter['date']:''; ?>' /><i class="fa fa-calendar" aria-hidden="true"></i></span>
				  
				  
			    </span>
			  </div>
			  <table class='record_list'>
		        <tr class='data_field'>
			      <td title='編號'		>no.</td>
				  <td title='進入日期'	>進入日期</td>
				  <td title='抽籤日期'	>抽籤日期</td>
				  <td title='申請區域'	>申請區域</td>
			      <td title='每日容量'	>每日容量</td>
				  <td title='預約數量'	>預約數量</td>
				  <td title='抽籤時間'	>抽籤時間</td>
				  <td title='正取人數'	>正取人數</td>
				  <td title='狀態' style='text-align:center;'      >狀態</td>
			    </tr>
			    <tbody class='data_result' mode='list' >   <!-- list / search--> 
			    <?php foreach($data_list as $i=> $data): ?>  
			      <tr class='data_record _data_read' no='<?php echo intval($data['blno']);?>' page='' >
                    <td field='no'  	   	><?php echo $i+1; ?></td>
					<td field='date_tolot' 	><?php echo $data['date_enter']; ?></td>
			        <td field='date_tolot' 	><?php echo $data['date_tolot']; ?></td>
				    <td field='area_name'	><?php echo $data['area_name']; ?></td>
				    <td field='area_load'	><?php echo $data['area_load']; ?></td>
					<td field='applied'	    ><?php echo $data['applied']; ?></td>
					<td field='time_lotto'	><?php echo $data['time_lotto']; ?></td>
					<td field='lotto_num' 	><?php echo $data['lotto_num']; ?></td>
					<td field='@loted' islot=<?php echo $data['_loted'];?> ><i class="fa fa-clock-o" aria-hidden="true"></i><i class="fa fa-check-square-o" aria-hidden="true"></i></td>
				  </tr> 
			    <?php endforeach; ?>
			      <tr class='data_field'>
			        <td title='編號'		>no.</td>
					<td title='進入日期'	>進入日期</td>
				    <td title='抽籤日期'	>抽籤日期</td>
				    <td title='申請區域'	>申請區域</td>
			        <td title='每日容量'	>每日容量</td>
				    <td title='預約數量'	>預約數量</td>
					<td title='抽籤時間'	>抽籤時間</td>
				    <td title='正取人數'	>正取人數</td>
				    <td title='狀態' style='text-align:center;'      >狀態</td>
			      </tr> 
				</tbody>
				<tbody class='data_target'></tbody>
			  </table>
			  <div class='record_control'>
			    <span class='record_result'>  
			      顯示 <span> 1 </span> - <span> 10 </span> /  共 <span> <?php echo $data_count; ?></span>  筆
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
			  <span class='record_name'>抽籤紀錄</span>
			  
			  <span class='record_option'>
                <?php if(isset($ui_config['admin_lotto.html5tpl.php']['act_lotto_active']) && intval($ui_config['admin_lotto.html5tpl.php']['act_lotto_active'])): ?> 
				<i class='sysbtn' id='act_lotto_active'>抽籤</i>
				<?php endif; ?>
				<a class='option view_switch' id='editor_switch' >  +  </a>
				<a class='option' id='editor_reform'  >  &times;  </a>
			  </span>
			</div> 
		    <div class='record_body tr_like' id='record_form_block'>  
			  
			  <!-- 申請列表  -->
			  <div class='form_block float_cell' >
			    <h1>申請進入列表</h1>
			    <table class='booking_list'>
				  <tr class='data_field '>
				    <td>單號</td>
					<td>申請日</td>
					<td>申請人</td>
					<td align='center'>人數</td>
					<td>查核</td>
					<td align='center'>抽籤</td>
				  </tr>
			      <tbody class='_variable' id='area_booking_today' >
				  </tbody>
				</table>
			  </div>
			  
			  <!-- 抽籤歷程  -->
			  <div class='form_block float_cell' >
			    <h1>抽籤歷程</h1>
				<ul class='lotto_logs _variable' id='lotto_process_logs' >
				   
				</ul>
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