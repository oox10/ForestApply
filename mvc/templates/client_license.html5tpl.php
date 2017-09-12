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
	
	<!-- plugin -->
	<script type="text/javascript" src="tool/jonthornton-jquery-timepicker/jquery.timepicker.min.js"></script>
	<link rel="stylesheet" type="text/css" href="tool/jonthornton-jquery-timepicker/jquery.timepicker.css" />
	
	<link rel="stylesheet" href="tool/jquery-date-range-picker/daterangepicker.min.css">
    <script type="text/javascript" src="tool/jquery-date-range-picker/moment.min.js"></script>
    <script type="text/javascript" src="tool/jquery-date-range-picker/jquery.daterangepicker.min.js"></script>
	
	<!-- PHP DATA -->
	<?php
	$page_info = isset($this->vars['server']['info']) ? $this->vars['server']['info'] : ''; 
	
	$apply_conf = isset($this->vars['server']['data']['preview']) ? $this->vars['server']['data']['preview'] : array(); 
	$apply_view = isset($this->vars['server']['data']['license']) ? $this->vars['server']['data']['license'] : array('PAGE_CONTENT'=>''); 
	
	$apply_progress_info = array('遞交申請','初始階段','抽籤階段','審查階段','等待階段','最終階段');
	
	$area_contect = isset($this->vars['server']['data']['area']['contect']) ? $this->vars['server']['data']['area']['contect'] : array(); 
	
	?>
	<style>
	/*四季背景*/
	body{
	background:url('theme/image/index_background_<?php echo intval((date('m')%12)/3); ?>.jpg') no-repeat center center fixed; 
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
		  <li class='current' ><a href='index.php'>申請進入</a></li>
		  <li ><a href='index.php?act=Landing/applied'>抽籤結果</a></li>
		  <li >聯絡我們</li>
		</ul>
	  </div>
	</header>
    	
	<div class='system_border_area'>
	  <div class='container border-block'>
	    
		<ol class="progress">
		  <li class='action'> 首頁 / 申請預覽：<?php echo $apply_conf['apply_code'];?>  </li>
		  
		  <li class='step checked' data-section='agrement_checker' status='' >  
			<h2>
			  <span class='name' >同意聲明</span>
			  <span class='status'></span>
			</h2>
			<i></i>
		  </li>
		  <li class='step checked' data-section='applicant_form' no=''  status='_INITIAL'>  
			<h2>
			  <span class='name' >申請人註冊</span>
			  <span class='status'></span>
			</h2>
			<div class='info'> </div>
		  </li>
		  <li class='step checked' data-section='booking_form' no=''   status='_FORM'>  
			<h2>
			  <span class='name' >申請資料</span>
			  <span class='status'></span>
			</h2>
			<i></i>
		  </li>
		  <li class='step checked' data-section='member_form' status='_MEMBER' >  
			<h2>
			  <span class='name' >成員名單</span>
			  <span class='status'></span>
			</h2>
			<i></i>
		  </li>
		  <!--
		  <li class='step' data-section='submit_process' no=''   status=''>  
			<h2>
			  <span class='name' >遞交申請</span>
			  <span class='status'></span>
			</h2>
			<i></i>
		  </li>
		  -->
		  <!-- <li class='step'> 修改申請 </li> -->
		  <!-- <li class='step'> 要求補件 </li> -->
		  
		  <li class='step currency' data-section='submit_status' status='_SUBMIT' id='' >  
			<h2>
			  <span class='name' ><?php echo intval($apply_conf['_stage'])<5 ? "申請處理中.." : $apply_conf['_status']; ?></span>
			  <span class='status'></span>
			</h2>
			<!--<i>目前狀態：收件待審</i>-->
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
			  <span class='step_title'>申請步驟 5 - 申請資料處理中，目前位於<i id='apply_progress_info' ><?php echo $apply_progress_info[intval($apply_conf['_stage'])]; ?></i>，狀態為：<i id='apply_status_info' ><?php echo $apply_conf['_status']?></i> </span>
			  <span class='step_option'>
			    <button type='button' class='active' id='act_license_download'  
				        code='<?php echo $apply_conf['apply_code']; ?>' 
						area='<?php echo $apply_conf['application']['area']['code']; ?>' 
						<?php echo (intval($apply_conf['_stage'])==5 && $apply_conf['_final']=='核准進入') ? '':'disabled'; ?>
				><i class='mark16 iconv_pdf'></i> 下載許可證 </button>
			  
			  </span>
			</h1>
			
			<section>
			    <h2> 申請進度與狀態 </h2>
				<table id='apply_process_table'>
				  <tr class='process_header' stage=<?php echo intval($apply_conf['_stage']); ?> >
				    <td >初始階段</td>
					<td >抽籤階段</td>
					<td >審查階段</td>
					<td >等待階段</td>
					<td >最終階段</td>
				  </tr>
				  <tr class='process_task' id='client' > 
				  <?php for( $i=1 ; $i<=5 ; $i++ ): ?>  
				  <?php   if(isset($apply_conf['_progres']['client'][$i])): ?>
                  <?php     $progress_logs = array_reverse($apply_conf['_progres']['client'][$i]);    ?>
				    <td class='stage<?php echo $i; ?> _variable ' >
				  <?php     foreach( $progress_logs as  $conf): ?>
				      <div class='progres_record'>
					    <div class='prmain'><span class='logtime' title='<?php echo $conf['time'];?>'><?php echo substr($conf['time'],0,10); ?></span><span class='loginfo'><?php echo $conf['status']; ?></span></div>
					    <div class='prnote'><span class='loginfo'><?php echo  $conf['note'] ; ?></span></div>
					  </div>
				  <?php     endforeach; ?>
				    </td>
				  <?php   else: ?>
				    <td class='stage<?php echo $i; ?> _variable ' > </td>
				  <?php   endif; ?>
				  <?php endfor; ?>
				  </tr>
				  <tr class='process_task' id='review' > 
				    <td class='stage1 ' >
					<?php if(intval($apply_conf['_stage']) < 3 ): //階段2以前才能異動 ?>
                      <button id='act_apply_tomodify' code='<?php echo $apply_conf['apply_code']; ?>' area='<?php echo $apply_conf['application']['area']['code']; ?>' >申請資料異動</button>					
					<?php endif; ?>
					</td>
				    <td class='stage2 _variable' > 
					<?php if(isset($apply_conf['_progres']['review'][2])): ?>  
				    <?php   foreach( $apply_conf['_progres']['review'][2] as  $conf): ?>
				      <div class='progres_record'>
					    <div class='prmain'><span class='logtime' title='<?php echo $conf['time'];?>'><?php echo substr($conf['time'],0,10); ?></span><span class='loginfo'><?php echo $conf['status']; ?></span></div>
					    <div class='prnote'><span class='loginfo'><?php echo  $conf['note'] ; ?></span></div>
					  </div>
				    <?php   endforeach; ?>
				    <?php   endif; ?>
					</td>
				    <td class='stage3 ' >
					<?php if(intval($apply_conf['_stage']) == 3 ): //階段2以前才能異動 ?>
                      <button id='act_apply_toreview' code='<?php echo $apply_conf['apply_code']; ?>' area='<?php echo $apply_conf['application']['area']['code']; ?>' >申請資料補充</button>				
					<?php endif; ?>
					</td>
				    <td class='stage4 _variable' > 
					<?php if(isset($apply_conf['_progres']['review'][4])): ?>  
				    <?php   foreach( $apply_conf['_progres']['review'][4] as  $conf): ?>
				      <div class='progres_record'>
					    <div class='prmain'><span class='logtime' title='<?php echo $conf['time'];?>'><?php echo substr($conf['time'],0,10); ?></span><span class='loginfo'><?php echo $conf['status']; ?></span></div>
					    <div class='prnote'><span class='loginfo'><?php echo  $conf['note'] ; ?></span></div>
					  </div>
				    <?php   endforeach; ?>
				    <?php   endif; ?>
					</td>
					<td class='stage5' >
					  <?php if(!$apply_conf['_isdone']): //進入取消日前才可以取消  ?>
					  <button id='act_apply_tocancel' code='<?php echo $apply_conf['apply_code']; ?>' area='<?php echo $apply_conf['application']['area']['code']; ?>'>取消申請</button>
					  <?php endif; ?>
					</td>
				  </tr>
				</table>
				
			    <h2> 申請資料預覽 </h2>
				<div class='license' id='license_preview'>
				<?php echo isset($apply_view['PAGE_CONTENT']) ? $apply_view['PAGE_CONTENT'] : '查無資料'; ?>
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
