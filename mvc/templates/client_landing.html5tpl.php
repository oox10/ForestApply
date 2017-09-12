<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	
	<title><?php echo defined('_SYSTEM_HTML_TITLE') ? _SYSTEM_HTML_TITLE:'資料庫';?></title>
	<!-- CSS -->
	<link type="text/css" href="tool/font-awesome-4.6.2/css/font-awesome.min.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_system.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_client.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_landing.css" />
	
	<!-- JS -->
	<script type="text/javascript" src="tool/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="tool/jquery-ui-1.11.2.custom/jquery-ui.min.js"></script>
	<script type="text/javascript" src="tool/canvasloader-min.js"></script>	
	
	<!-- TOOL -->
	<script type="text/javascript" src="tool/jquery-date-range-picker/moment.min.js"></script>
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_client.js"></script>
	<script type="text/javascript" src="js_landing.js"></script>
	
	<!-- PHP DATA -->
	
	<?php
	$page_info = isset($this->vars['server']['info']) ? $this->vars['server']['info'] : ''; 
	
	$post_list = isset($this->vars['server']['data']['post']) ? $this->vars['server']['data']['post'] : ''; 
	$area_list = isset($this->vars['server']['data']['area']['list']) ? $this->vars['server']['data']['area']['list'] : array(); 
	$area_type = isset($this->vars['server']['data']['area']['type']) ? $this->vars['server']['data']['area']['type'] : array(); 
	$area_contect = isset($this->vars['server']['data']['area']['contect']) ? $this->vars['server']['data']['area']['contect'] : array(); 
	
	$area_group_flag = '';
	
	
	$nowtime   = date('Y-m-d H:i:s');
	
	$month_start  = date('N',strtotime('first day of this month', time()))%7;
	$month_length = date('d',strtotime('last day of this month', time()));
	$month_today  = $month_start+date('d')-1;
    
	$month_start_m2  = date('N',strtotime('first day of this month', strtotime('+1 month')))%7;
	$month_length_m2 = date('d',strtotime('last day of this month', strtotime('+1 month')));
	
	?>
	
	<style>
	/*四季背景*/
	body{
	/*background:url('theme/image/index_background_<?php echo intval((date('m')%12)/3); ?>.jpg') no-repeat center center fixed; */
	background:url('theme/image/index_background_f3.jpg') no-repeat center center fixed; 
    background-repeat: no-repeat;
    background-position:top;
    background-size: cover; 
    }
	</style>
	
  </head>
  
  <body>
    
	<header class='navbar '>
	  <div class='container'>
	    <div id='navbar-header'>
		  <img  id='organ_mark' src='theme/image/logo2.png'>
		  <span id='system_title' >自然保護區域進入申請系統</span>
		</div>
		<ul id='navbar-manual'></ul>
	  </div>
	</header>
    	
	<div class='system_border_area'>
	  <div class='container border-block'>
	    
		<div class='introduction' >
	      <h1>
		    <img  id='system_mark' src='theme/image/mark_forest_area.png' style='height:100px;'/>
		    <span>自然保護區域進入<br/>申請系統</span>
		  </h1>
	      <p>
		    本系統提供農委會主管法規所公告（含林務局或各地方政府管理）之自然保留區、自然保護區、野生動物保護區進入申請服務，申請前請先參照申請須知與流程，並確認該區域公告與目前之承載量管制人數」
		  </p>
		</div>
		
		<div class='registration' >
		  <ul class='form_manual'>
		    <li class='option formmode atthis' data-dom='booking_form'>保護留區進入申請</li>
			<li class='option formmode ' data-dom='regist_form'>申請單查詢</li>
			<li class='option  link' ><a href='index.php?act=Landing/applied' >抽籤結果查詢</a></li>
		  </ul>
		  <div class='formblock' id='booking_form'>
		    <h1>保護留區進入申請</h1>
		    <div class='form_raw' data-field='booking_code' >
			  <label>區域類型</label>
			  <select id='area_type_sel' >
				<option value='' disabled selected>請選擇區域類型</option>
				<?php foreach($area_type as $at): ?>  
				<option value='<?php echo $at; ?>' ><?php echo $at; ?></option>
				<?php endforeach; ?>
			  </select>
			</div>
		    <div class='form_raw' data-field='booking_code' >
			  <label>選擇區域</label>
			  <select id='apply_area_sel' >
			    <option value='' disabled selected>請選擇申請區域</option>
				<?php foreach($area_list as $area): ?>  
			    <?php 
				  if($area_group_flag != $area['area_type']){
					$area_group_flag = $area['area_type'];
					echo '<optgroup label="'.$area['area_type'].'">  ';	
				  }
				?>
				<option value="<?php echo $area['area_code'];?>" > <?php echo $area['area_name']; ?> </option>
				<?php endforeach; ?>  	
			  </select>
			</div>
            <div class='form_func'>
			  <button class='cancel'>取消</button>
			  <button class='submit blue' id='area_reserve' >我要申請</button>
			</div>			
		  </div>
		  
		  <div class='formblock ' id='regist_form'>
		    <div id='check_form' >
			  <h1>申請單查詢</h1>		
              <div class='form_raw' data-field='booking_code' >
			    <label>申請編號</label>
				<input type='text' id='applied_code'  placeholder="8個英數字母"   />
			  </div>	
			  <div class='form_raw ' data-field='booking_check'>
			    <label>申請人EMAIL</label>
				<input type='text' id='applier_mail'  placeholder="申請人電子郵件" />
			  </div>
			  <div class='form_func'>
				<a class='option forgot' id='act_forgot'> 忘記編號 </a>
				<span>
				  <button class='cancel'  >取消</button>
				  <button class='submit blue' id='user_applied' >查詢</button>
				</span>
			  </div>
			</div>
			<div id='recover_form' >
			  <h1>重新寄發申請編號</h1>		
              <div class='form_raw' data-field='booking_code' >
			    <label>申請人身分證號 / 護照號碼</label>
				<input type='text'  placeholder="英數字母"   />
			  </div>	
			  <div class='form_raw' data-field='booking_check'>
			    <label>申請人信箱</label>
				<input type='email' placeholder="電子郵件信箱" />
			  </div>
			  <div class='form_raw fail' data-field='booking_check'>
			    <label>申請進入日期</label>
				<input type='text' placeholder="西元年月日共10碼" readonly=true/>
			  </div>
			  <div class='form_func'>
			    <a class='option return' id='act_gohome' > 回到首頁 </a>
				<span>
				  <button class='cancel' from='forgot' >取消</button>
				  <button class='submit blue'>查詢</button>
				</span>
			  </div>
			</div>
		  
		  </div>
		</div>
		
		
		
		
	  </div>
	</div>	
		
    <div class='system_body_area'>
	  
	  <div class='container information-block' id='announcement'>
	    <div class='billboard' more='0' >
	      <h1>
		    <label>最新消息</label>
		    <?php if(count($post_list )>6): ?>
			<span class='more option' id='act_switch_post_mode' ><i class="fa fa-angle-double-right" aria-hidden="true"></i> <i class='more' title='顯示所有公告'>MORE</i><i class='hide' title='顯示前列公告'>HIDE</i> </span>	
			<?php endif; ?>
		  </h1>
		  <div class='news_block' >
		    <?php foreach($post_list as $post): ?>   
            <div class='post' no='<?php echo intval($post['pno']);?>' top='<?php echo $post['post_level'] > 2 ? 1 : 0; ?>' mark="<?php echo $post['post_type']; ?>" popout='<?php echo $post['post_type']=='緊急通告' ? 1 : 0; ?>'  >
			  <h2>
			    <span class='post_date' > <?php echo substr($post['post_time_start'],0,10); ?></span>
				<span class='post_type' > <?php echo $post['post_type']; ?> </span>
				<span class='post_summary' > <?php echo $post['post_title']; ?> </span>
				<span class='post_organ'>  <?php echo $post['post_from']; ?> </span>
				<span class='post_rate' style='width:<?php echo ($post['post_level']-1)*22; ?>px'>  </span>
			  </h2>
			  <div class='post_content'>
			    <?php echo $post['post_title']; ?>
			  </div>
			</div>
		    <?php endforeach; ?>
		  </div>
		</div> 
	  </div>
	  
	  <div class='container ' >
	    
		<div class='situation'>
	      <h1>
		    <label>區域申請狀況</label>
		  </h1>
		  <div class='area-block'>
			  <div class='calendar' area='' >
				<div  class='option_block'> 
				  <div class='locat_option'>
					<select class='area_type_selecter' >
					  <?php foreach($area_type as $at): ?>  
					  <option value='<?php echo $at; ?>'   <?php echo $at=='自然保留區'? 'selected':'';?>   ><?php echo $at; ?></option>
					  <?php endforeach; ?>
					</select>
					<select class='area_into_selecter'>
					  <?php foreach($area_list as $area): ?>  
					  <?php 
						if($area_group_flag != $area['area_type']){
						  $area_group_flag = $area['area_type'];
						  echo '<optgroup label="'.$area['area_type'].'">  ';	
						}
					  ?>
						<option value="<?php echo $area['area_code'];?>" > <?php echo $area['area_name']; ?> </option>
					  <?php endforeach; ?>  	
					</select>
				  </div>
				  <div class='date_option'>
					<span class='date_change option' mode='-' ><i class="fa fa-chevron-left" aria-hidden="true"></i></span>
					<span class='date_ynm' data-month='<?php echo date('Y-m');?>' ><?php echo date('Y年m月');?></span>
					<span class='date_change option' mode='+'><i class="fa fa-chevron-right" aria-hidden="true"></i></span>
				  </div>
				</div>
				<div  class='week_block' > 
				  <div class='date'><span>日</span></div>
				  <div class='date'><span>一</span></div>
				  <div class='date'><span>二</span></div>
				  <div class='date'><span>三</span></div>
				  <div class='date'><span>四</span></div>
				  <div class='date'><span>五</span></div>
				  <div class='date'><span>六</span></div>
				</div>
				<div class='date_container apply_datas apply_entry' data-today='<?php echo date('Y-m-d');?>' > </div>
				<div class='area_limit' >
				  <span id='area_target'></span>申請進入每日上限：<span id='area_load'></span> 人
				</div>
			  </div>
		      <div class='descrip' area='' >
				<div class='photo_container'> </div>
				<div class='area_descrip'> </div>
			  </div>
		  </div>
		
		
		</div>
	  </div>
	  
	  <!-- area intro -->
	  <div class='container ' >
	    <div>-</div>
	  </div>
	  
	</div>	
	
	<footer>
	<?php include("area_client_footer.php"); ?>    
	</footer>
	
	<!-- 框架外結構  -->
	
	<!-- 公告訊息 -->
	<div class='system_announcement_area'>
	    <div class='container'>
		  <div id='announcement_block'>
		    <h1>
			  <div class='ann_header'>
			    <span class='ann_type'></span> 
				<span class='ann_title'></span> 
			  </div>
			  <span class='ann_close option' title='關閉'><i class="fa fa-times-circle" aria-hidden="true"></i></span>
			</h1>
			<div class='ann_contents'></div>
			<div class='ann_footer'>
			  <div>
			    <span class='ann_time'>  </span>
				From
				<span class='ann_from'>  </span>
			  </div>
			  <div>
			    <span class='ann_counter'>  </span>
			  </div>
			</div>
		  </div>
        </div>
	</div>
	
	<!-- 系統訊息 -->
	<div class='system_message_area'>
	  <div class='message_block'>
		<div id='message_container'>
		  <div class='msg_title'></div>
		  <div class='msg_info'><?php echo $page_info;?></div>
		</div>
		<div id='area_close'></div>
      </div>
	</div> 
    
  </body>
</html>
