<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?php echo defined('_SYSTEM_HTML_TITLE') ? _SYSTEM_HTML_TITLE:'資料庫';?></title>
	<!-- CSS -->
	<link type="text/css" href="tool/jquery-ui-1.11.2.custom/jquery-ui.structure.min.css" rel="stylesheet" />
	<link type="text/css" href="tool/jquery-ui-1.11.2.custom/jquery-ui.theme.min.css" rel="stylesheet" />
	<link type="text/css" href="tool/font-awesome-4.6.2/css/font-awesome.min.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_system.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_client.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_booking.css" />
	
	<!-- JS -->
	<script type="text/javascript" src="tool/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="tool/jquery-ui-1.11.2.custom/jquery-ui.min.js"></script>
	<script type="text/javascript" src="tool/canvasloader-min.js"></script>	
	
	<link rel="stylesheet" href="tool/jquery-date-range-picker/daterangepicker.min.css">
    <script type="text/javascript" src="tool/jquery-date-range-picker/moment.min.js"></script>
    <script type="text/javascript" src="tool/jquery-date-range-picker/jquery.daterangepicker.min.js"></script>
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_client.js"></script>
	<script type="text/javascript" src="js_applied.js"></script>
	
	
	
	<!-- PHP DATA -->
	<?php
	$page_info = isset($this->vars['server']['info']) ? $this->vars['server']['info'] : ''; 
	
	$area_list = isset($this->vars['server']['data']['area']['list']) ? $this->vars['server']['data']['area']['list'] : array(); 
	$area_type = isset($this->vars['server']['data']['area']['type']) ? $this->vars['server']['data']['area']['type'] : array(); 
	$area_contect = isset($this->vars['server']['data']['area']['contect']) ? $this->vars['server']['data']['area']['contect'] : array();
	
	$area_group_flag = '';
	
	
	?>
	<style>
	/*四季背景*/
	body{
	background:url('theme/image/index_background_f<?php echo intval(((date('m')%12)/3)); ?>.jpg') no-repeat center center fixed; 
    background-repeat: no-repeat;
    background-position:middle;
    background-size: cover; 
    }
	</style>
  </head>
  
  <body>
    
	<header class='navbar '>
	  <div class='container'>
	    <div id='navbar-header'>
		  <img  id='organ_mark' src='theme/image/logo2.png'>
		  <img  id='system_mark' src='theme/image/mark_forest_area.png' />
		  <span id='system_title' >自然保護區域進入申請系統</span>
		</div>
		<ul id='navbar-manual'>
		  <li ><a href='index.php'>首頁</a></li>
		  <li ><a href='index.php#announcement'>最新消息</a></li>
		  <li ><a href='index.php'>申請進入</a></li>
		  <li class='current' >抽籤結果</li>
		  <li >聯絡我們</li>
		</ul>
	  </div>
	</header>
    	
	<div class='system_border_area'>
	  <div class='container border-block'>
	    
		<ol class="progress">
		  <li class='action'> 首頁 / 各區申請狀態查詢：  </li>
		  <li class='forms'>
		    <label> 選擇申請區域 </label>
			<select id='area_into_selecter' >
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
		  </li>  
		  <li class='forms' >
		    / <label> 選擇進入日期 </label>
			<input type='date' id='apply_inter_date' value='<?php echo date('Y-m-d'); ?>' />
		  </li>
		  
		  <li class='forms' >
		    <button type='button' class='active' id='applied_search' > 查詢 </button>
		  </li>
		</ol>
		
	  </div>
	</div>	
		
    <div class='system_body_area'>
	  
	  <div class='container content-block'>
	    
		<aside class='function_area'>
		  <h1> 申請功能</h1>
		  <ul>
		    <li> 線上申請進入區域 </li>
		    <li> 申請資料異動</li>
			<li> 申請許可預覽與下載</li>
		  </ul>
		  <h1> 相關連結與下載 </h1>
		  <ul>  
		    <li>申請成員表單</li>
			<li>自然保護區法規</li>
		    <li>自然保留區法規</li>
			<li>野生動物保護區法規</li>
		  </ul>
		</aside>
		
	    <div class='workaround_area' >
		  <div class='booking_step' id='submit_status' style='display:block;'>
		    <h1>
			  <span class='step_title'> <i id='area_name' ></i> <i id='area_name' ></i> </span>
			</h1>
			
			<section id='applied_status' >
			    <h2 > 
				  <span>本月申請狀態</span> 
				  <div class='date_option'>
					<span class='date_change option' mode='-' ><i class="fa fa-chevron-left" aria-hidden="true"></i></span>
					<span class='date_ynm' data-month='<?php echo date('Y-m');?>' ><?php echo date('Y年m月');?></span>
					<span class='date_change option' mode='+'><i class="fa fa-chevron-right" aria-hidden="true"></i></span>
				  </div>
				</h2>
				<div class='area-block'>
				  <div class='calendar' id='area_calendar' area='' >
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
			    </div>
			    
				<h2> 當日抽籤情況 </h2>
				<div class='lotto_block' id='lotto_infomation'>
				  
				  <div class='form_block ' >
					<table class='booking_list'>
					  <tr class='data_field '>
						<td>單號</td>
						<td>申請日期</td>
						<td>申請人</td>
						<td align='center'>成員人數</td>
						<td>抽籤結果</td>
						<td align='center'>抽籤</td>
					  </tr>
					  <tbody class='_variable' id='area_booking_today' >
					  </tbody>
					</table>
				  </div>
				  
				</div>
			
			</section>
			
		  </div>
		</div>
		  
	  </div>
		
		
	</div>
	  
	
	<footer>
	<?php include("area_client_footer.php");?>    
	</footer>
	
	
	
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
