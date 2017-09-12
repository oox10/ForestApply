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
	<script type="text/javascript" src="tool/Highcharts-4.2.3/js/highcharts.js"></script>
	
	<!-- Self -->
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_main.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_dashboard.css" />
	
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_dashboard.js"></script>
	
	
	<!-- PHP -->
	<?php
	
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	$page_info 		= isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';  
	$dashboard 		= isset($this->vars['server']['data']['dashboard']) ? $this->vars['server']['data']['dashboard'] 	: array();     
	$datalogs 		= isset($this->vars['server']['data']['datalogs']) 	? $this->vars['server']['data']['datalogs'] 	: array('sync'=>array(),'dump'=>array());  
	
	$post_data		= isset($this->vars['server']['data']['post']) 		? $this->vars['server']['data']['post'] 	: array('client'=>array(),'admin'=>array());
	
	
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
		  <ul class='module_summary'>
		    <li> 
			  <div class='counter'><?php echo isset($dashboard['module']['account']) ? $dashboard['module']['account']:0;?></div>
			  <h1>帳號管理</h1>
			  <span class='module_iconv'><i class="fa fa-user-plus" aria-hidden="true"></i></span>
			  <span class='module_reset'></span>
			</li>
		    <li>
			  <div class='counter'><?php echo isset($dashboard['module']['metadata']) ? $dashboard['module']['metadata']:0;?></div>
			  <h1>資料總數</h1>
			  <span class='module_iconv'><i class="fa fa-archive" aria-hidden="true"></i></span>
			  <span class='module_reset'></span>
			</li>
		    <li>
			  <div class='counter'><?php echo isset($dashboard['module']['gathering']) ? $dashboard['module']['gathering']:0;?></div>
			  <h1>新增徵集</h1>
			  <span class='module_iconv'><i class="fa fa-picture-o" aria-hidden="true"></i></span>
			  <span class='module_reset'></span>
			</li>
		    <li>
			  <div class='counter'><?php echo isset($dashboard['module']['feedback']) ? $dashboard['module']['feedback']:0;?></div>
			  <h1>問題回報</h1>
			  <span class='module_iconv'><i class="fa fa-commenting" aria-hidden="true"></i></span>
			  <span class='module_reset'></span>
			</li>
		  </ul>
		</div>
		
		<div class='main_content' >
		  <div class='datainfo_area'>
			  <h1> 系統內容資訊 </h1>
			  <div class='collection_info'>
				<div class='data_amount' id='amount_chart'>
				  asdfasdf
				</div>
				<div class='data_control'>
				  
				  <h1>
				    <span>同步資訊</span>
					<button type='button' id='act_sync_now' class=' active'> 立即同步 </button>  
				  </h1>
				  <ul class='logs sync_log'>
				  <?php foreach($datalogs['sync'] as $logs): ?>	  
				    <li>
					  <span><?php echo $logs['time']; ?></span>
					  <span><?php echo $logs['name']; ?></span>
					  <span><?php echo $logs['result']; ?></span>
					</li> 
				  <?php endforeach; ?>
				  </ul>
				  
				  <h1>
				    <span>備份資訊</span> 
				    <button type='button' id='act_dump_now' class='active'> 立即備份 </button>
				  </h1>
				  <ul class='logs dump_log'>
				  <?php foreach($datalogs['dump'] as $logs): ?>	  
				    <li>
					  <span><?php echo substr($logs['time'],0,10); ?></span>
					  <span><?php echo $logs['name']; ?></span>
					  <span><?php echo $logs['result']; ?></span>
					</li> 
				  <?php endforeach; ?>	  
				  </ul>
				  
				</div>
			  </div>
		  </div>
		  
		  <div class='contents_area post_container'>
			<div class='post_list' id='system_alert_post'>
			  <h1>管理系統公告</h1>
			  <ul>
				<?php foreach($post_data['admin'] as $post): ?>
				<li>
				<div class='post' no='<?php echo $post['pno'];?>' top='<?php echo $post['post_level'] > 2 ? 1 : 0; ?>' mark="<?php echo $post['post_type']; ?>" >
				  <h2>
					<span class='post_date' > <?php echo substr($post['post_time_start'],0,10); ?></span>
					
					<span class='post_title'><?php echo $post['post_title']; ?></span>
					<span class='post_rate' style='width:<?php echo ($post['post_level']-1)*22; ?>px'>  </span>
				  </h2>
				  <div class='post_content'>
					<?php echo System_Helper::short_string_utf8(strip_tags(htmlspecialchars_decode($post['post_content'])),100);?>
				  </div>
				  <div class='post_refer'>
					<span class='post_organ'> From <?php echo $post['post_from']; ?> </span>
				  </div>
				</div>
				</li>
			  <?php endforeach; ?>
			  </ul>
			</div>
			
			<div class='post_list' id='content_fix_post'>
			  <h1>檢索系統公告</h1>
			  <ul>
				<?php foreach($post_data['client'] as $post): ?>
				<li>
				<div class='post' no='<?php echo $post['pno'];?>' top='<?php echo $post['post_level'] > 2 ? 1 : 0; ?>' mark="<?php echo $post['post_type']; ?>" >
				  <h2>
					<span class='post_date' > <?php echo substr($post['post_time_start'],0,10); ?></span>
					<span class='post_title'><?php echo $post['post_title']; ?></span>
					<span class='post_rate' style='width:<?php echo ($post['post_level']-1)*22; ?>px'>  </span>
				  </h2>
				  <div class='post_content'>
					<?php echo System_Helper::short_string_utf8(strip_tags(htmlspecialchars_decode($post['post_content'])),100);?>
				  </div>
				  <div class='post_refer'>
					<span class='post_organ'> From <?php echo $post['post_from']; ?> </span>
				  </div>
				</div>
				</li>
			  <?php endforeach; ?>
			  </ul>
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
		  <div class='msg_info'><?php echo $page_info; ?></div>
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