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
	
	$group_list = isset($this->vars['server']['data']['group']) ? $this->vars['server']['data']['group'] : array();
	$page_info  = isset($this->vars['server']['info']) ? $this->vars['server']['info'] : ''; 
	
	?>
	
  </head>
  
  <body>
    <div class='system_main_area '>
	  <!-- 系統TITLE BANNER -->
	  
	  <div class='system_body_area has_footer'>
	    <div class='system_body_block'>
		  <div class='ad_login_block' >
		      
			  <div class='system_descrip_block register'>
				<div class='desc_border'>註冊須知</div>
				  <p class='format_desc'>
					<ol class='test_list'>
					  <li><span class='test_title'>1</span>:  填寫申請表單，確認資料無誤，送出申請</li>
					  <li><span class='test_title'>2</span>:  請注意：* 號欄位皆為必填欄位。</li>
					  <li><span class='test_title'>3</span>:  審核過後，申請結果將會以電子郵件方式寄到您的申請信箱中，並進行後續帳號設定。</li>
					</ol>
				  </p>
				<div class='desc_border'>系統需求</div>
				  <p class='format_desc'>
					本系統使用 HTML5 , CSS3 網頁標準製作，請使用支援此標準的瀏覽器，並請勿關閉瀏覽器的 Javascript 支援，以得到最好的體驗。本系統支援瀏覽器清單如下: 				
				  </p>
				  <div class='support_browser tr_like'>
					<div class='support_item' ><a class='mark30 pic_chrome' title='前往下載 Chrome 瀏覽器' href='http://www.google.com/intl/zh-TW/chrome/' target='_blank' ></a></div>
					<div class='support_item' ><a class='mark30 pic_firefox' title='前往下載 Firefox 瀏覽器' href='http://mozilla.com.tw/firefox/new/' target='_blank' ></a></div>
					<div class='support_item' ><a class='mark30 pic_safari' title='前往下載 Safari 瀏覽器' href='http://www.apple.com/tw/safari/' target='_blank' ></a></div>
					<div class='support_item' ><a class='mark30 pic_opera' title='前往下載 Opera 瀏覽器' href='http://www.opera.com/zh-tw' target='_blank' ></a></div>
					<div class='support_item' ><a class='mark30 pic_explorer' href='http://windows.microsoft.com/zh-tw/internet-explorer/ie-9-worldwide-languages' target='_blank' ></a><span> 11 以上版本</span></div>
				  </div>
				  <div class='desc_border'>連絡我們</div>
				  <table class='contact_table'>
					<tr> <td> 國立臺灣大學森林環境暨資源學系 </td></tr>
					<tr> <td >(02) - 33665275 賴小姐</td> </tr>
					<tr> <td> 系統維護 </td></tr>
					<tr> <td >(02) - 33669847 蕭先生</td> </tr>
				  </table>
			  </div>
			  
			  <div class='system_login_area' id='regist_type'>
				<div class='signin_header'> <h1><?php echo _SYSTEM_HTML_TITLE; ?> </h1> <h2>帳號註冊</h2> </div>
				<div class='regist_form'>			
				  <div class='regfield _nessary'>
					<label>帳號</label> <input type='text' class='_regist' id='user_mail' placeholder=" 帳號請使用 email" />
				  </div>
				  <div class='regfield'>
					<label>密碼</label> <input type='text'  id='' value='成功後將寄發密碼設定信' placeholder='' readonly=true disabled=true/>
				  </div>
				  <div class=' regfield _nessary'>
				    <label>單位</label> 
					<select class='_regist' id='user_group'>
					  <option value=''> - </option>
					  <?php foreach($group_list as $glist) : ?>
					  
					  <?php   if( !isset($group) || $group!=$glist['ug_info']): ?>
					  <optgroup label="<?php echo $glist['ug_info']; ?>">
					  <?php     $group=$glist['ug_info'];?>
					  <?php   endif?>
					  <option value='<?php echo $glist['ug_code']?>'><?php echo $glist['ug_name'];?></option>
					  <?php endforeach; ?>
					  </select>
				  </div>
				  <div class='tr_like'>
				    <div class='colset regfield  _nessary'>
					  <label>姓名</label> <input type='text' class='_regist'  id='user_name' placeholder=" 請填寫姓名" /> 
				    </div>
				    <div class='colset regfield'>
					  <label>代號</label> <input type='text' class='_regist'  id='user_idno' title='' placeholder="稱謂或暱稱，可不填" /> 
				    </div>
				  </div>
				  <div class='tr_like'>
					<div class='colset regfield _nessary' >
					  <label>組室科別</label> <input type='text' class='_regist' id='user_organ' placeholder=" 請填寫組室科別" /> 
					</div>
				    <div class='colset regfield _nessary'>
					  <label>職稱</label> <input type='text'  class='_regist' id='user_staff' placeholder=" 請填寫職稱" /> 
				    </div>
				  </div>
				  <div class='regfield _nessary'>
					<label>聯絡地址</label> <input type='text' class='_regist' id='user_address' placeholder="辦公室或單位地址" /> 
				  </div>				  
				  <div class='regfield _nessary'>
					<label>聯絡電話</label> <input type='text' class='_regist' id='user_tel' placeholder=" 電話或分機號碼" /> 
				  </div>
				  <div class='signin_func' id='regist_submit'>
				    <span class='captcha'>
				      <input type="text" class='' id='captcha_input' name="Turing" value="" style='width:90px;' maxlength="5">
					  <img src="tool/captcha/code.php" id="captcha">
					  [ <a href="#" onclick="document.getElementById('captcha').src = document.getElementById('captcha').src + '?' + (new Date()).getMilliseconds()" title='新驗證碼'><i class='mark16 option pic_reflash'></i></a> ] 				
					</span>
					<span class='submit'> <button type='button' class='active' id='reg_act_sent'>註冊</button> </span>
				  </div>
                  <div class='signin_func' id='regist_finish'>
				    <span class='process'></span>
					<span class='submit'> <button type='button' class='active' id='act_login'>回首頁</button> </span>
				  </div> 				  
				</div>
				<div class='register_borad'>
				  <a id='act_cancel'>取消</a>
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
