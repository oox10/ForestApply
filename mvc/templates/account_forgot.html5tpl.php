<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<title><?php echo defined('_SYSTEM_HTML_TITLE') ? _SYSTEM_HTML_TITLE:'資料庫';?></title>
	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_main.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_account.css" />
	
	<!-- JS -->
	<script type="text/javascript" src="tool/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="tool/jquery-ui-1.11.2.custom/jquery-ui.min.js"></script>
	<script type="text/javascript" src="tool/canvasloader-min.js"></script>	
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_account.js"></script>
	<!-- PHP DATA -->
	
	<?php
	$page_info   = isset($this->vars['server']['info']) ? $this->vars['server']['info'] : ''; 
	?>
	
  </head>
  
  <body>
    <div class='system_main_area '>
	  <!-- 系統TITLE BANNER -->
	  
	  <div class='system_body_area has_footer'>
	    <div class='system_body_block'>
		  <div class='ad_login_block tr_like' >
		
		  <?php include("area_admin_intro.php"); ?>	  
			  
		  <div class='system_login_area'>
            <div class='signin_header'> <h1>帳號回復</h1><h2>發送密碼設定信</h2> </div>
			<div class='signin_form'>			
              <input type='email' id='userid'     class='lg_text _keyin' placeholder="請輸入註冊帳號">
			  <input type='email' id='email' 	  class='lg_text _keyin' placeholder="請輸入註冊信箱">
			  <div class='signin_func' id='regist_submit'>
				<input type="text" class='_keyin' id='captcha_input' name="Turing" value="" style='width:120px;'>
				<img src="tool/captcha/code.php" id="captcha">
				[ <a id='captcha_refresh' href="#" onclick="document.getElementById('captcha').src = document.getElementById('captcha').src + '?' + (new Date()).getMilliseconds()" title='新驗證碼'><i class='mark16 option pic_reflash'></i></a> ] 				
			  </div>
			  <div class='signin_func'>
				<span class='signin'> <button id='act_reset'>取消</button> </span>
				<span class='submit'> <button id='act_recover'>密碼重設信</button> </span>
			  </div> 
			</div>
			<div class='register_borad'>
              <a id='act_login'>回首頁</a>
			</div>	
		  </div>
		  
		  </div>
		</div>
	  </div>
	</div>
	<?php include("area_admin_footer.php"); ?>
	
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
