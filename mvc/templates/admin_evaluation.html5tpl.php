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
	<link rel="stylesheet" type="text/css" href="theme/css/css_evaluation_admin.css?<?php echo time();?>" />
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_evaluation_admin.js?<?php echo time();?>"></script>
	
	
	<!-- PHP -->
	<?php
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	$page_info 		= isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';  
	
	$area_list  	= isset($this->vars['server']['data']['areas']) 	? $this->vars['server']['data']['areas'] : array();  
	$data_list  	= isset($this->vars['server']['data']['records']) 	? $this->vars['server']['data']['records'] : array();  
	
	//echo "<pre>";
	//var_dump($user_info );  
	//exit(1);
	 
	?>
  </head>
  
  
  <body>
	<div class='system_main_area'>
	  <div class='system_manual_area'>
	  <?php include('area_admin_manual.php'); ?>
	  </div>
	  <div class='system_content_area wide_mode'>
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
		  <h2>保護區經營管理效能評量：<span id='record_id' no='' >新增評量資料</span></h2> 
		  <ul class='section_switch' >
 		    <li class='session active'  dom='questionnaire_information' > 填答者基本資料 </li>
			<li class='session ' 		dom='questionnaire_mettdata' 	> (一) 保護區資料表 </li>
			<li class='session '		dom='questionnaire_tendency' 	> (二) 保護區的壓力表 </li>
			<li class='session '		dom='questionnaire_evaluate' 	> (三) 經營管理評量表 </li>
		  </ul>
		  <div class='lunch_option'> 
		    <button type='button' class='active' id='act_download_evaluation' ><i class="fa fa-download" aria-hidden="true"></i> 下載評量</button>
			<button type='button' class='active' id='act_submit_evaluation' ><i class="fa fa-paper-plane-o" aria-hidden="true"></i> 遞交評量</button>
			<button type='button' class='active' id='act_get_record' ><i class="fa fa-repeat" aria-hidden="true"></i> 重新整理</button>
			
		  </div> 
		</div>
		
		<div class='main_content' >
		  
		  <!-- 資料列表區 -->
		  <div class='data_record_block' id='questionnaire_information' data-table=''>
		    <div class='record_header'>
			  <span class='record_name'>基本資料</span> 
			  <span class='record_option'>
			    <label>參考評量：</label>
				<select id='evaluate_history'>
				  <option value=''>參考最新一筆資料</option>
				  <optgroup label='歷年評量'>
				  <?php foreach($data_list  as $meta): ?>  
				    <?php if(!$meta['_active']) continue;?>
				    <option value='<?php echo $meta['record_id'];?>' area='<?php echo $meta['record_area'];?>'> <?php echo $meta['record_year'];?> 年度 , <?php echo $meta['record_area'];?> </option>
				  <?php endforeach; ?>
				  </optgroup>
				</select>
				<button type='button' class='active' id='act_create_record' style='padding:5px 3px;'><i class="fa fa-quora" aria-hidden="true"></i> 開始評量</button>
			    <!--<button type='button' class='cancel' id='act_test_record'> 讀取測試資料 </button>-->
			  </span> 
			  
			</div>  
			
			<div class='record_body' id=''>
			
			  <div class='form_area'> 
			     
				<div class='data_col _flat' id='' style='margin-top:15px;'> 
				  <label >1. 填寫姓名</label>
				  <div class='data_value'>   
					<input type='text'  class=' _update ' name="user_name" id='user_name'  placeholder='填寫姓名' value='<?php echo $user_info['user']['user_name'];?>'  />
				  </div>
				</div>

				<div class='data_col _flat' id='' > 
				  <label >2. 所屬單位</label>
				  <div class='data_value'>   
					<input type='text'  class=' _update ' name="user_organ" id='user_organ'  placeholder='所屬單位 / 機構' value='<?php echo $user_info['user']['user_organ'];?>'  />
				  </div>
				</div>
				
				<div class='data_col _flat' id='' > 
				  <label >3. 職稱</label>
				  <div class='data_value'>   
					<input type='text'  class=' _update ' name="user_title" id='user_title'  placeholder='職稱' value='<?php echo $user_info['user']['user_staff'];?>'  />
				  </div>
				</div>
				
				<div class='data_col _flat' id='' > 
				  <label >4. 連絡電話</label>
				  <div class='data_value'>   
					<input type='text'  class=' _update ' name="user_tel" id='user_tel'  placeholder='連絡電話' value='<?php echo $user_info['user']['user_tel'];?>'  />
				  </div>
				</div>
				<div class='data_col _flat' id='' > 
				  <label >5. 聯繫email</label>
				  <div class='data_value'>   
					<input type='text'  class=' _update ' name="user_mail" id='user_mail'  placeholder='聯繫email' value='<?php echo $user_info['user']['user_mail'];?>'  />
				  </div>
				</div>
				<div class='data_col _flat' id='' > 
				  <label >6. 填寫日期</label>
				  <div class='data_value'>   
					<input type='text'  class=' _update ' name="record_year" id='record_year'  placeholder='' value='<?php echo date('Y-m-d');?>'  />
				  </div>
				</div>
				<div class='data_col _flat' id='' > 
				  <label >7. 保護區名稱</label>
				  <div class='data_value'>   
				    <select class=' _update ' name="record_area" id='record_area' >
					  <option value='' disabled selected >請選擇評量區域</option>
					  <?php foreach($area_list as $num => $data): ?>  
					  <option value='<?php echo $data['area_name']; ?>'><?php echo $data['area_name']; ?></option>
					  <?php endforeach; ?>
					</select> 
				  </div>
				</div> 
              </div>
			  <div class='guide_area' id='mettrecords' >
				<h1 >填寫紀錄</h1>
                <ol class='user_record_list'>  
                <?php foreach($data_list  as $meta): ?>  
				  <li >
				    <span class='ryear'> <a class='option act_evaluate_delete' no='<?php echo $meta['record_id'];?>'  ><i class="fa fa-trash" aria-hidden="true"></i></a>  </span>
				    <span class='ryear'> <?php echo $meta['record_year'];?> 年度  </span>
				    <span class='rarea'> <?php echo $meta['record_area'];?>  </span>
				    <span class='ruser' title='於 <?php echo substr($meta['_time_create'],0,10);?>'> <?php echo $meta['_user_create'];?>  填寫  </span>
					<span class='ractive'> 
					<?php if(!$meta['_active']): ?>   
					  / <i title='<?php echo $meta['_time_create'];?>'>填寫中</i> /
					<?php else: ?>
					  / <i title='<?php echo $meta['_time_finish'];?>'>已遞交</i> /
					<?php endif; ?>
					</span>
					<span class='ractive' title='完成時間：<?php echo $meta['_time_finish'];?>' > 
					  <?php if($user_info['signin']==$meta['_user_create']):?>
					  <button type='button' class='active act_evaluate_continue' no='<?php echo $meta['record_id'];?>' >  <?php echo $meta['_active'] ? '修改' : '繼續'; ?>  </button>
					  <?php else: ?>
					  <i ><?php echo $meta['_active'] ? "完成" : "...." ?></i>
					  <?php endif; ?>
					</span>
					 
				  </li> 
				<?php endforeach; ?>
				</ol> 
			  </div>	
			</div>
		  </div>
		  <!-- end of session -->
		  
		  <!-- session 2 -->
		  <div class='data_record_block' id='questionnaire_mettdata' data-table='evaluation_mettdata'>
		    <div class='record_header'>
			  <span class='record_name'>(一) 保護區資料表  </span> 
			  <span class='record_name'> / 前次評量：<span class='history_record'></span> / <button type='button' class='active' id='act_import_lastone'>  帶入上次資料 </button></span> 
			  
			  <span class='record_option'></span> 
			</div> 
		    <div class='record_body' id=''>  
			  
			  <div class='form_area'>
				<div class='data_col _hashentry' id='block-emd01' > 
				  <label > 1. 設立日期</label>
				  <div class='data_value'>   
					<input type='text'  class='_variable _update ' name="emd01" id='emd01'  placeholder='西元OOOO年OO月OO日' value=''  />
				  </div>
				</div>
			    
				<div class='data_col _hashentry' id='block-emd02' > 
				  <label > 2. 法源依據</label>
				  <div class='data_value'>   
					<input type='text'  class='_variable _update _wide' name="emd02" id='emd02'  placeholder='' value=''  />
				  </div>
				</div>
			    
				<div class='data_col _hashentry' id='block-emd03' > 
				  <label > 3. 五年內保護區法源是否更動？</label>
				  <div class='data_value'>  
                    <input type='radio' name='emd0301' class='_variable _update ' value=0  bind='#emd0302' effect='disabled'	>否
					<input type='radio' name='emd0301' class='_variable _update ' value=1  bind='#emd0302' effect='editable'	>是，
				   	變動情形： <input type='text'  class='_variable _update ' name="emd0302" id='emd0302'  placeholder='如有變動請說明' value='' check='emd0301' effect='available' disabled />
				  </div>
				</div>
				
			    <div class='data_col _hashentry' id='block-emd04' > 
				  <label > 4. 職權範圍與保護區重疊、或與保護區經營管理運作有競合關係的法規</label>
				  <div class='data_value'>  
                    <ul class='mutiselecter'>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='國家公園法' 	bind='#emd04oth' effect='bundle'	/>國家公園法</li>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='濕地保育法' 		bind='#emd04oth' effect='bundle'	/>濕地保育法</li>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='海岸法' 		bind='#emd04oth' effect='bundle'	/>海岸法</li>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='水利法' 		bind='#emd04oth' effect='bundle'	/>水利法</li>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='水土保持法' 	bind='#emd04oth' effect='bundle'	/>水土保持法</li>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='山坡地保育利用條例' bind='#emd04oth' effect='bundle'	/>山坡地保育利用條例</li>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='森林法' 		bind='#emd04oth' effect='bundle'	/>森林法</li>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='觀光發展條例'	bind='#emd04oth' effect='bundle' 	/>觀光發展條例</li>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='漁業法' 		bind='#emd04oth' effect='bundle'	/>漁業法</li>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='都市計畫法' 	bind='#emd04oth' effect='bundle'	/>都市計畫法</li>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='要塞堡壘地帶法' bind='#emd04oth' effect='bundle'	/>要塞堡壘地帶法</li>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='礦業法' 		bind='#emd04oth' effect='bundle'	/>礦業法</li>
					  <li><input type='checkbox' name='emd04' class='_variable _update ' value='原住民族基本法' bind='#emd04oth' effect='bundle'	/>原住民族基本法</li>
			          <li class='other'>其他：<input type='text'  class='_variable _update _expend'  partof='emd04'   name="emd04oth" id='emd04oth'  placeholder='自行新增，請以分號隔開' value=''  /></li>
					</ul> 
				  </div>
				</div>  
				  
				<div class='data_col _hashentry' id='block-emd05' > 
					<label > 5. 保護區大小(公頃)</label>
					<div class='data_value'>  
						陸域：<input type='text'  class='_variable _update ' name="emd0501" id='emd0501'  placeholder='數字' value='' size='10' />公頃 
					</div>
					<div class='data_value'>  
					    海域：<input type='text'  class='_variable _update ' name="emd0502" id='emd0502'  placeholder='數字' value='' size='10' />公頃
					</div>
				</div>
				
                <div class='data_col _hashentry' id='block-emd06' > 
				  <label >6. 五年內保護區大小是否有產生變動？</label>
				  <div class='data_value'>  
                    <input type='radio' name='emd0601' class='_variable _update ' value=0  bind='#emd0602' effect='disabled'	>否
					<input type='radio' name='emd0601' class='_variable _update ' value=1  bind='#emd0602' effect='editable'			>是，
					變動情形： 
					<input type='text'  class='_variable _update ' name="emd0602" id='emd0602'  placeholder='如有變動請說明' value=''  disabled  />
				  </div>
				</div>  				

                <div class='data_col _hashentry' id='block-emd07' > 
				  <label >7. 五年內產權是否有產生變動？</label>
				  <div class='data_value'>  
                    <input type='radio' name='emd0701' class='_variable _update ' value=0  bind='#emd0702' effect='disabled'	>否
					<input type='radio' name='emd0701' class='_variable _update ' value=1  bind='#emd0702' effect='editable'	>是，
					變動情形： 
					<input type='text'  class='_variable _update ' name="emd0702" id='emd0702'  placeholder='如有變動請說明' value='' disabled />
				  </div>
				</div>  
                
				<div class='data_col _hashentry' id='block-emd08' > 
					<label > 8. 產權所有者：(可自行新增 <a class='option act_add_value' title='新增'><i class='fa fa-plus'></i></a>)</label>
					<div class='data_value'>  
						<ul class='increase_form _update' name='emd0800' id='emd0800'  >
						  <li class='listrecord pattern ' id='' >
						    <select  class='_variable _update _element' partof='#emd0800' name="emd0800_type" > 
							  <option value='' disabled selected	> 請選擇類別 </option>
						      <option value='其他公有'	> 其他公有 </option>
							  <option value='私有'		> 私有 </option>
							  <option value='使用權或其他' > 使用權或其他 </option>
							</select>
							<input type='text'  class='_variable _update _element' partof='#emd0800' name="emd0800_name"		id=''  placeholder='請填寫所有者名稱' value=''  />：
							產權面積=
							<input type='text'  class='_variable _update _element' partof='#emd0800' name="emd0800_area"		id=''  placeholder='數值' value='' size='10' />公頃，
							占比=
							<input type='text'  class='_variable _update _element' partof='#emd0800' name="emd0800_proportion"	id=''  placeholder=''	value=''  readonly size='5' />%  
						    ，
							<a class='option act_remove_element' ><i class="fa fa-trash" aria-hidden="true"></i></a>
						  </li>
						  <li class='listrecord default' id='emd0800owner' >
						    <select class='_variable _update _element' partof='#emd0800' name="emd0800_type"  > 
						      <option value='主管機關所有' selected>主管機關所有</option>
							</select>
							<input type='text'  class='_variable _update _element' partof='#emd0800' name="emd0800_name" 		id=''  placeholder='請填寫主管機關' value=''  />：
							產權面積=
							<input type='text'  class='_variable _update _element' partof='#emd0800' name="emd0800_area" 		id=''  placeholder='數值' value='' size='10'  />公頃，
							占比=
							<input type='text'  class='_variable _update _element' partof='#emd0800' name="emd0800_proportion" id=''  placeholder='' value='' readonly size='5' />% 
						  </li>
						  <li class='listrecord default' id='emd0800other' >
						    <select class='_variable _update _element' partof='#emd0800' name="emd0800_type"> 
						      <option value='其他公有'	> 其他公有 </option>
							  <option value='私有'		> 私有 </option>
							  <option value='使用權或其他' > 使用權或其他 </option>
							</select>
							<input type='text'  class='_variable _update _element' partof='#emd0800' name="emd0800_name"		id=''  placeholder='請填寫所有者名稱' value=''  />：
							產權面積=
							<input type='text'  class='_variable _update _element' partof='#emd0800' name="emd0800_area"		id=''  placeholder='數值' value='' size='10' />公頃，
							占比=
							<input type='text'  class='_variable _update _element' partof='#emd0800' name="emd0800_proportion"	id=''  placeholder='' value=''  readonly size='5' />%  
						  </li>
						</ul>
					</div>
				</div>
				
				<div class='data_col _hashentry' id='block-emd09' > 
				  <label > 9. 管理機關：</label>
				  <div class='data_value'>   
					主管機關名稱：
					<input type='text'  class='_variable _update ' name="emd0901" id='emd0901'  placeholder='' value=''  /> 				 
				    <i> - 請填寫公告的機關名稱（含承辦單位）</i>
				  </div>
				  
				  <div class='data_value'>   
					經營管理機關：
					<input type='text'  class='_variable _update ' name="emd0902" id='emd0902'  placeholder='' value=''  />
				    <i> - 請填寫實際經營管理機關名稱（含承辦單位）</i>
				  </div>
				  
				  <div class='data_value'>   
					其他管理機關：
					<input type='text'  class='_variable _update ' name="emd0903" id='emd0903'  placeholder='' value=''  />
				    <i> - 請填寫其他管理機關(備註)</i>
				  </div>
				</div>
				
				 
			    
				<div class='data_col _hashentry' id='block-emd10' > 
					<label >
					  10. 人力資源：(可自行新增<a class='option act_add_value' title='新增'><i class='fa fa-plus'></i></a>)
					</label>
					<div class='data_value'>  
						<ul class='increase_form _update' name='emd1000' id='emd1000'>
						  <li class='listrecord pattern ' id=''>
						    <select name='emd1000_type'  class='_variable _update _element' partof='#emd1000'> 
							  <option value='' disabled selected>人力類型</option>
						      <option value='編制內'	>編制內</option>
							  <option value='約聘僱'	>約聘僱</option>
							  <option value='臨時'		>臨時</option>
							  <option value='外包'		>外包</option>
							</select>
							/ <input type='text'  class='_variable _update _element' partof='#emd1000'		name="emd1000_number" id=''  placeholder='人次' value='' size='5' /> 人
							/ 工作比重=<input type='text'  class='_variable _update _element' partof='#emd1000' name="emd1000_loading" id=''  placeholder='百分比' value=''  size='5' />%
						  　，
							<a class='option act_remove_element' ><i class="fa fa-trash" aria-hidden="true"></i></a>　
						  </li>
						  <li class='listrecord default ' id='emd1000default'>
						    <select name='emd1000_type' class='_variable _update _element' partof='#emd1000' > 
							  <option value='' disabled selected>人力類型</option>
						      <option value='編制內'	>編制內</option>
							  <option value='約聘僱'	>約聘僱</option>
							  <option value='臨時'		>臨時</option>
							  <option value='外包'		>外包</option>
							</select>
							/ <input type='text'  class='_variable _update _element' partof='#emd1000' name="emd1000_number" id=''  placeholder='人次' value='' size='5' /> 人
							/ 工作比重=<input type='text'  class='_variable _update _element' partof='#emd1000' name="emd1000_loading" id=''  placeholder='百分比' value=''  size='5' />%
						  </li>
						</ul>
					</div>
				</div>
				
				
				<div class='data_col _hashentry' id='block-emd11' > 
				  <label >11. 設立保護區的主要價值：<i>請依照EOH選項填寫（填入選單，例如：生物多樣性…等）</i></label>
				  
				  <div class='data_value'>
					<table>
					  <tr><td><input type='checkbox' name='emd110101' value='1' class='_variable _update ' bind='#emd110102' effect='switch'	/>生物多樣性價值</td><td>說明:<input type='text'  class='_variable _update ' name="emd110102" id='emd110102'  placeholder='' value=''  disabled /></td></tr>
                      <tr><td><input type='checkbox' name='emd110201' value='1'	class='_variable _update ' bind='#emd110202' effect='switch'	/>地景價值</td><td>說明:<input type='text'  class='_variable _update ' name="emd110202" id='emd110202'  placeholder='' value='' disabled  /></td></tr>
                      <tr><td><input type='checkbox' name='emd110301' value='1'	class='_variable _update ' bind='#emd110302' effect='switch'	/>文化價值</td><td>說明:<input type='text'  class='_variable _update ' name="emd110302" id='emd110302'  placeholder='' value='' disabled  /></td></tr>					  
					  <tr><td><input type='checkbox' name='emd110401' value='1'	class='_variable _update ' bind='#emd110402' effect='switch'	/>經濟價值</td><td>說明:<input type='text'  class='_variable _update ' name="emd110402" id='emd110402'  placeholder='' value='' disabled  /></td></tr>
					  <tr><td><input type='checkbox' name='emd110501' value='1'	class='_variable _update ' bind='#emd110502' effect='switch'	/>教育價值</td><td>說明:<input type='text'  class='_variable _update ' name="emd110502" id='emd110502'  placeholder='' value='' disabled  /></td></tr>
					  <tr><td><input type='checkbox' name='emd110601' value='1'	class='_variable _update ' bind='#emd110602' effect='switch'	/>其他社會價值</td><td>說明:<input type='text'  class='_variable _update ' name="emd110602" id='emd110602'  placeholder='' value='' disabled  /></td></tr>
			          <tr><td><input type='text'  class='_variable _update ' name="emd110701" id='emd110701'  placeholder='其他' value='' bind='#emd110702' effect='together'  /></td><td>說明:<input type='text'  class='_variable _update ' name="emd110702" id='emd110702'  placeholder='' value='' disabled /></td></tr>
					</table> 
				  </div>
				</div>


                <div class='data_col _hashentry' id='block-emd12' > 
				  <label >12. 保護區的保育目標</label>
				  <div class='data_value'>   
					<textarea type='text'  class='_variable _update _wide' name="emd12" id='emd12'  placeholder='請依照保育計畫書內容填寫' value=''  ></textarea>
				  </div>
				</div>	
				
				<div class='data_col _hashentry' id='block-emd13' > 
					<label >13. 本年度各單位投入經費：(可自行新增<a class='option act_add_value' title='新增'><i class='fa fa-plus'></i></a>)</label>
					<div class='data_value'>  
						<ul class='increase_form _update' name='emd1300' id='emd1300'>
						  <li class='listrecord pattern' id=''>
						    單位：<input type='text'  class='_variable _update _element' partof='#emd1300' name="emd1300_organ" 	id=''  placeholder='單位名稱'	value=''	size='20' />
						    金額：<input type='text'  class='_variable _update _element' partof='#emd1300' name="emd1300_funding"	id=''  placeholder='數值'		value=''	size='5' />(千元) 
						  　，
							<a class='option act_remove_element' ><i class="fa fa-trash" aria-hidden="true"></i></a>　
						  </li>
						  <li class='listrecord default ' id='emd1300default' >
						    單位：<input type='text'  class='_variable _update _element' partof='#emd1300' name="emd1300_organ"		id=''  placeholder='單位名稱'	value=''	size='20' />
						    金額：<input type='text'  class='_variable _update _element' partof='#emd1300' name="emd1300_funding"	id=''  placeholder='數值'		value=''	size='5' />(千元) 
						  　，
							<a class='option act_remove_element' ><i class="fa fa-trash" aria-hidden="true"></i></a>　
						  </li>
						</ul>
					</div>
				</div>
				
				
				<div class='data_col _hashentry' id='block-emd14' > 
				  <label >14. 請填寫本年度已執行結束與本保護區相關的計畫成果報告：(可自行新增 <a class='option act_add_tbrow' title='新增'><i class='fa fa-plus'></i></a>)</label>
				  <div class='data_value'>   
					<table class='record_list increase_table _update'  name='emd1400' id='emd1400'>
						<thead>
						<tr class='data_field'>
						  <td > <a class='option act_add_tbrow' title='新增'><i class='fa fa-plus'></i></a> </td>
						  <td title='計畫名稱' >計畫名稱</td>
						  <td title='執行期間' >執行期間</td>
						  <td title='經費' >經費</td>
						  <td title='發包單位' >發包單位</td>
						  <td title='承辦單位' >承辦單位</td>
						  <td title='結案報告書' >結案報告書&其他格式檔案</td>
						</tr>
						</thead>
						<tbody class='data_result' mode='list' ></tbody>
						<tfoot class='data_format' mode='list' > 
						    <tr  >
							  <td align=center  ><a class="option act_remove_tbrow"><i class="fa fa-trash" aria-hidden="true"></i></a></td>
							  <td title='計畫名稱' ><input type='text'  class='_variable _update _element' partof='#emd1400' name="title"		placeholder='' value=''  /></td>
							  <td title='執行期間' ><input type='text'  class='_variable _update _element' partof='#emd1400' name="year"		placeholder='' value=''   /></td>
							  <td title='經費' ><input type='text'  class='_variable _update _element' partof='#emd1400' 		name="budget"	placeholder='' value=''   /></td>
							  <td title='發包單位' ><input type='text'  class='_variable _update _element' partof='#emd1400' name="organ"		placeholder='' value=''  /></td>
							  <td title='承辦單位' ><input type='text'  class='_variable _update _element' partof='#emd1400' name="contractor"		placeholder='' value=''   /></td>
							  <td title='結案報告書&其他格式檔案' >
							    <ul class='upllist'></ul>
							    <input type='file'  class='_variable docupload' name="docupload"  placeholder='' value='' disabled  />
							  </td>
							</tr>   
						</tfoot>	
					</table>
					
				  </div>
				</div>
				
				<div class='data_col _hashentry' id='block-emd15' > 
					<label >15. 近五年年度總預算：<i>專職員工薪資不列入</i></label>
					<div class='data_value'>  
						<ul>
						  <li>
						    年度：<input type='text'  class='_variable _update ' name="emd1501" id='emd1501'  placeholder='年度' value='' size='5' />
						    金額：<input type='text'  class='_variable _update ' name="emd1502" id='emd1502'  placeholder='數值' value='' size='5' />(千元) 
						  </li>
						  <li>
						    年度：<input type='text'  class='_variable  _history' name="H-emd1501" hisindex=0 placeholder='年度' value='' size='5' readonly />
						    金額：<input type='text'  class='_variable  _history' name="H-Hemd1502" hisindex=0 placeholder='數值' value='' size='5' readonly />(千元) 
						  </li>
						  <li>
						    年度：<input type='text'  class='_variable  _history' name="H-emd1501" hisindex=1 placeholder='年度' value='' size='5' readonly />
						    金額：<input type='text'  class='_variable  _history' name="H-emd1502" hisindex=1 placeholder='數值' value='' size='5' readonly />(千元) 
						  </li>
						  <li>
						    年度：<input type='text'  class='_variable  _history' name="H-emd1501" hisindex=2 placeholder='年度' value='' size='5' readonly />
						    金額：<input type='text'  class='_variable  _history' name="H-emd1502" hisindex=2 placeholder='數值' value='' size='5' readonly />(千元) 
						  </li>
						  <li>
						    年度：<input type='text'  class='_variable  _history' name="H-emd1501" hisindex=3 placeholder='年度' value='' size='5' readonly />
						    金額：<input type='text'  class='_variable  _history' name="H-emd1502" hisindex=3 placeholder='數值' value='' size='5' readonly />(千元) 
						  </li>
						</ul>
					</div>
				</div>
				
				
				
				
							
				<?php /*
				<div class='data_col _hashentry' id='block-emd16' > 
				  <label >16. 保育價值與目標參考文獻</label>
				  <div class='data_value'>   
					<textarea type='text'  class='_variable _update _wide' name="emd16" id='emd16'  placeholder='保育價值與目標參考文獻' value=''  ></textarea>
				  </div>
				</div>
				*/
				?>
				
				
				
			  </div>	
				
			  <ul class='guide_area'>
			    <li dom='block-emd01' class='finish'>設立日期</li>
				<li dom='block-emd02' >法源依據</li>
				<li dom='block-emd03' >五年內保護區法源是否更動</li>
				<li dom='block-emd04' >職權範圍與保護區重疊、或與保護區經營管理運作有競合關係的法規</li>
				<li dom='block-emd05' >保護區大小</li>
				<li dom='block-emd06' >五年內保護區大小是否有產生變動</li>
				<li dom='block-emd07' >五年內產權是否有產生變動？</li>
				<li dom='block-emd08' >產權所有者</li>
				<li dom='block-emd09' >管理機關</li>
				<li dom='block-emd10' >人力資源</li>
				<li dom='block-emd11' >設立保護區的主要價值</li>
				<li dom='block-emd12' >保護區的保育目標</li>
				<li dom='block-emd13' >其他單位投入預算</li>
				<li dom='block-emd14' >本年度已執行結束與本保護區相關的計畫成果報告</li>
				<li dom='block-emd15' >近五年年度預算</li>
				<!--<li dom='block-emd16' >保育價值與目標參考文獻</li>-->
				
			  </ul>	
				
			</div>   
		  </div>
		  <!-- end of session -->
		  
		  <!-- session 3 -->
		  <div class='data_record_block' id='questionnaire_tendency' data-table='evaluation_mettpressure'>
		    <div class='record_header'>
			   <span class='record_name'>(二) 保護區的壓力表</span> 
			   <span class='record_name'> / 前次評量：<span class='history_record'></span> </span> 
			   <span class='record_option'>
			     <button type='button' class='active act_check_input'> 檢查資料 </button>
			   </span> 
			</div> 
		    <div class='record_body' id=''>  
			  <div class='form_area'>  
