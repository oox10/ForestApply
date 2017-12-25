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
	
	$page_content = isset($this->vars['server']['data']['page']) ? $this->vars['server']['data']['page'] : array(); 
	
	$area_list = isset($this->vars['server']['data']['area']['list']) ? $this->vars['server']['data']['area']['list'] : array(); 
	$area_type = isset($this->vars['server']['data']['area']['type']) ? $this->vars['server']['data']['area']['type'] : array(); 
	$area_contect = isset($this->vars['server']['data']['area']['contect']) ? $this->vars['server']['data']['area']['contect'] : array();
	
	$apply_area = isset($this->vars['server']['data']['info']['area']) ? $this->vars['server']['data']['info']['area'] : array(); 
	$start_date = isset($this->vars['server']['data']['info']['start']) ? $this->vars['server']['data']['info']['start'] : date('Y-m-d');  // 最近的申請日期
	
	$apply_date = isset($this->vars['server']['data']['date']) ? $this->vars['server']['data']['date'] : array(); 
	$picker_config = isset($this->vars['server']['data']['picker']) ? json_encode($this->vars['server']['data']['picker']) : '[]'; 
	
	//echo "<pre>";
	//var_dump($apply_date);
	//exit(1);
	
	$apply_form = isset($apply_area['forms']) ? $apply_area['forms'] : array(''); 
	
	$optiontime   = strtotime('+'.$apply_area['accept_min_day'].' day');
	$month_start  = date('N',strtotime('first day of this month', $optiontime))%7;
	$month_length = date('d',strtotime('last day of this month', $optiontime));
	$month_today  = '';//$month_start+date('d')-1;
    
	$optiontime2   = strtotime('+1 month',$optiontime);
	$month_start2  = date('N',strtotime('first day of this month', $optiontime2))%7;
	$month_length2 = date('d',strtotime('last day of this month', $optiontime2));
	$month_today  = '';//$month_start+date('d')-1;
	
	?>
	
	<style>
	  body{
	    background:url('theme/image/index_background_f<?php echo intval(((date('m')%12)/3)); ?>.jpg') no-repeat center center fixed; 
		background-repeat: no-repeat;
        background-position:top;
        background-size: cover; 
      }
	  
	  .date-picker-wrapper{
		width:100%;  
	  }
	  .month-wrapper{
		display:flex;  
		flex-wrap:wrap;
	  }
	  .date-picker-wrapper > .month-wrapper > table,.date-picker-wrapper > .month-wrapper > table.month2{
		width:47%;  
	  }
	  
	</style>
	
	<data id='area_apply_date_picker_config' data-start='<?php echo $start_date;?>' data-config='<?php echo $picker_config;?>' ></data>
	<data id='area_apply_time_picker_config' data-open='<?php echo $apply_area['time_open'];?>' data-close='<?php echo $apply_area['time_close'];?>' ></data>
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
		  <li class='action checked'> 首頁 / 申請進入<?php echo $apply_area['area_type'];?>: </li>
		  
		  <li class='step currency' data-section='agrement_checker' status='_AGREMENT' >  
			<h2>
			  <span class='name' >同意聲明</span>
			  <span class='status'></span>
			</h2>
			<i></i>
		  </li>
		  <li class='step' data-section='applicant_form' no=''  status='_INITIAL'>  
			<h2>
			  <span class='name' >申請人註冊</span>
			  <span class='status'></span>
			</h2>
			<div class='info'> </div>
		  </li>
		  <li class='step ' data-section='booking_form' no=''   status='_FORM'>  
			<h2>
			  <span class='name' >申請資料</span>
			  <span class='status'></span>
			</h2>
			<i></i>
		  </li>
		  <li class='step ' data-section='member_form' status='_MEMBER' >  
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
		  
		  <li class='step' data-section='submit_status' status='_SUBMIT' id='' >  
			<h2>
			  <span class='name' >遞交申請</span>
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
		    <li>&#187;申請成員表單 : <a href='docs/保護留區申請進入名單表格.ods' target='_blank' >ods</a> , <a href='docs/保護留區申請進入名單表格.xlsx' target='_blank' >xlsx</a> </li>
			<li>&#187;<a href='http://law.moj.gov.tw/LawClass/LawAllIf.aspx?PCode=M0040032' target='_blank'>自然保留區法規</a></li>
		    <li>&#187;<a href='http://law.moj.gov.tw/LawClass/LawAllIf.aspx?PCode=M0040036' target='_blank'>自然保護區法規</a></li>
			<li>&#187;<a href='http://law.moj.gov.tw/LawClass/LawAllIf.aspx?PCode=M0120001' target='_blank'>野生動物保護區法規</a></li>
		  </ul>
		</aside>
		
	    <div class='workaround_area' >
		  
		  <!-- 同意申明  -->
		  <div class='booking_step' id='agrement_checker' style='display:block;' >
			<h1>
			  <span class='step_title'>申請步驟 1 - 閱讀並同意申請規範 </span>
			  <span class='step_option'></span>
			</h1>
			
			<section>
			  <?php if($apply_area['area_type']=='自然保留區' && isset($page_content['自然保留區申請規範'])):?>
			  <div class="declaration" >
			    <?php echo $page_content['自然保留區申請規範']; ?>
			  </div>
			  <?php elseif($apply_area['area_type']=='自然保護區' && isset($page_content['自然保護區申請規範'])):?>
			  <div class="declaration" >
			    <?php echo $page_content['自然保護區申請規範']; ?>
			  </div>
			  <?php elseif($apply_area['area_type']=='動物保護區' && isset($page_content['動物保護區申請規範'])):?>
			  <div class="declaration" >
			    <?php echo $page_content['動物保護區申請規範']; ?>
			  </div>
			  <?php else:?>
			  <div class="declaration" >
			    請遵守申請進入規範，相關規定請洽各區域管理單位
			  </div>
			  <?php endif; ?>
			  <div class='agrement'>
			    <input id='apply_agrement' type='checkbox'> 以上說明，本人業已完全明瞭，並同意確實遵守相關規定。 
			  </div>
			  
			</section> 
			
		  </div>
		  
		  <!-- 資料填寫 -->
		  <div class='booking_step' id='applicant_form' >
		    <h1>
			  <span class='step_title'>申請步驟 2 - 填寫申請人資料 </span>
			  <span class='step_option'>
			    <button type='button' class='active' id='apply_step_02' > 下一步 </button>
			  </span>
			</h1>
			
			<section>	
			  <div class='applicant_record'>	
				<div class='applicant'>
					<div  class='form_element _nessary'>
					  <label>申請人姓名</label>
					  <div>
						<input type='text' class='apply_data chief' id='applicant_name'  placeholder="證件姓名">
						<i class='warning'></i>
					  </div>
					</div>
					<div  class='form_element _nessary'>
					  <label>身分證字號 (外國人請填寫護照號碼 )</label>
					  <div>
						<input type='text' class='apply_data chief' id='applicant_userid' placeholder="身分證字號或護照號碼">
						<i class='warning'></i>
					  </div>
					</div>				
					<div  class='form_element _nessary'>
					  <label>申請人電子郵件</label>
					  <div>
						<input type='email' class='apply_data chief' id='applicant_mail' placeholder="電子郵件信箱">
						<i class='warning'></i>
					  </div>
					</div>
				</div>
				<div class='history'>
				  <h2>申請紀錄</h2>
				  <ul class='apply_record'></ul>
				</div>
			  </div>	
				
			  <?php if($apply_area['area_type']=='自然保護區'):?>
			  <h2> 申請進入自然保護區需提供以下資訊：</h2>
			  <div  class='form_element'>
				  <label>出生年月日</label>
				  <div><input type='text' class='apply_data' id='applicant_birthday' placeholder="範例:1951-01-01"><i class='warning'></i></div>
			  </div>
			  <div  class='form_element'>
				  <label>服務單位</label>
				  <div><input type='text' class='apply_data' id='applicant_serviceto' placeholder=""><i class='warning'></i></div>
			  </div>
			  <div  class='form_element'>
				  <label>聯繫市話</label>
				  <div><input type='text' class='apply_data' id='applicant_phonenumber' placeholder=""><i class='warning'></i></div>
			  </div>
			  <div  class='form_element'>
				  <label>聯繫手機</label>
				  <div><input type='text' class='apply_data' id='applicant_cellphone' placeholder=""><i class='warning'></i></div>
			  </div>
			  <div  class='form_element'>
				  <label>傳真號碼</label>
				  <div><input type='text' class='apply_data' id='applicant_faxnumber' placeholder=""><i class='warning'></i></div>
			  </div>
			  <div  class='form_element'>
				  <label>通訊地址 <i>需填寫郵遞區號</i></label>
				  <div><input type='text' class='apply_data' id='applicant_mailaddress' placeholder=""><i class='warning'></i></div>
			  </div>
			  <div  class='form_element'>
				  <label>戶籍地址 <i>需填寫郵遞區號</i> <a class='option' id='copy_mailaddress' >複製通訊地址</a> </label>
				  <div><input type='text' class='apply_data' id='applicant_regaddress' placeholder=""><i class='warning'></i></div>
			  </div>
			  <?php endif; ?>
			</section>
			<h1>
			  <span class=''> </span>
			  <span class='step_option'>
			    <button type='button' class='cancel apply_cancel'   > 取消申請 </button>
			  </span>
			</h1>
		  </div> <!-- end of applicant data -->
		  
		  
		  <!-- 選擇區域 -->
		  <div class='booking_step' id='booking_form' >
		    
			<h1>
			  <span class='step_title'>申請步驟 3 - 選擇申請區域、目的與時間與相關資訊 </span>
			  <span class='step_option'>
			    <button type='button' class='active apply_step_03' id='apply_step_03' > 下一步 </button>
			  </span>
			</h1>
			<section>  
				
				<h2> 1. 選擇申請區域與進入範圍 </h2>
				
				<div class='form_element'>
				  <label>申請區域 - <i><?php echo $apply_area['area_type'];?></i></label>
				  <div>
					<select  class='apply_data' id='apply_area' >
					  <option value='' disabled selected>請選擇申請區域</option>
					  <?php foreach($area_list as $list): ?>  
					  <?php   if($list['area_type']==$apply_area['area_type'] && $apply_area['area_code']==$list['area_code']):?>
					  <option value="<?php echo $list['area_code'];?>"  <?php echo $apply_area['area_code']==$list['area_code'] ? 'selected':''; ?>     > <?php echo $list['area_name']; ?> </option>
					  <?php   endif; ?>
					  <?php endforeach; ?>  	
					</select>
				  </div>
				</div>
				
				
				<div class='form_element'>
				  <label>進入範圍或地點</label>
				  <div>
				    <ul class='subarea  '>
					<?php if(count($apply_area['sub_block'])):?>
					  <?php foreach($apply_area['sub_block'] as $bid => $block): ?>  
					  <li class='apply_subarea' data-subarea='<?php echo $block['name']; ?>'   >
					    <input type='checkbox' class='apply_data' name='inter_area' value='<?php echo $block['name']; ?>' />
						<?php echo $block['name']; ?>
						<i><?php echo $block['desc']; ?></i>
				      </li>
					  <?php endforeach; ?>
					  <li class='apply_subarea' data-subarea='other'  >
					    <input type='text' class='apply_data' name='inter_area' id='inter_area_other' placeholder='其他未列出之範圍或備註'/>
					  </li>
					<?php else: ?>
					  <li class='apply_subarea' data-subarea='other'  >
					    <input type='text' class='apply_data' name='inter_area' id='inter_area_other' placeholder='請填寫進入範圍'/>
					  </li>
					<?php endif; ?>
					</ul>
				  </div>  
				</div>
				
				<h2> 2. 選擇進出入口與預計抵達時間 </h2>
				<div class='form_group' >
				  <div  class='form_element'>
					<label>進入入口</label>
					<div class='assist_block'>
					  <input type='text' class='apply_data' id='area_gate_entr' placeholder='請選擇預計進入入口或自行填寫' >
					</div>
				  </div>
				  <div  class='form_element'>
					<label>預計抵達時間</label>
					<div><input type='time' class='apply_data apply_time' id='area_gate_entr_time' value='<?php echo $apply_area['time_open'];?>' ></div> 
				  </div>
				</div>
				<div class='form_group'>
				  <div  class='form_element'>
					<label>離開出口</label>
					<div class='assist_block'>
					  <input type='text' class='apply_data' id='area_gate_exit' placeholder='請選擇預計離開入口或自行填寫'>
					</div>
				  </div>
				  <div  class='form_element'>
					<label>預計抵達時間</label>
					<div><input type='time' class='apply_data apply_time' id='area_gate_exit_time' value='<?php echo $apply_area['time_close'];?>'  ></div> 
				  </div>
				</div>	
				
				<?php if($apply_form['application_reason']): ?>
				<h2> 3. 申請目的或項目 <?php if($apply_form['application_reason']['config']['input']=='checkbox'):?>(可複選)<?php endif; ?></h2>
				<div class='reason_set'>
				  <div class='form_element'>
				    <?php foreach($apply_form['application_reason']['elements'] as $input ):   ?>
				    <div class='option_set'>
					  <input type='<?php echo $apply_form['application_reason']['config']['input'];?>' 
					         class='apply_data apply_reason' 
							 name='apply_reason' 
							 value='<?php echo $input['name']; ?>' 
							 attach='<?php echo strstr($input['conf'],'attach') ? 1:0; ?>'   
					         crossday='<?php echo strstr($input['conf'],'crossday') ? 1:0; ?>' 
                             limit='<?php echo strstr($input['conf'],'limit') ? 1:0; ?>'   							 
					  />  
					  <span><?php echo $input['name']; ?></span>
					  <?php if(isset($input['note']) && $input['note']): ?>
					  <i><?php echo str_replace(';','</i><i>',$input['note']);?></i>
					  <?php endif; ?>
					</div> 
				    <?php endforeach; ?>
					<div class='option_set other_option'>
					  <span>其他申請事項:</span><input type='text' class='apply_data' id='apply_reason_other' name='apply_reason' value='' placeholder='請填寫其他申請事項內容' >
					</div>
				  </div>
				  <div  class='form_element ' id='apply_documents' style=''>
					<label >請上傳所需文件<i> - 限PDF或影像掃描檔案</i></label>
					<form id='apply_attachment_upload_form' action="index.php?act=Landing/uplath/" method="post" enctype="multipart/form-data" target="upload_target" >
					  <input name="file" type="file"  id='apply_attachment_upload' >
					</form>
					
					<label style='margin-top:5px;'>已上傳附件清單:</label>
					<ul id='attachment_list' ></ul>
					  
				  </div>
				</div>
				<?php unset($apply_form['application_reason']); ?>
				<?php endif; ?>
				
				
				<h2> 4. 選擇進入日期 </h2>
				<div class=''>
				  <div  class='form_element'>
				    <label>勾選申請日期範圍</label>
				    <div class='value_set apply_dates'>
					  <input type='text' class='apply_data apply_date' id='apply_date_1s' placeholder='例:<?php echo date('Y-m-d');?>' >
					  <input type='text' class='apply_data apply_date' id='apply_date_1e' >
					  <i class='warning'></i>
					</div>
				  </div>
			      <div class='dates_apply' id='date-range12-container' data-startDate='' ></div>
				</div>
				
				<?php if(count($apply_form)): ?>
				<?php   foreach($apply_form as $class => $fields ):   ?>
				<h2>5. <?php echo $class; ?></h2>
				<?php     foreach($fields as $fid=>$fconf): ?>
				<div  class='form_element other_form'>
				  <label><?php echo $fconf['label']; ?></label>
				  <div>
				    <?php if($fconf['input']=='textarea'): ?>
				    <textarea class='apply_data' id='<?php echo $fid; ?>' ><?php echo $fconf['value']; ?></textarea>
				    <?php else: ?> 
				    <input type="text" class='apply_data' id='<?php echo $fid; ?>' value='<?php echo $fconf['value']; ?>' />
					<?php endif; ?>
				  </div> 
				  <i><?php echo nl2br($fconf['notes']); ?></i>
				</div> 
				<?php     endforeach; ?>
				<?php   endforeach; ?>
				<?php endif; ?>
				
				
				<?php if($apply_area['area_gates']): ?>
				<ul class='input_assist' id='area_gates_assist' >
				  <?php foreach(explode(';',$apply_area['area_gates']) as $gate): ?>  
				  <li class='get_selecter' name='<?php echo $gate;?>' ><?php echo $apply_area['area_name'];?> - <?php echo $gate;?></li>
				  <?php endforeach; ?>
				  <?php foreach($apply_area['sub_block'] as $bid => $block): ?>  
				  <?php   if(!count($block['gate'])) continue; ?>
				  <?php   foreach($block['gate'] as $gate): ?>  
			      <li class='get_selecter' block='<?php echo $block['name']?>' name='<?php echo $block['name'].' - '.$gate;?>'  >&nbsp;&nbsp;<?php echo $block['name'].' - '.$gate; ?></li>
				  <?php   endforeach; ?>
				  <?php endforeach; ?>
				</ul>
			    <?php endif; ?>
				
					
			</section>
			<h1>
			  <span class=''> </span>
			  <span class='step_option'>
			    <button type='button' class='cancel apply_cancel'   > 取消申請 </button>
			    <button type='button' class='active apply_step_03'  > 下一步 </button>
			  </span>
			</h1>
		  </div>
		  
		 <!-- 成員名單 -->
		  <div class='booking_step' id='member_form' >
		    
			<h1>
			  <span class='step_title'>申請步驟 4 - 填寫進入成員名單 </span>
			  <span class='step_option'>
			    <button type='button' class='active' id='apply_step_04' ischeck=0 > 確認成員 </button>
			    <button type='button' class='active' id='apply_submit'  disabled=true  > 遞交申請 </button>
			  </span>
			</h1>
			<section>  
				<h2>6. 進入人員名冊 ( 下載名冊檔案 : <a class='option act_member_list_file' data-format='ods' >ods</a>, <a class='option act_member_list_file' data-format='xlsx' >xls</a> )</h2>
				<div  class='form_element'>
				  <form id='apply_member_upload_form' action="index.php?act=Landing/uplmbr/" method="post" enctype="multipart/form-data" target="upload_target" >
                    <input id='apply_member_upload' name="file" type="file" />
                  </form> 
				  <i><span>請上傳申請成員表單，或利用下方編輯</span></i>
				</div>
				<div  class='form_element'>
				  <table class='member-list'>
				    <!-- 
					<br>(外籍人士請填護照號碼)</td><td>出生年月日</td><td>通訊地址</td><td>聯絡電話</td><td>緊急連絡人</td><td>緊急連絡電話
					-->
				  
					<tr class='fields'><td>NO.</td><td>角色</td><td>成員資料 (所有欄位都需填寫)</td><td>編輯</td></tr>
				    <tbody class='apply_data' id='apply_member_list' pnum='' ></tbody>
					<tr class='fields'><td>NO.</td><td>角色</td><td>成員資料</td><td><a class='option' id='act_add_member' ><i class="fa fa-user-plus" aria-hidden="true"></i> 新增</a></td></tr>
					<tr class='member template' edit=0 save=0 >
					    <td ><a class='option mdele'><i class="fa fa-trash-o" aria-hidden="true"></i></a> <span class='mbr_no'></span>.</td>
						<td class='mbr_role' >role</td>
						<td class='mbr_info' >
						  <div><label>姓名</label><span class='mbr_data' ><input type='text' class='member_name' /></span><label>證件號碼</label><span class='mbr_data'><input type='text' class='member_id' placeholder='身分證號或護照號碼' /></span></div>
						  <div><label>生日</label><span class='mbr_data'><input type='text' class='member_birth' /></span><label>性別</label><span class='mbr_data'><input type='radio' class='member_sex' value='男' />男 <input type='radio' class='member_sex' value='女' />女 </span></div>
						  <div><label>通訊電話</label><span class='mbr_data'><input type='text' class='member_tel' placeholder='電話含區碼' /></span class='mbr_data'><label>行動電話</label><span class='mbr_data'><input type='text' class='member_cell' placeholder='行動電話' /></span></div>
						  <div><label>通訊地址</label><span class='mbr_data long_input'><input type='text' class='member_addr' /></span></div>
						  <div><label>服務單位</label><span class='mbr_data long_input'><input type='text' class='member_org' /></span><i></i></div>
						  <div><label>緊急聯絡人</label><span class='mbr_data'><input type='text' placeholder='緊急聯絡人' class='member_contacter' /></span> <label>聯絡人電話</label><span class='mbr_data'><input type='text' placeholder='聯絡人電話' class='member_contactto' /></span></div>
						</td>
						<td >
						  <a class='option msave' ><i class="fa fa-minus-square-o" aria-hidden="true"></i></a>
						  <a class='option medit' ><i class="fa fa-plus-square-o" aria-hidden="true"></i></a>
						</td>
					</tr>			
				  </table>
				</div>
					
			</section>
			<h1>
			  <span class=''> </span>
			  <span class='step_option'>
			    <button type='button' class='cancel apply_cancel'   > 取消申請 </button>
			  </span>
			</h1>
		  </div>
		  
		  
		  
		  
		  
		  <!-- 審核狀態 -->
		  <div class='booking_step' id='submit_status'>
		    <h1>
			  <span class='step_title'>申請步驟 5 - 申請資料已發送給主管機關，請靜候審核 </span>
			  <span class='step_option'></span>
			</h1>
			
			<section>
			    <h2> 申請進度與狀態 </h2>
				<table id='apply_process_table'>
				  <tr class='process_header' >
				    <td >初始階段</td>
					<td >抽籤階段</td>
					<td >審查階段</td>
					<td >等待階段</td>
					<td >最後階段</td>
				  </tr>
				  
				  <tr class='process_task' id='client' > 
				    <td class='stage1 _variable ' > </td>
				    <td class='stage2 _variable ' > </td>
				    <td class='stage3 _variable ' > </td>
				    <td class='stage4 _variable ' > </td>
					<td class='stage5 _variable ' > </td>
				  </tr>
				  
				  <tr class='process_task' id='review' > 
				    <td class='stage1 ' ><button id='act_apply_toreview' >申請資料異動</button></td>
				    <td class='stage2 _variable' > </td>
				    <td class='stage3 ' ><button id='act_apply_toreview' >更新申請資料</button></td>
				    <td class='stage4 _variable' > </td>
					<td class='stage5' ><button id='act_apply_toreview' >申請取消</button></td>
				  </tr>
				</table>
				
			    <h2> 申請資料預覽 </h2>
				<div class='license' id='license_preview'>
				</div>
			
			</section>
			
			
			 
			
		  </div>
		  <iframe id="upload_target" name="upload_target" src="loader.php" style='display:none;'></iframe>	
		
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
