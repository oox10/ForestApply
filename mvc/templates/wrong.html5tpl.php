<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?php echo defined('_SYSTEM_HTML_TITLE') ? _SYSTEM_HTML_TITLE:'資料庫';?></title>
	<!-- CSS -->
	<link type="text/css" href="tool/font-awesome-4.6.2/css/font-awesome.min.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_system.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_client.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_booking.css" />
	
	<!-- JS -->
	<script type="text/javascript" src="tool/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="tool/jquery-ui-1.11.2.custom/jquery-ui.min.js"></script>
	<script type="text/javascript" src="tool/canvasloader-min.js"></script>	
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_client.js"></script>
	<script type="text/javascript" src="js_apply.js"></script>
	
	<!-- PHP DATA -->
	<?php
	$page_info =  ''; 
	$area_contect = isset($this->vars['server']['data']['area']['contect']) ? $this->vars['server']['data']['area']['contect'] : array(); 
	$alert_message = isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';
	
	?>
	
  </head>
  
  <body>
    
	<header class='navbar '>
	  <div class='container'>
	    <div id='navbar-header'>
		  <img  id='system_mark' src='theme/image/logo2.png'>
		  <span id='system_title' >保護留區進入申請系統</span>
		</div>
		<ul id='navbar-manual'>
		  <li><a href='index.php'>首頁</a></li>
		  <li>最新消息</li>
		  <li>常見問題</li>
		  <li>抽籤結果</li>
		  <li>聯絡我們</li>
		</ul>
	  </div>
	</header>
    	
	<div class='system_border_area'>
	  <div class='container border-block'>
		<ol class="progress">
		  <li class='action'> 首頁 / ERROR PAGE </li>
		  <li class='step ' data-section='agrement_checker' status='' >  
			<h2>
			  <span class='name' >同意聲明</span>
			  <span class='status'></span>
			</h2>
			<i></i>
		  </li>
		  <li class='step ' data-section='applicant_form' no=''  status='_INITIAL'>  
			<h2>
			  <span class='name' >申請人資料</span>
			  <span class='status'></span>
			</h2>
			<div class='info'> </div>
		  </li>
		  <li class='step ' data-section='booking_form' no=''   status='_FORM'>  
			<h2>
			  <span class='name' >填寫申請資料</span>
			  <span class='status'></span>
			</h2>
			<i></i>
		  </li>
		  <li class='step ' data-section='member_form' status='_MEMBER' >  
			<h2>
			  <span class='name' >填寫成員名單</span>
			  <span class='status'></span>
			</h2>
			<i></i>
		  </li>
		  <li class='step currency error' data-section='submit_status' status='_ERROR' id='' >  
			<h2>
			  <span class='name' >發生錯誤</span>
			  <span class='status'></span>
			</h2>
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
		  <!-- 審核狀態 -->
		  <div class='booking_step' id='submit_status' style='display:block;'>
		    <h1>
			  <span class='step_title'>發生錯誤 </span>
			  <span class='step_option'>
			    <button type='button' class='active' onclick="window.history.go(-1);"   > 回到上一頁 </button>	
			  </span>
			</h1>
			
			<section>
			  <div class="declaration" >
                <h2>發生時間：</h2>
				<p class='rule'><?php echo date('Y-m-d H:i:s'); ?></p>
				<h2>錯誤訊息：</h2>
				<p class='rule'><span style='font-weight:bold;font-size:1em;color:red;'><?php echo $alert_message; ?></span></p>
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