<?php 
function BuiltTable($EmpCode,$PreValue=[],$NowValue=[]){
  echo "
	<table class='tendency_block'>
	  <thead>
		<tr>
		  <td>評量</td>
		  <td>前次評量</td>
		  <td>本次評量</td>
		</tr>
	  </thead>
	  <tbody class='furthermain'>
		<tr>
		  <td>是否存在此壓力</td>
		  <td>
		    <input type='radio' class='_variable _history _furthercheck' name='H-".$EmpCode.'check'."' hisindex=0 readonly disabled checked  value=0 />無 
			<input type='radio' class='_variable _history _furthercheck' name='H-".$EmpCode.'check'."' hisindex=0 readonly disabled value=1 >有
		  </td>
		  <td>
			<input type='radio' name='".$EmpCode.'check'."' class='_variable _update _furthercheck' value=0  >無 
			<input type='radio' name='".$EmpCode.'check'."' class='_variable _update _furthercheck' value=1  >有
			<span class='furtherdescrip'>，請說明:<input type='text' class='_variable _update _wide' name='".$EmpCode.'descrip'."' />，並勾選下列選項</span>
		  </td>
		</tr>
	  </tbody>	
	  <tbody class='furtherform' >	
		<tr>
		  <td>與去年相較壓力的變化趨勢</td>
		  <td><span class='_variable _history' name='H-".$EmpCode.'A'."' hisindex=0 ></span></td>
		  <td>
			<ul class='optionset' title='前述本次評量欄位填寫「有」，此欄位必填'>
			  <li><input type='radio' name='".$EmpCode.'A'."' class='_variable _update ' value='遽減'	/>遽減</li>
			  <li><input type='radio' name='".$EmpCode.'A'."' class='_variable _update ' value='緩減'	/>緩減</li>
			  <li><input type='radio' name='".$EmpCode.'A'."' class='_variable _update ' value='不變'	/>不變</li>
			  <li><input type='radio' name='".$EmpCode.'A'."' class='_variable _update ' value='微升'	/>微升</li>
			  <li><input type='radio' name='".$EmpCode.'A'."' class='_variable _update ' value='遽升'	/>遽升</li>
			</ul>
		  </td>
		</tr>
		
		<tr>
		  <td>影響範圍</td>
		  <td><span class='_variable _history' name='H-".$EmpCode.'B'."' hisindex=0 ></span></td>
		  <td>
			<ul  class='optionset' title='前述本次評量欄位填寫「有」，此欄位必填'>
			  <li><input type='radio' name='".$EmpCode.'B'."' class='_variable _update ' value='< 5%'	/> < 5% </li>
			  <li><input type='radio' name='".$EmpCode.'B'."' class='_variable _update ' value='5~15%'	/> 5~15% </li>
			  <li><input type='radio' name='".$EmpCode.'B'."' class='_variable _update ' value='15~50%'	/> 15~50% </li>
			  <li><input type='radio' name='".$EmpCode.'B'."' class='_variable _update ' value='> 50%'	/> > 50% </li>
			</ul>
		  </td>
		</tr>
		<tr>
		  <td>影響程度</td>
		  <td><span class='_variable _history' name='H-".$EmpCode.'C'."' hisindex=0 ></span></td>
		  <td>
			<ul  class='optionset' title='前述本次評量欄位填寫「有」，此欄位必填'>
			  <li><input type='radio' name='".$EmpCode.'C'."' class='_variable _update ' value='微'	/>微</li>
			  <li><input type='radio' name='".$EmpCode.'C'."' class='_variable _update ' value='普'	/>普</li>
			  <li><input type='radio' name='".$EmpCode.'C'."' class='_variable _update ' value='高'	/>高</li>
			  <li><input type='radio' name='".$EmpCode.'C'."' class='_variable _update ' value='嚴'	/>嚴</li>
			</ul>
		  </td>
		</tr>
		<tr>
		  <td>推測未來影響持續性(年)</td>
		  <td><span class='_variable _history' name='H-".$EmpCode.'D'."' hisindex=0 ></span></td>
		  <td>
			<ul  class='optionset' >
			  <li><input type='radio' name='".$EmpCode.'D'."' class='_variable _update ' value='0~5年'		/> 0~5年 </li>
			  <li><input type='radio' name='".$EmpCode.'D'."' class='_variable _update ' value='5~20年'		/> 5~20年 </li>
			  <li><input type='radio' name='".$EmpCode.'D'."' class='_variable _update ' value='20~100年'	/> 20~100年 </li>
			  <li><input type='radio' name='".$EmpCode.'D'."' class='_variable _update ' value='大於100年'	/> 大於100年 </li>
			</ul>
		  </td>
		</tr>
		 
	  </tbody>	
	</table>
  ";
}
?>				
				<div class='_hashentry' id='block-emp01'>
				    <h1 >
					  <div class='topic' >1.住所與商業開發</div>
					  <div class='descrip' >本題項說明：來自人類聚落或其他非農業的土地利用對該區壓力的實際影響</div>
					</h1>
					<div class='question_set' >
						<div class='data_col' id='' > 
						  <label >1.1 房屋與都市化：</label><label>都市、城鎮及聚落，及伴隨建物的軟硬體發展 (如：廣場、路燈、道路、下水道等公共設施)</label>
						  <div class='data_value'><?php BuiltTable('emp0101');?></div>
						</div>
						  
						<div class='data_col' id='' > 
						  <label >1.2 商業與工業區：</label><label>工廠與其他商業中心</label>
						  <div class='data_value'><?php BuiltTable('emp0102');?></div>
						</div>				
						<div class='data_col' id='' > 
						  <label >1.3 觀光遊憩區：</label>
						  <label >有大量人為開發的觀光遊憩區 (通常指保護週邊的大量開發，如高爾夫球場、度假村、主題遊樂園…等觀光遊憩區的開發)：有大量人為開發的觀光遊憩區 (通常指保護週邊的大量開發，如高爾夫球場、度假村、主題遊樂園…等觀光遊憩區的開發)</label>
						  <div class='data_value'><?php BuiltTable('emp0103');?></div>
						</div>
					</div>
				</div>
				
				<div class='_hashentry' id='block-emp02'>
					<h1 >
					  <div class='topic' >2.農牧與水產養殖業</div>
					  <div class='descrip' >本題項說明：因農業擴張及程度加劇 (如造林、海產養殖及水產養殖) 帶來的農墾及放牧行為的壓力</div>
					</h1>
					<div class='question_set'>
						<div class='data_col' id='' > 
						  <label >2.1 一年生及多年生非木材作物 (種植行為)：</label>
						  <label>用於食品、飼料、纖維、燃料或其它用途的作物</label>
						  <div class='data_value'><?php BuiltTable('emp0201');?></div>
						</div>
						<div class='data_col' id='' > 
						  <label >2.2 人工林與紙漿材：</label>
						  <label >在天然林外以生產木材與木材纖維為目的而種植的林木，通常非屬本地種</label>
						  <div class='data_value'><?php BuiltTable('emp0202');?></div>
						</div>
						<div class='data_col' id='' > 
						  <label >2.3 畜牧業與牧場經營：</label>
						  <label >分為圈養及放牧兩種：將家畜飼養於一固定場域，餵食飼料或非在地的資源，是為圈養；讓家畜或半家畜在自然棲地的支持下，於野外自由活動，是為放牧。 </label>
						  <div class='data_value'><?php BuiltTable('emp0203');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >2.4 海水與淡水養殖：</label>
						  <label >將水生動物養殖於一固定場域，餵食飼料或非在地的資源，將孵化魚苗放養於大海。 (箱網養殖) </label>
						  <div class='data_value'><?php BuiltTable('emp0204');?></div>
						</div>
					</div>
				</div>
				
				<div class='_hashentry' id='block-emp03'>
					<h1  >
					  <div class='topic' >3.能源生產與採礦</div>
					  <div class='descrip' >本題項說明：因使用非生物資源進行生產所造成的壓力</div>
					</h1>
					<div class='question_set'>
						<div class='data_col' id='' > 
						  <label > 3.1 石油與天然氣鑽探：</label><label>探勘、開發及生產石油和其它液態烴 </label>
						  <div class='data_value'><?php BuiltTable('emp0301');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label > 3.2 採礦與採石：</label><label>探勘、開發及生產礦物與石材</label>
						  <div class='data_value'><?php BuiltTable('emp0302');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label > 3.3 可再生能源：</label><label>探勘、開發及生產可再生能源</label>
						  <div class='data_value'><?php BuiltTable('emp0303');?></div>
						</div> 
					</div>
				</div>
				
				<div  class='_hashentry' id='block-emp04'>
					<h1 >
					  <div class='topic' >4.交通運輸及服務廊道</div>
					  <div class='descrip' >本題項說明：因狹長的運輸廊道、與載具而造成的相關壓力 (如讓野生動物致命)</div>
					</h1>
					<div class='question_set'>
						<div class='data_col' id='' > 
						  <label >4.1 公路與鐵道：</label><label>道路與專用軌道等地面交通 (包括路殺動物) </label>
						  <div class='data_value'><?php BuiltTable('emp0401');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label > 4.2 公用設備及服務項目：</label><label>能源與資源的傳輸 (例如電力電纜線，電話線)</label>
						  <div class='data_value'><?php BuiltTable('emp0402');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label > 4.3 航道：</label><label>淡水、海洋水面與水下的航路交通</label>
						  <div class='data_value'><?php BuiltTable('emp0403');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label > 4.4 飛行路徑：</label><label>航空與太空運輸</label>
						  <div class='data_value'><?php BuiltTable('emp0404');?></div>
						</div> 
					
					</div>
				</div>
				
				<div class='_hashentry' id='block-emp05'>
					<h1  >
					  <div class='topic' >5.生物資源的使用</div>
					  <div class='descrip' >本題項說明：因使用野生生物資源所產生的威脅，包含計畫與意外捕獲的影響；也包含對特定物種的殘害或控制 (注意，此處指的包括獵捕及殺害動物)</div>
					</h1>
					<div class='question_set'>
					
						<div class='data_col' id='' > 
						  <label > 5.1狩獵與採集陸域動物</label >
						  <label >殺死或陷捉陸生野生動物或動物產製品用於商業、休閒娛樂、生計、科研或文化之目的，或管控/殘害之理由，包括意外死亡/誤捕</label>
						  <div class='data_value'><?php BuiltTable('emp0501');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label > 5.2 採集陸生植物：</label>
						  <label>為商業、休閒娛樂、生計、科研或文化之目的，或管控目的，採集植物、真菌及其他非林木/非動物產品</label>
						  <div class='data_value'><?php BuiltTable('emp0502');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >5.3 木材伐採：</label>
						  <label>為木材、纖維、或燃料伐採樹木與其他木本植物 </label>
						  <div class='data_value'><?php BuiltTable('emp0503');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >5.4 漁撈及收穫水產資源：</label>
						  <label>為商業、休閒娛樂、生計、科研或文化之目的，或管控或殘害目的，垂釣與捕撈水生、野生動植物資源，包括意外死亡/誤捕 </label>
						  <div class='data_value'><?php BuiltTable('emp0504');?></div>
						</div> 
					
					</div>
				</div>
				
				<div class='_hashentry' id='block-emp06'>
					<h1  >
					  <div class='topic' >6. 人類入侵與干擾</div>
					  <div class='descrip' >本題項說明：因非消耗性使用生物資源的人類活動，改變、摧毀或干擾棲地與物種而產生的壓力</div>
					</h1>
					<div class='question_set'>
						<div class='data_col' id='' > 
						  <label >6.1 遊憩活動：</label>
						  <label>以休閒為由，花時間待在自然環境、或者搭乘交通工具在既有交通廊道外旅行</label>
						  <div class='data_value'><?php BuiltTable('emp0601');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >6.2 戰爭、內亂及軍事演習：</label>
						  <label>一次性或非永久性之正規軍事行動或準軍事行動 </label>
						  <div class='data_value'><?php BuiltTable('emp0602');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >6.3 工作與其他活動‵：</label>
						  <label>除了遊憩或軍事行動，其他人們留駐於自然環境或在其間旅行的活動 (如：宗教、營建或運輸使用、飲水點與水壩、蓄意破壞行為，或對保護區員工與遊客的壓力) </label>
						  <div class='data_value'><?php BuiltTable('emp0603');?></div>
						</div> 
					</div>
				</div>
				
				<div class='_hashentry' id='block-emp07'>
					<h1  >
					  <div class='topic' >7. 改變自然系統</div>
					  <div class='descrip' >本題項說明：經營管理天然或半天然的生態系統時 (往往是為了增進人類福祉)，造成棲地改變或劣化的壓力</div>
					</h1>
					<div class='question_set'>
						<div class='data_col' id='' > 
						  <label >7.1 防火與滅火：</label><label>在其自然變化範圍外，增加或減少火災的頻率及(或)強度</label>
						  <div class='data_value'><?php BuiltTable('emp0701');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >7.2 水壩與水管理/使用：</label><label>刻意或因其他活動原因而改變水的自然流動</label>
						  <div class='data_value'><?php BuiltTable('emp0702');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >7.3 其他生態系統修改：</label><label>其他經營管理自然系統以增進人類福祉所造成棲地轉變或劣化的行動 (如：棲地破碎化、島嶼化、發生邊緣效應或基石物種減損等)</label>
						  <div class='data_value'><?php BuiltTable('emp0703');?></div>
						</div> 
					</div>
				</div>
				
				<div class='_hashentry' id='block-emp08'>
					<h1  >
					  <div class='topic' >8. 入侵與其他問題物種及基因</div>
					  <div class='descrip' >本題項說明：原生及非原生動物、植物、微生物/病原體、或遺傳物質之引入、傳播或增殖，在生物多樣性上已經產生，或被預期將會帶來的有害影響</div>
					</h1>
					<div class='question_set'>
						<div class='data_col' id='' > 
						  <label >8.1 外來入侵物種：</label><label>藉由人類活動直接或間接引進與傳播的非原生於該生態系之有害動物、植物、微生物及其他病原體</label>
						  <div class='data_value'><?php BuiltTable('emp0801');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >8.2 本土問題物種：</label><label>原生於該生態系之有害動物、植物、微生物及其他病原體，直接或間接因為人為活動失去其在該生態系中之平衡或非正常出現</label>
						  <div class='data_value'><?php BuiltTable('emp0802');?></div>
						</div>
						<div class='data_col' id='' > 
						  <label >8.3 引進遺傳物質：</label><label>遺傳物質之引進 (如基因改造有機物)</label>
						  <div class='data_value'><?php BuiltTable('emp0803');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >8.4 其他：</label>
						  <label >由無法鑑定為本土或外來種之問題動物、植物、微生物或病原體，或由未知病原體引起之疾病、具傳染性的病毒導致生育率降低或死亡率增加等原因，影響生態系原有之平衡與生物多樣性</label>
						  <div class='data_value'><?php BuiltTable('emp0804');?></div>
						</div> 
					</div>
				</div>
				
				<div class='_hashentry' id='block-emp09'>
					<h1  >
					  <div class='topic' >9. 污染</div>
					  <div class='descrip' >本題項說明：外來的點源與非點源性的有毒與剩餘的材料，或能源所造成的壓力</div>
					</h1>
					<div class='question_set'>
						<div class='data_col' id='' > 
						  <label >9.1 家庭污水與城市廢水：</label>
						  <label >來自家戶與都市區域，包括營養物、有毒化學物質，及/或沉積物的水媒汙水與非點源溢流</label>
						  <div class='data_value'><?php BuiltTable('emp0901');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >9.2 工業與軍事廢水：</label>
						  <label >來自工業與軍事來源，包括採礦、能源生產及其他資源採集工業，如：營養物、有毒化學物質及/或沉積物的水媒汙染物</label>
						  <div class='data_value'><?php BuiltTable('emp0902');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >9.3 農業與林業廢水：</label>
						  <label >來自農業、造林及水產養殖業系統，包括營養物、有毒化學物質及/或沉積物的水媒汙染物，及其在施用地點的影響 (如過量的肥料與殺蟲劑)</label>
						  <div class='data_value'><?php BuiltTable('emp0903');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >9.4 垃圾與固體廢物：</label>
						  <label >垃圾與其他固體物質，包括會纏住野生動物的物體</label>
						  <div class='data_value'><?php BuiltTable('emp0904');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >9.5 空氣污染物：</label>
						  <label>點源和非點源的空氣汙染物</label>
						  <div class='data_value'><?php BuiltTable('emp0905');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >9.6 超量能源：</label>
						  <label>會干擾野生動物或生態系統的熱、聲音或光 (如熱汙染、光害等)</label>
						  <div class='data_value'><?php BuiltTable('emp0906');?></div>
						</div> 
					</div>
				</div>
				
				<div class='_hashentry' id='block-emp10'>
					<h1  >
					  <div class='topic' >10. 地質事件</div>
					  <div class='descrip' >本題項說明：地質事件可能是許多生態系中自然擾動體系的一部分。但當一物種或棲地已受損並喪失其回復力、對擾動變得相當脆弱時，地質事件就會是一項壓力。因應這些改變所做的經營管理能力可能是有限的</div>
					</h1>
					<div class='question_set'>
						<div class='data_col' id='' > 
						  <label >10.1 火山：</label><label>火山活動</label>
						  <div class='data_value'><?php BuiltTable('emp1001');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >10.2 地震/海嘯：</label><label>地震和相關事件</label>
						  <div class='data_value'><?php BuiltTable('emp1002');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >10.3 山崩/地滑：</label><label>山崩、雪崩或地滑</label>
						  <div class='data_value'><?php BuiltTable('emp1003');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >10.4 侵蝕與淤積/沉澱：</label><label>土壤之侵蝕、淤積或沉澱所造成之地景改變 (如海岸線或河床改變)</label>
						  <div class='data_value'><?php BuiltTable('emp1004');?></div>
						</div> 
					</div>
				</div>
				
				<div class='_hashentry' id='block-emp11'>
					<h1  >
					  <div class='topic' >11. 氣候變化與惡劣天氣</div>
					  <div class='descrip' >本題項說明：可能與全球暖化及其他超過自然變化範圍的嚴重氣候或天氣事件相關聯，而可能摧毀脆弱的物種或棲地的長期氣候變化</div>
					</h1>
					<div class='question_set'>
						<div class='data_col' id='' > 
						  <label >11.1 棲地改變：</label><label>棲地組成與位置的重大改變</label>
						  <div class='data_value'><?php BuiltTable('emp1101');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >11.2 乾旱：</label><label>降雨量低於正常變異範圍</label>
						  <div class='data_value'><?php BuiltTable('emp1102');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >11.3 極端氣溫：</label><label>氣溫超過或低於正常變異範圍</label>
						  <div class='data_value'><?php BuiltTable('emp1103');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >11.4 暴雨與洪水：</label><label>極端降雨或強風事件，或暴風雨的季節性巨大轉變</label>
						  <div class='data_value'><?php BuiltTable('emp1104');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >11.5 其他影響：</label><label>其他氣候改變的影響或上述沒有包含到的嚴重氣候事件 (列出具體的影響形式)</label>
						  <div class='data_value'><?php BuiltTable('emp1105');?></div>
						</div> 
					</div>
				</div>
				
				<div class='_hashentry' id='block-emp12'>
					<h1  >
					  <div class='topic' >12. 其他</div>
					  <div class='descrip' >其他壓力</div>
					</h1>
					<div class='question_set'>
						<div class='data_col' id='' > 
						  <label >12.1 特殊文化與社會威脅：</label><label>如：文化連結、傳統知識及/ 或經營管理作法的喪失；重要文化場址價值自然地衰退；文化遺產建築、花園或場址等受到破壞</label>
						  <div class='data_value'><?php BuiltTable('emp1201');?></div>
						</div> 
						<div class='data_col' id='' > 
						  <label >12.2 其他威脅：</label><label>其他可能造成保護區負面影響的威脅項目</label>
						  <div class='data_value'><?php BuiltTable('emp1202');?></div>
						</div> 
					</div>
				</div>
				
			  </div><!-- end of form area -->

              <ul class='guide_area'>
			    <li dom='block-emp01'>住所與商業開發</li>
				<li dom='block-emp02'>農牧與水產養殖業</li>
				<li dom='block-emp03'>能源生產與採礦</li>
				<li dom='block-emp04'>交通運輸及服務廊道</li>
				<li dom='block-emp05'>生物資源的使用</li>
				<li dom='block-emp06'>人類入侵與干擾</li>
				<li dom='block-emp07'>改變自然系統</li>
				<li dom='block-emp08'>入侵與其他問題物種及基因</li>
				<li dom='block-emp09'>污染</li>
				<li dom='block-emp10'>地質事件</li>
				<li dom='block-emp11'>氣候變化與惡劣天氣</li>
				<li dom='block-emp12'>其他</li>
			  </ul>
			  
			</div>   
		  </div><!-- end of session -->
		  
		  
		  <!-- session 4 -->
		  <div class='data_record_block' id='questionnaire_evaluate' data-table='evaluation_mettevaluate'>
		    <div class='record_header'>
			    <span class='record_name'>(三) 經營管理評量表</span> 
			    <span class='record_name'> / 前次評量：<span class='history_record'></span> </span> 
			  
				<span class='record_option'>
			        <button type='button' class='active act_check_input'> 檢查資料 </button>
			    </span>
			</div> 
		    <div class='record_body' id=''>  
			  <div class='form_area'>
			    <div class='data_col' id='' > 
				  <div class='data_value'>
				    <table class='evaluate_table'>
				      <thead>
					    <tr> 
						  <td><!--元素--></td>
						  <td>議題</td>
						  <td>評量標準說明(單選)</td>
						  <td>分數</td>
						  <td>上次</td>
						  <td>本次</td>
						  <td>評論/解釋</td>
						</tr>
					  </thead>
				      <tbody  class='_hashentry' id='block-eme01'>
					    <tr> 
						  <td class='element'   rowspan=4>脈絡</td>
						  <td class='topic' 	rowspan=4>1. 法律地位<br/><br/>保護區是否具有法律上的身分 (或者是由契約或其他形式合約所涵蓋的私人保護區)？</td>
						  <td class='selecter' 	>該保護區未經公告</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme01Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme01Score'   value='0' class='_variable _update ' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea	name='eme01Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
							<h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme01Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>保護區籌備與規劃完成 (林務局內部已通過劃設案)，但還沒有開始劃設公告的程序</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme01Score' value='1' class='_variable _history' hisindex=0 disabled /></td>
						  <td class='checked'	><input type='radio'	name='eme01Score' value='1' class='_variable _update _descrip'  /></td>
						</tr>
						<tr> 
						  <td>在公告劃設的程序中</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme01Score' value='2' class='_variable _history' hisindex=0 disabled /></td>
						  <td class='checked'	><input type='radio'	name='eme01Score' value='2' class='_variable _update _descrip'  /></td>
						</tr>
						<tr> 
						  <td>該保護區已經正式公告</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme01Score' value='3' class='_variable _history' hisindex=0 disabled /></td>
						  <td class='checked'	><input type='radio'	name='eme01Score' value='3' class='_variable _update _descrip'  /></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme02'>
					    <tr> 
						  <td class='element'   rowspan=4>規劃</td>
						  <td class='topic' 	rowspan=4>2. 保護區法規規範<br/><br/>是否有適當的法規以控制土地利用與人為活動 (如打獵)？</td>
						  <td class='selecter' 	>沒有管控保護區內土地利用與人為活動的法規</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme02Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme02Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme02Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme02Descrip'></div>
						</tr>
						<tr> 
						  <td>有指涉管控保護區內土地利用與人為活動的法規，但明顯不足</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme02Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme02Score' value='1' class='_variable _update _descrip' /></td>
						</tr>
						<tr> 
						  <td>有管控保護區內土地利用與人為活動的法規，但稍嫌不足或有缺失</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme02Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme02Score' value='2' class='_variable _update _descrip' /></td>
						</tr>
						<tr> 
						  <td>有管控保護區內不當土地利用與人為活動的法規，並能提供絕佳的經營管理基礎</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme02Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme02Score' value='3' class='_variable _update _descrip' /></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme03'>
					    <tr> 
						  <td class='element'   rowspan=4>投入</td>
						  <td class='topic' 	rowspan=4>3. 法律的執行情況<br/><br/>員工 (負責保護區經營管理的人員) 能否充分執行保護區的法規？</td>
						  <td class='selecter' 	>員工沒有有效的能力/ 資源執行保護區的法規</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme03Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme03Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme03Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme03Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>員工執行保護區法規的能力/ 資源明顯 (嚴重) 不足 (如能力不足、沒有巡護經費、缺乏制度上的支持)<br/>(近五年每年都有非法案件，如盜伐、盜墾、盜獵、抗爭等情形發生)</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme03Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme03Score' value='1' class='_variable _update _descrip'/></td>
						</tr>
						<tr> 
						  <td>員工有堪可接受的能力/ 資源執行保護區的法規，但仍嫌不足</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme03Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme03Score' value='2' class='_variable _update _descrip'/></td>
						</tr>
						<tr> 
						  <td>員工有傑出的能力/ 資源執行保護區的法規</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme03Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme03Score' value='3' class='_variable _update _descrip'/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme04'>
					    <tr> 
						  <td class='element'   rowspan=4>規劃</td>
						  <td class='topic' 	rowspan=4>4. 保護區目標<br/><br/>保護區是否有確定的目標？又是否有根據此目標進行經營管理？</td>
						  <td class='selecter' 	>保護區沒有確定的目標</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme04Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme04Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme04Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme04Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>保護區訂有確定的目標，但未依其進行經營管理</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme04Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme04Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>保護區訂有確定的目標，但僅依部份進行經營管理</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme04Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme04Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>保護區訂有確定的目標，並依其進行經營管理</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme04Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme04Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme05'>
					    <tr> 
						  <td class='element'   rowspan=4>規劃</td>
						  <td class='topic' 	rowspan=4>5. 保護區設計<br/><br/>保護區的大小與形狀是否恰當而能保護亟欲關注的物種、棲地、生態過程及集水區？</td>
						  <td class='selecter' 	>保護區設計不適當，很難達到保護區的主要目標</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme05Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme05Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme05Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme05Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>保護區設計不適當，雖很難達到保護區的主要目標，但仍有一些緩和行動 (如與鄰近的土地所有權人協議設置野生生物廊道、或引進適當的流域管理)</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme05Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme05Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>保護區的設計對於目標達成影響不大，但仍有待改善的空間 (如就較大尺度的生態過程而言)</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme05Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme05Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>保護區的設計有助於達成目標；對於物種與棲地保育而言相當適當；也維持了生態過程，如維持流域尺度下的地表逕流與地下水流、自然擾動的型態等</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme05Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme05Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme06'>
					    <tr> 
						  <td class='element'   rowspan=4>過程</td>
						  <td class='topic' 	rowspan=4>6. 保護區界線<br/><br/>保護區的界線是否清楚且眾所皆知？</td>
						  <td class='selecter' 	>經營管理機關或在地居民/ 鄰近土地使用者不清楚保護區的界線</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme06Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme06Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme06Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme06Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>經營管理機關清楚保護區的界線，但在地居民/ 鄰近土地使用者則不然</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme06Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme06Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>經營管理機關與在地居民/ 鄰近土地使用者清楚保護區界線，但其標定不夠明確</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme06Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme06Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>經營管理機關與在地居民/ 鄰近土地使用者清楚保護區的界線，且其標定明確</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme06Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme06Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme07'>
					    <tr> 
						  <td class='element'   rowspan=4>規劃</td>
						  <td class='topic' 	rowspan=4>7. 經營管理計畫<br/><br/>有無被執行的經營管理計畫？</td>
						  <td class='selecter' 	>保護區沒有經營管理計畫</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme07Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme07Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme07Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme07Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>經營管理計畫正在發展中，或既有但未被執行</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme07Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme07Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有經營管理計畫，但受經費限制或其他問題，僅部分被執行</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme07Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme07Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有正在執行中的經營管理計畫</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme07Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme07Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
						
						<tr> 
						  <td class='element'   >規劃</td>
						  <td class='topic' 	>7a. 規劃過程</td>
						  <td class='selecter' 	>規劃過程讓關鍵的權益關係人有適當機會參與並影響經營管理計畫 (經營管理計畫有納入權益關係人的意見，如目標、重要工作項目或威脅壓力的議定)</td>
						  <td class='score'  	>+1</td>
						  <td class='checked' 	><input type='radio'	name='H-eme07aScore' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='checkbox'	name='eme07aScore'   value='1' class='_variable _update _descrip ' /></td>
						  <td class='descrip'   ><input type='text'		name='eme07aDescrip' value=''  class='_variable _update' placeholder='請說明給分原因'/></td>
						</tr>
						
						<tr> 
						  <td class='element'   >規劃</td>
						  <td class='topic' 	>7b. 規劃過程</td>
						  <td class='selecter' 	>已建立定期回顧與更新經營管理計畫的時程與流程 (依政策規定，五年進行一次定期回顧與更新)</td>
						  <td class='score'  	>+1</td>
						  <td class='checked' 	><input type='radio'	name='H-eme07bScore' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='checkbox'	name='eme07bScore'   value='1' class='_variable _update _descrip ' /></td>
						  <td class='descrip'   ><input type='text'		name='eme07bDescrip' value=''  class='_variable _update' placeholder='請說明給分原因'/></td>
						</tr>
						
						<tr> 
						  <td class='element'   >規劃</td>
						  <td class='topic' 	>7c. 規劃過程</td>
						  <td class='selecter' 	>定期將監測、研究及評量的結果納入規劃</td>
						  <td class='score'  	>+1</td>
						  <td class='checked' 	><input type='radio'	name='H-eme07cScore' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='checkbox'	name='eme07cScore'   value='1' class='_variable _update _descrip ' /></td>
						  <td class='descrip'   ><input type='text'		name='eme07cDescrip' value=''  class='_variable _update' placeholder='請說明給分原因'/></td>
						</tr>
						
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme08'>
					    <tr> 
						  <td class='element'   rowspan=4>規劃/ 產出</td>
						  <td class='topic' 	rowspan=4>8. 常態性的工作計畫 (年度工作計畫)<br/><br/>是否有被執行的常態性工作計畫？</td>
						  <td class='selecter' 	>沒有常態性的工作計畫</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme08Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme08Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme08Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme08Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>有常態性的工作計畫，但僅執行少數項目 (1/2以下) </td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme08Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme08Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有常態性的工作計畫，且執行許多項目 (1/2以上但未完全) </td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme08Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme08Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有常態性的工作計畫，且執行所有項目</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme08Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme08Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme09'>
					    <tr> 
						  <td class='element'   rowspan=4>投入</td>
						  <td class='topic' 	rowspan=4>9. 資源清單<br/><br/>是否有被執行的常態性工作計畫？</td>
						  <td class='selecter' 	>沒有或幾乎沒有保護區關鍵的棲地、物種及文化價值的資訊</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme09Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme09Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme09Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme09Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>保護區關鍵的棲地、物種、生態過程及文化價值的資訊不足以支持規劃與決策 </td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme09Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme09Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>保護區關鍵的棲地、物種、生態過程及文化價值的資訊足以支持大多數重要區域的規劃與決策</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme09Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme09Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>保護區關鍵的棲地、物種、生態過程及文化價值的資訊足以支持所有區域的規劃與決策</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme09Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme09Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme10'>
					    <tr> 
						  <td class='element'   rowspan=4>過程/ 成果</td>
						  <td class='topic' 	rowspan=4>10. 保護系統<br/><br/>保護區內是否具有控管進出/ 資源使用的系統？</td>
						  <td class='selecter' 	>缺乏保護系統或保護系統無法有效控管進出/ 資源使用 (巡邏隊、許可證等)</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme10Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme10Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme10Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme10Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>保護系統僅能部份有效控管進出/ 資源使用 </td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme10Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme10Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>控管進出/ 資源使用的保護系統中度有效 (可達到約50%的效果)</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme10Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme10Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>控管進出/ 資源使用的措施大多或全部有效</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme10Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme10Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme11'>
					    <tr> 
						  <td class='element'   rowspan=4>過程</td>
						  <td class='topic' 	rowspan=4>11.研究<br/><br/>有無以經營管理為導向的調查與研究工作</td>
						  <td class='selecter' 	>在保護區內沒有進行調查或研究工作</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme11Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme11Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme11Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme11Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>有少量的調查與研究工作，但並不直接符合保護區經營管理的需求 (涵蓋項目少於1/3五年內預定執行之計畫項目，且不符合五年的保護區經營管理計畫時程與安排的需求)</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme11Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme11Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有相當多的調查與研究工作，但並不直接符合保護區經營管理的需求 (涵蓋項目少於2/3，大於1/3五年內預定執行之計畫項目)</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme11Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme11Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有全面、整合性的調查與研究工作計畫，並符合經營管理的需求 (涵蓋項目大於2/3五年內預定執行之計畫項目和五年內預定執行之計畫項目之關鍵項目)</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme11Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme11Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme12'>
					    <tr> 
						  <td class='element'   rowspan=4>過程</td>
						  <td class='topic' 	rowspan=4>12. 資源經營管理<br/><br/>有無積極的資源經營管理？ (有些保護區雖不需要積極的人為介入，但若能了解保護區的狀況，並予以處理，即為積極)</td>
						  <td class='selecter' 	>沒有積極的資源經營管理</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme12Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme12Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme12Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme12Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>針對關鍵棲地、物種、生態過程及文化價值的積極經營管理要求幾乎沒有執行</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme12Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme12Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>積極執行許多關鍵棲地、物種、生態過程及文化價值的經營管理，但一些重要議題未被強調</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme12Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme12Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>持續或充分積極地執行關鍵棲地、物種、生態過程及文化價值的經營管理</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme12Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme12Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme13'>
					    <tr> 
						  <td class='element'   rowspan=4>投入</td>
						  <td class='topic' 	rowspan=4>13. 員工數量<br/><br/>是否雇用足夠的人力在現場經營管理保護區？ (以人事費雇用的人力)</td>
						  <td class='selecter' 	>沒有員工</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme13Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme13Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme13Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme13Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>員工數量不足以執行關鍵的經營管理行動</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme13Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme13Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>員工數量少於進行關鍵經營管理行動的理想人數</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme13Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme13Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>員工數量能滿足保護區經營管理的需求</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme13Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme13Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme14'>
					    <tr> 
						  <td class='element'   rowspan=4>投入/ 過程</td>
						  <td class='topic' 	rowspan=4>14A. 員工訓練<br/><br/>員工是否受到適當的訓練以符合且應對經營管理目標？</td>
						  <td class='selecter' 	>員工缺乏保護區經營管理所需的訓練</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme14aScore' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme14aScore'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme14aDescrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme14aDescrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>員工的訓練和保護區的需求關聯較低</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme14aScore' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme14aScore' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>員工的訓練適當，但仍可再改善以充分符合且應對經營管理目標</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme14aScore' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme14aScore' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>員工的訓練能符合且應對該保護區的經營管理需求</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme14aScore' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme14aScore' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme14b'>
					    <tr> 
						  <td class='element'   rowspan=4>投入/ 過程</td>
						  <td class='topic' 	rowspan=4>14B. 員工技能<br/><br/>員工是否有足夠的技能以符合且應對經營管理目標？</td>
						  <td class='selecter' 	>員工缺乏保護區經營管理所需的技能</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme14bScore' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme14bScore'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme14bDescrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme14bDescrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>員工的技能和保護區的需求關聯較低</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme14bScore' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme14bScore' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>員工的技能適當，但仍可再改善以充分符合且應對經營管理目標</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme14bScore' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme14bScore' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>員工的技能能符合且應對該保護區的經營管理需求</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme14bScore' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme14bScore' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme15'>
					    <tr> 
						  <td class='element'   rowspan=4>投入</td>
						  <td class='topic' 	rowspan=4>15. 現有經費<br/><br/>現有經費是否充足？</td>
						  <td class='selecter' 	>沒有保護區經營管理的經費</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme15Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme15Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme15Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme15Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>可用經費不足以符合且應對經營管理的基本需求 (關鍵經營管理動作)，且嚴重限制了經營管理能力</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme15Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme15Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>可用經費尚可接受，但仍有改善空間以充分符合且應對有效經營管理</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme15Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme15Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>可用經費充足且充分符合且應對保護區的經營管理需求</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme15Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme15Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme16'>
					    <tr> 
						  <td class='element'   rowspan=4>投入</td>
						  <td class='topic' 	rowspan=4>16. 經費保障<br/><br/>經費是否穩定？</td>
						  <td class='selecter' 	>保護區經營管理的經費沒有保障，且全數仰賴外界或高度不穩定的資金</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme16Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme16Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme16Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme16Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>經費幾乎沒有保障，且在沒有外界資金的情況下，保護區不能適當地運作</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme16Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme16Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有適當無虞的核心經費供保護區日常運作，但許多創新與方案仍仰賴外界資金</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme16Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme16Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>保護區與其經營管理需求具有充足無虞的經費</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme16Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme16Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme17'>
					    <tr> 
						  <td class='element'   rowspan=4>過程</td>
						  <td class='topic' 	rowspan=4>17. 經費經營管理<br/><br/>經費是否有得到管理以符合關鍵的經營管理需求？ (經費的有效利用)</td>
						  <td class='selecter' 	>經費經營管理非常不足且明顯損害了效能</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme17Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme17Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme17Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme17Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>經費經營管理不足且限制了效能</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme17Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme17Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>經費經營管理適當但仍可改善</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme17Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme17Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>經費經營管理卓越且符合且應對經營管理需求</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme17Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme17Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme18'>
					    <tr> 
						  <td class='element'   rowspan=4>投入</td>
						  <td class='topic' 	rowspan=4>18.設備<br/><br/>設備是否符合且應對經營管理需求？ (包含溝通工具、交通運輸設施、現場設備、員工設施等)</td>
						  <td class='selecter' 	>幾乎沒有或沒有經營管理所需的設備與設施</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme18Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme18Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme18Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme18Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>有一些經營管理所需的設備與設施，但不足以應付大部分的經營管理需求</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme18Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme18Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有經營管理所需的設備與設施，但仍有稍微落差而限制經營管理</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme18Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme18Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有適當的設備與設施</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme18Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme18Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme19'>
					    <tr> 
						  <td class='element'   rowspan=4>過程</td>
						  <td class='topic' 	rowspan=4>19. 設備維護<br/><br/>設備是否得到適當維護？</td>
						  <td class='selecter' 	>幾乎沒有或沒有維護設備與設施</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme19Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme19Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme19Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme19Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>有一些設備與設施的臨時性維護 (以現有材料進行非永久性或固定性的修繕)</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme19Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme19Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有設備與設施的基礎維護</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme19Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme19Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>設備與設施維護良好 (即時且完善)</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme19Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme19Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme20'>
					    <tr> 
						  <td class='element'   rowspan=4>過程</td>
						  <td class='topic' 	rowspan=4>20. 教育及推廣<br/><br/>有無規劃扣連目標和需求的教育計畫？</td>
						  <td class='selecter' 	>沒有教育與推廣計畫 (未列於經營管理計畫書中，但有執行)</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme20Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme20Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme20Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme20Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>具備有限與臨時的教育與推廣計畫</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme20Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme20Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有教育與推廣計畫，但僅滿足部分需求，仍有待改善</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme20Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme20Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有適當且全面實行的教育與推廣計畫</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme20Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme20Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme21'>
					    <tr> 
						  <td class='element'   rowspan=4>規劃</td>
						  <td class='topic' 	rowspan=4>21. 土地與水資源利用規劃<br/><br/>土地與水資源利用規劃有無考量到保護區並有助於保育目標的達成？</td>
						  <td class='selecter' 	>鄰近的土地與水資源利用規劃未考量保護區的需求，且其活動/ 政策不利於保護區</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme21Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme21Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme21Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme21Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>鄰近的土地與水資源利用規劃未考量保護區的長期需求，但其活動不會對保護區有害</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme21Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme21Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>鄰近的土地與水資源利用規劃局部考量保護區的長期需求</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme21Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme21Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>鄰近的土地與水資源利用規劃完整考量保護區的長期需求(例如：周邊土地使用有經營管理計畫)</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme21Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme21Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
						
						<tr> 
						  <td class='element'   >規劃</td>
						  <td class='topic' 	>21a. 為棲地保育進行土地與水資源規劃</td>
						  <td class='selecter' 	>涵括保護區的流域或地景規劃與經營管理，有考量提供永續相關棲地的適當環境條件 (如水流流量、水質與時段、空氣汙染程度等)</td>
						  <td class='score'  	>+1</td>
						  <td class='checked' 	><input type='radio'	name='H-eme21aScore' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='checkbox'	name='eme21aScore'   value='1' class='_variable _update _descrip ' /></td>
						  <td class='descrip'   ><input type='text'		name='eme21aDescrip' value=''  class='_variable _update'  placeholder='請說明給分原因'/></td>
						</tr>
						<tr> 
						  <td class='element'   >規劃</td>
						  <td class='topic' 	>21b. 為連結性所進行土地與水資源規劃</td>
						  <td class='selecter' 	>連結保護區提供野生生物通往區外關鍵棲地的廊道經營管理 (如：讓遷徙性魚類能夠在淡水產卵地與海洋間移動，或讓動物能夠遷徙)</td>
						  <td class='score'  	>+1</td>
						  <td class='checked' 	><input type='radio'	name='H-eme21bScore' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='checkbox'	name='eme21bScore'   value='1' class='_variable _update _descrip '  /></td>
						  <td class='descrip'   ><input type='text'		name='eme21bDescrip' value=''  class='_variable _update'  placeholder='請說明給分原因'/></td>
						</tr>
						<tr> 
						  <td class='element'   >規劃</td>
						  <td class='topic' 	>21c. 為生態系服務與物種保育進行的土地與水資源規劃</td>
						  <td class='selecter' 	>〝著重生態系特定需求與/ 或考量特定物種在生態系尺度上需求的規劃 (如：淡水的流量、品質及時機以維持特定物種，為維持莽原棲地進行火的管理等)〞</td>
						  <td class='score'  	>+1</td>
						  <td class='checked' 	><input type='radio'	name='H-eme21cScore' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='checkbox'	name='eme21cScore'   value='1' class='_variable _update _descrip ' /></td>
						  <td class='descrip'   ><input type='text'		name='eme21cDescrip' value=''  class='_variable _update'  placeholder='請說明給分原因'/></td>
						</tr>
						
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme22'>
					    <tr> 
						  <td class='element'   rowspan=4>過程</td>
						  <td class='topic' 	rowspan=4>22. 行政邊界或商業上的鄰居<br/><br/>有無和相鄰的土地與水資源使用者合作？</td>
						  <td class='selecter' 	>管理者和鄰近的經營管理機關或土地與水資源使用團體間沒有聯繫</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme22Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme22Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme22Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme22Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>管理者和鄰近的經營管理機關或土地與水資源使用團體間有聯繫，但幾乎沒有或沒有合作</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme22Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme22Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>管理者和鄰近的經營管理機關或土地與水資源使用團體間有聯繫，但僅有一些合作</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme22Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme22Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>管理者和鄰近的經營管理機關或土地與水資源使用團體間有規律定期的聯繫，且在經營管理上有實質合作</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme22Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme22Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme23'>
					    <tr> 
						  <td class='element'   rowspan=4>過程</td>
						  <td class='topic' 	rowspan=4>
						    23.在地社區（非原住民）<br/><br/>居住或是鄰近於保護區的在地社區是否投入經營管理決策中？<br/><br/>
							<div class='exclude_record'>
							  不列入計分：<input type='checkbox'	name='eme23Option' value='1' class='_variable _update _descrip '  /><br/>
						 	  請說明理由：<input type='text' 		name='eme23Reason' value=''  class='_variable _update' 	   />
							</div>
						  </td>
						  <td class='selecter' 	>在地社區沒有參與保護區經營管理決策</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme23Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme23Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme23Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme23Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>在地社區有參與一些經營管理的討論，但沒有實質的角色扮演</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme23Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme23Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>在地社區直接影響一些經營管理決策，但其參與仍有待提升</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme23Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme23Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>在地社區直接參與所有經營管理決策 (如共管)</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme23Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme23Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme24'>
					    <tr> 
						  <td class='element'   rowspan=4>過程</td>
						  <td class='topic' 	rowspan=4>
						    24. 原住民族在地社區（原住民）<br/><br/>原住民是否投入經營管理決策中？<br/><br/>
							<div class='exclude_record'>
							  不列入計分：<input type='checkbox'	name='eme24Option' value='1' class='_variable _update _descrip '  /><br/>
						 	  請說明理由：<input type='text' 		name='eme24Reason' value=''  class='_variable _update' 	/>
							</div>
						  </td>
						  <td class='selecter' 	>原住民沒有參與保護區經營管理決策</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme24Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme24Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme24Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme24Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>原住民有參與一些經營管理的討論，但沒有實質的角色扮演</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme24Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme24Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>原住民直接影響一些經營管理決策，但其參與仍有待提升</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme24Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme24Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>原住民直接參與所有經營管理決策 (如共管)</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme24Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme24Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
						
						
						<tr> 
						  <td class='element'   >過程</td>
						  <td class='topic' 	>24a. 對社區的衝擊</td>
						  <td class='selecter' 	>在地社區與/ 或原住民、權益關係人及保護區經營管理者間有開放的溝通與信任</td>
						  <td class='score'  	>+1</td>
						  <td class='checked' 	><input type='radio'	name='H-eme24aScore'	value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='checkbox'	name='eme24aScore'		value='1' class='_variable _update _descrip ' /></td>
						  <td class='descrip'   ><input type='text'		name='eme24aDescrip'	value=''  class='_variable _update' placeholder='請說明給分原因'/></td>
						</tr>
						<tr> 
						  <td class='element'   >過程</td>
						  <td class='topic' 	>24b. 對社區的衝擊</td>
						  <td class='selecter' 	>在保育保護區資源的同時，也會增進社區福祉 (除經濟誘因外) </td>
						  <td class='score'  	>+1</td>
						  <td class='checked' 	><input type='radio'	name='H-eme24bScore'	value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='checkbox'	name='eme24bScore'		value='1' class='_variable _update _descrip ' /></td>
						  <td class='descrip'   ><input type='text'		name='eme24bDescrip'	value=''  class='_variable _update' placeholder='請說明給分原因'/></td>
						</tr>
						<tr> 
						  <td class='element'   >過程</td>
						  <td class='topic' 	>24c. 對社區的衝擊</td>
						  <td class='selecter' 	>在地居民與/ 或原住民主動支持該保護區</td>
						  <td class='score'  	>+1</td>
						  <td class='checked' 	><input type='radio'	name='H-eme24cScore'	value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='checkbox'	name='eme24cScore'		value='1' class='_variable _update _descrip ' /></td>
						  <td class='descrip'   ><input type='text'		name='eme24cDescrip'	value=''  class='_variable _update' placeholder='請說明給分原因'/></td>
						</tr>
						
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme25'>
					    <tr> 
						  <td class='element'   rowspan=4>成果</td>
						  <td class='topic' 	rowspan=4>25. 經濟利益<br/><br/>保護區有無提供在地社區如收入、就業及環境服務的報償作為經濟誘因？</td>
						  <td class='selecter' 	>保護區沒有提供在地社區任何經濟利益</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme25Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme25Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme25Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme25Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>有潛在的經濟利益，且有發展計畫以實現之</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme25Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme25Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有分配在地社區一些經濟利益</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme25Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme25Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>相關保護區的活動分配給在地社區大量的經濟利益</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme25Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme25Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme26'>
					    <tr> 
						  <td class='element'   rowspan=4>規劃/ 過程</td>
						  <td class='topic' 	rowspan=4>26. 監測與評量<br/><br/>保護區活動有無相關效能的監測？</td>
						  <td class='selecter' 	>保護區沒有進行監測與評量</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme26Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme26Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme26Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme26Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>有一些臨時性的監測與評量，但沒有全面的策略與/ 或定期性彙整結果</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme26Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme26Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有一議定並實施的監測與評量系統，但結果未(完全) 回饋納入經營管理</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme26Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme26Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>存在良好與貫徹執行的監測與評量系統，並使用於適應性經營管理</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme26Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme26Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme27'>
					    <tr> 
						  <td class='element'   rowspan=4>產出</td>
						  <td class='topic' 	rowspan=4>27. 訪客設施<br/><br/>是否有適宜的訪客設施？</td>
						  <td class='selecter' 	>沒有訪客設施與服務</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme27Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme27Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme27Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme27Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>對於既有的參訪程度，訪客設施與服務是不足的</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme27Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme27Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>對於既有的參訪程度，訪客設施與服務是足夠的，但仍有改善空間</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme27Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme27Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>訪客設施與服務能良善應對既有的參訪程度</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme27Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme27Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme28'>
					    <tr> 
						  <td class='element'   rowspan=4>過程</td>
						  <td class='topic' 	rowspan=4>
						    28. 商業旅遊業者<br/><br/>商業旅遊業者是否對保護區的經營管理做出貢獻？<br/><br/>
							<div class='exclude_record'>
							  不列入計分：<input type='checkbox'	name='eme28Option' value='1' class='_variable _update _descrip'  /><br/>
						 	  請說明理由：<input type='text' 		name='eme28Reason' value=''  class='_variable _update '   />
							</div>
						  </td>
						  <td class='selecter' 	>管理者與利用保護區的旅遊業者間幾乎沒有或沒有聯繫</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme28Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme28Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme28Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme28Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>管理者與利用保護區的旅遊業者間有聯繫，但大多止於行政或管制事宜</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme28Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme28Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>管理者與使用保護區的旅遊業者間存在有限的合作 (營運合作)，以提升訪客經驗並維持保護區價值</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme28Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme28Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>管理者與使用保護區的商業旅遊業者之間有良好的合作 (營運合作)，以提升訪客經驗並維持保護區價值</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme28Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme28Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme29'>
					    <tr> 
						  <td class='element'   rowspan=4>投入/ 過程</td>
						  <td class='topic' 	rowspan=4>29. 費用<br/><br/>如果進行收費 (如入園費或罰款)，會否有助於保護區的經營管理？</td>
						  <td class='selecter' 	>雖然理論上有收費/罰款，但並未收取費用</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme29Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme29Score'   value='0' class='_variable _update' /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme29Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme29Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>有收取費用，但對保護區或其環境沒有貢獻</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme29Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme29Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有收取費用，並且對保護區與其環境有一點貢獻</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme29Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme29Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>有收取費用，並且對保護區與其環境有顯著貢獻</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme29Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme29Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
					  </tbody>
					  
					  <tbody  class='_hashentry' id='block-eme30'>
					    <tr> 
						  <td class='element'   rowspan=4>成果</td>
						  <td class='topic' 	rowspan=4>30. 價值狀況<br/><br/>相對於上次評量時，保護區的重要價值現況如何？ (若為首次評量，則和公告劃設時比較)<br/></td>
						  <td class='selecter' 	>許多重要的生物多樣性、生態或文化價值正嚴重衰退中 (與保育目標做比對)</td>
						  <td class='score'  	>0</td>
						  <td class='checked' 	><input type='radio'	name='H-eme30Score' value='0' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='radio'	name='eme30Score'   value='0' class='_variable _update'  /></td>
						  <td class='descrip'   rowspan=4>
						    <textarea		name='eme30Descrip' value=''  class='_variable _update' placeholder='請說明給分原因'></textarea>
						    <h2><span>上次評論：</span><a class='option act_copy_emedescrip'><i class="fa fa-clone" aria-hidden="true"></i>沿用</a></h2>
							<div class='_variable _history ' hisindex=0 name='H-eme30Descrip'></div>
						  </td>
						</tr>
						<tr> 
						  <td>一些生物多樣性、生態或文化價值正嚴重衰退中</td>
						  <td class='score'		>1</td>
						  <td class='checked'	><input type='radio'	name='H-eme30Score' value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme30Score' value='1' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>一些生物多樣性、生態與文化價值正局部衰退中，但最重要的價值仍未受到顯著衝擊</td>
						  <td class='score'		>2</td>
						  <td class='checked'	><input type='radio'	name='H-eme30Score' value='2' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme30Score' value='2' class='_variable _update _descrip '/></td>
						</tr>
						<tr> 
						  <td>生物多樣性、生態與文化價值普遍未受損害</td>
						  <td class='score'		>3</td>
						  <td class='checked'	><input type='radio'	name='H-eme30Score' value='3' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked'	><input type='radio'	name='eme30Score' value='3' class='_variable _update _descrip '/></td>
						</tr>
						
						
						<tr> 
						  <td class='element'   >成果</td>
						  <td class='topic' 	>30a. 價值狀況</td>
						  <td class='selecter' 	>有根據研究與/ 或監測的評估價值狀況</td>
						  <td class='score'  	>+1</td>
						  <td class='checked' 	><input type='radio'	name='H-eme30aScore'	value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='checkbox'	name='eme30aScore'		value='1' class='_variable _update _descrip ' /></td>
						  <td class='descrip'   ><input type='text'		name='eme30aDescrip'	value=''  class='_variable _update' placeholder='請說明給分原因'/></td>
						</tr>
						<tr> 
						  <td class='element'   >成果</td>
						  <td class='topic' 	>30b. 價值狀況</td>
						  <td class='selecter' 	>有因應生物多樣性、生態及文化價值所受的威脅的特定經營管理計畫</td>
						  <td class='score'  	>+1</td>
						  <td class='checked' 	><input type='radio'	name='H-eme30bScore' 	value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='checkbox'	name='eme30bScore'		value='1' class='_variable _update _descrip ' /></td>
						  <td class='descrip'   ><input type='text'		name='eme30bDescrip'	value=''  class='_variable _update' placeholder='請說明給分原因'/></td>
						</tr>
						<tr> 
						  <td class='element'   >成果</td>
						  <td class='topic' 	>30c. 價值狀況</td>
						  <td class='selecter' 	>例行的保護區經營管理包括維持關鍵生物多樣性、生態及文化價值的行動</td>
						  <td class='score'  	>+1</td>
						  <td class='checked' 	><input type='radio'	name='H-eme30cScore'    value='1' class='_variable _history' hisindex=0 disabled  /></td>
						  <td class='checked' 	><input type='checkbox'	name='eme30cScore'		value='1' class='_variable _update _descrip '/></td>
						  <td class='descrip'   ><input type='text'		name='eme30cDescrip'	value=''  class='_variable _update' placeholder='請說明給分原因'/></td>
						</tr>
						
					  </tbody>
					  
					  
					</table>
					
					
					
					
				  </div>
				</div> 
			    
			  </div>	
			  <ul class='guide_area'>
			    <li dom='block-eme01'>法律地位</li>
				<li dom='block-eme02'>保護區法規規範</li>
				<li dom='block-eme03'>法律的執行情況</li>
				<li dom='block-eme04'>保護區目標</li>
				<li dom='block-eme05'>保護區設計</li>
				<li dom='block-eme06'>保護區界線</li>
				<li dom='block-eme07'>經營管理計畫</li>
				<li dom='block-eme08'>常態性的工作計畫</li>
				<li dom='block-eme09'>資源清單</li>
				<li dom='block-eme10'>保護系統</li>
				
				<li dom='block-eme11'>研究</li>
				<li dom='block-eme12'>資源經營管理</li>
				<li dom='block-eme13'>員工數量</li>
				<li dom='block-eme14'>員工訓練</li>
				  
				<li dom='block-eme15'>現有經費</li>
				<li dom='block-eme16'>經費保障</li>
				<li dom='block-eme17'>經費經營管理</li>
				<li dom='block-eme18'>設備</li>
				<li dom='block-eme19'>設備維護</li>
				<li dom='block-eme20'>教育及推廣</li>
				
				<li dom='block-eme21'>土地與水資源利用規劃</li>
				<li dom='block-eme22'>行政邊界或商業上的鄰居</li>
				<li dom='block-eme23'>在地社區（非原住民）</li>
				<li dom='block-eme24'>原住民族在地社區（原住民）</li>
				<li dom='block-eme25'>經濟利益</li>
				<li dom='block-eme26'>監測與評量</li>
				<li dom='block-eme27'>訪客設施</li>
				<li dom='block-eme28'>商業旅遊業者</li>
				<li dom='block-eme29'>費用</li>
				<li dom='block-eme30'>價值狀況</li>
				
			  </ul>
				
				
				
				
			</div>   
		  </div><!-- end of session -->
		   
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