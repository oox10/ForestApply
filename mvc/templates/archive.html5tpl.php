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
	
	
	<link type="text/css" href="tool/jquery-ui-1.11.2.custom/jquery-ui.structure.min.css" rel="stylesheet" />
	<link type="text/css" href="tool/jquery-ui-1.11.2.custom/jquery-ui.theme.min.css" rel="stylesheet" />
	
	
	<!-- Tool -->
	
	<!-- jquery mousewheel -->
	<script type="text/javascript" src="tool/jquery-mousewheel-master/jquery.mousewheel.min.js"></script>
	
	<!-- jquery lazyload -->
	<script type="text/javascript" src="tool/lazy-load-xt-master/src/jquery.lazyloadxt.js"></script>
	
	<!-- jquery scroll bar -->
	<link rel="stylesheet" href="tool/jquery.scrollbar/includes/prettify/prettify.css" />
    <script src="tool/jquery.scrollbar/includes/prettify/prettify.js"></script>
	<link type="text/css" href="tool/jquery.scrollbar/jquery.scrollbar.css" rel="stylesheet" />
	<script type="text/javascript" src="tool/jquery.scrollbar/jquery.scrollbar.min.js"></script>
	
	<!-- dropzone file uoloader -->
	<script type="text/javascript" src="tool/dropzone-4.2.0/dropzone.min.js"></script>
	
	<!-- jquery event-->
	<script type="text/javascript" src="tool/jquery.event/jquery.event.drag-2.2.js"></script>
	<script type="text/javascript" src="tool/jquery.event/jquery.event.drag.live-2.2.js"></script>
	<script type="text/javascript" src="tool/jquery.event/jquery.event.drop-2.2.js"></script>
	<script type="text/javascript" src="tool/jquery.event/jquery.event.drop.live-2.2.js"></script>
	
	
	<!-- Self -->
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_main.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_archive.css" />
	
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_archive.js"></script>
	
	
	
	
	<!-- PHP -->
	
	
	<?php
	
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	
	
	$level_data_catalog  = isset($this->vars['server']['data']['level']) ? $this->vars['server']['data']['level'] : array('');
	$user_tags_reference = isset($this->vars['server']['data']['tags'])  ? $this->vars['server']['data']['tags']  : array();
	
	
	//-- Server Data For Page
	
	// search data
	$server_result = isset($this->vars['server']['data']['result'])  ? $this->vars['server']['data']['result'] : array('result_data'=>array(),'access_num'=>'');
	$user_folders  = isset($this->vars['server']['data']['folders']) ? $this->vars['server']['data']['folders'] : array('user'=>array(),'share'=>array());
	
	
	/*
	result_num	result_data
	search_sql	search_term	query_set
	page_now	page_num
	*/
	
	$domDisplay['initialPage'] = isset($server_result['result_num']) && $server_result['result_num'] ? 'none' : 'block';
	$domDisplay['resultPage']  = isset($server_result['result_num']) && $server_result['result_num'] ? 'block' : 'none';
	
	$sort_target = isset($server_result['sort_target']) ? $server_result['sort_target'] : _USER_PROFILE_DEFAULT_ORDER;
	$sort_method = isset($server_result['sort_method']) ? strtolower($server_result['sort_method']) : 'desc';
	
	$domDisplay['bylevels']    = 'block';
	$domDisplay['byfolder']    = 'none';
	$domDisplay['byfilter']    = 'none';
	
	// doc status
	$page_info = isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';  
	
	?>
	<!-- Server Data For JS -->
	<?php if(isset($server_result['query_breadcrumbs'])):?>
    <data id='search_breadcrumbs'><?php echo json_encode($server_result['query_breadcrumbs']); ?></data>	
	<?php endif; ?>
	
	<?php if(isset($server_result['access_num'])):?>
    <data id='search_accnum'></data>	
	<?php endif; ?>
	
	<?php if(count($user_tags_reference)):?>
    <data id='tags_reference'><?php echo json_encode($user_tags_reference);?></data>	
	<?php endif; ?>
	
	
  </head>
  <body>
	<div class='system_main_area'>
	  <div class='system_manual_area clear_mode'>
	  <?php include('area_admin_manual.php'); ?>
	  </div>
	  <div class='system_content_area wide_mode tr_like'>
	    <!-- 文件結構區塊 -->
		<div class='structure_area' id='project' no='' >
	      <div class='action_banner'>
		    <span></span>
			<h1> 檔案資料庫 </h1>
			<h2> 檔案蒐尋、匯入與管理 </h2>
		  </div>
		  <ul class='data_group_mode tr_like'>
			<li class='mode_sel this_mode' dom='bylevels'> 
			  <h2>單位分類</h2> 
			</li>
			<li class='mode_sel' dom='byfilter' >
			  <h2>後分類</h2>
			</li>
	        <li class='mode_sel' dom='byfolder'>
			  <h2>個人</h2>
			</li>
		  </ul>
		  <div class='project_structure'>
		    <div class='group_condinter scrollbar-inner'>
			  <div class='data_group' id='bylevels' style='display:<?php echo $domDisplay['bylevels'];?>' >
			  <?php if(isset($level_data_catalog) && count($level_data_catalog)): ?>
				
				<?php
				foreach($level_data_catalog as $organ_code => $level_set){  
				  $NowLevelSite = 0;
				  $TageQueue    = array();
				  
				  foreach($level_set as $lv){
					
					$LevelIndex[$lv['LevelId']]  = $lv['LevelName'];  
					$LevelQueue   = array();
					$i = 2;
					do{
					  $LevelQueue[] = $LevelIndex[substr($lv['LevelId'],0,$i)];
					  $i+=2;	
					}while( $i <= strlen($lv['LevelId']) );
					
					$Top_Tag_Id			= $lv['LevelTable'].'-'.$organ_code.'-'.$lv['LevelId'];
					$Area_Tag_Id		= $organ_code.'-'.$lv['LevelId'];
					$Level_Term_Class 	= join(' ',$lv['LevelClass']);
					$Level_Term_Padding = (($lv['LevelSite']-1)*15)."px";
					$LevelDisplay = isset($lv['LevelView']) ? $lv['LevelView'] : 'none';
					$Level_View =
					"<ul  class='browse_level_area' role='tree' >\n".
					"  <li id='".$Top_Tag_Id."' class='LevelTerm ".$Level_Term_Class."' style='padding-left:".$Level_Term_Padding.";'  level='".join('/',$LevelQueue)."' info='".$lv['LevelInfo']."' code='".$lv['LevelId']."' >\n".
					"    <span class='level_option' ><a class='level_switch'  dom=".$Area_Tag_Id."  >".$lv['LevelOption']."</a></span>".
				    "    <span class='level_name' 	><a href='index.php?act=Archive/level/".$Area_Tag_Id."' target= '_self' >".System_Helper::short_string_utf8($lv['LevelName'],20-$lv['LevelSite'])."</a></span>".
					"    <span class='level_num'	>".$lv['LevelNum']."</span>". 
					"  </li>\n".
					"  <li id='".$Area_Tag_Id."' class='LevelGroup' style='display:".$LevelDisplay.";' >\n";	
					$Level_Check = $lv['LevelSite'] - $NowLevelSite;
					switch(true){
					  case ($Level_Check === 0) : // 同層
						echo array_pop($TageQueue);  
						echo $Level_View;
					    array_push($TageQueue,"</li></ul>");
						break;
					  case ($Level_Check > 0) : // 下層
						echo $Level_View;
                        array_push($TageQueue,"</li></ul>");						  
						break;
					  case ($Level_Check < 0) : // 上層
						
						echo array_pop($TageQueue); 
						while($Level_Check < 0){
						  echo array_pop($TageQueue);     
						  $Level_Check++;
						}
						echo $Level_View; 	
						array_push($TageQueue,"</li></ul>");
						break;
					}
					$NowLevelSite = $lv['LevelSite'];
				  }
				  echo join("\n",$TageQueue);
				}  
				?>
				<?php endif; ?>  
			  </div>
			  <div class='data_group' id='byfilter'  style='display:<?php echo $domDisplay['byfilter'];?>' accnum='<?php echo isset($server_result['access_num']) ? $server_result['access_num']:''; ?>' >
			    <h1 class='filter_border' >依年分篩選</h1>
			    <ul class='query_filter' id='PQ_YearNum'></ul>
				<h1 class='filter_border' >依執行單位篩選</h1>
			    <ul class='query_filter' id='organ_work'></ul>
				<h1 class='filter_border' >依研究區域篩選</h1>
				<ul class='query_filter' id='research_area'></ul>
				<h1 class='filter_border' >依研究方法篩選</h1>
				<ul class='query_filter' id='research_method'></ul>
				<h1 class='filter_border' >依標關鍵字</h1>
				<ul class='query_filter' id='keywords'></ul>
			  </div>
			  <div class='data_group' id='byfolder'  style='display:<?php echo $domDisplay['byfolder'];?>'> 
			    <h1>資料夾</h1>
			    <ul class='folder_list' id='myfolder'>
				<?php foreach($user_folders['user'] as $mf):?>
				  <li class='act_folder' no='<?php echo $mf['ufno']; ?>' >
				    <span class='mark32 pic_myfolder' title='<?php echo $mf['createtime']; ?>'></span>
					<span title='<?php echo $mf['name'];?>'><?php echo System_Helper::short_string_utf8($mf['name'],20);?></span>
				    <i class='count' title=' <?php echo ($mf['queue']- $mf['files']) ? $mf['queue']- $mf['files'].'個檔案處理中..':'';?> ' > <?php echo $mf['files'];?> </i>
				  </li>
				<?php endforeach;?>
				</ul>
				<!--
				<h1>共享資料夾</h1>
			    <ul class='folder_list' id='sharefolder'>
				<?php foreach($user_folders['share'] as $mf):?>
				  <li class='act_folder' no='<?php echo $mf['ufno']; ?>' title='<?php echo $mf['createtime']; ?>'><span class='mark32 pic_myfolder'></span> <?php echo $mf['name'];?> <i class='count'><?php echo $mf['files'];?></i></li>
				<?php endforeach;?>
				</ul>
				-->
				
				<h1>標籤</h1>
			    <ul class='tag_list' id='tags'>
				<?php foreach($user_tags_reference as $tag):?>
				  <li class='act_tags' term='<?php echo $tag['tag_term']; ?>' >
				    <span class='mark32 pic_systags' ><i class='mark16 pic_photo_meta_tags'></i></span>
					<span title='<?php echo $tag['tag_term'];?>'><?php echo System_Helper::short_string_utf8($tag['tag_term'],16); ?></span>
					<span class='tag_create'>by <?php echo $tag['user_name']; ?></span>
				  </li>
				<?php endforeach;?>  
				</ul>
				
			  </div>
			</div>
		  </div>
		  <div class='project_function tr_like'>
		    <div class='pojf_info'>
			  <label>*</label>
			  <span id='element_counter' ></span>
			</div>
		  </div>
		</div>
		<div class='workspace_area'>
		  <div class='tool_banner' >
		    <div class='search_block' qset=0 >
              <select id='search_field' class='search_field' >
			    <option value='kwds' > 全欄位 </option>
				<option value='pror' > 提供者 </option>
				<option value='phne' > 照片名稱 </option>
				<option value='phdt' > 拍攝時間 </option>
				<option value='phlo' > 拍攝地點 </option>
				<option value='phds' > 照片圖說 </option>
				<option value='cllv' > 專題分類 </option>
			    <option value='tags' > 標籤 </option>
			  </select>
			  <input  id='search_string' class='search_string' type=text id='' value='' >
			  <select id='search_mode' name='accnum'>
			    <option id='narrow' value='<?php echo isset($server_result['access_num'])?$server_result['access_num']:'';?>' > 再搜尋	</option>
			    <option id='reset'  value='' selected> 重新 </option>
			  </select>
			  <button id='act_search'    type='button' class='active' > <i class='mark16 pic_search'></i></button>
			  
			</div>
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
			<span class='search_tips'>搜尋空白欄位，請輸入：<em id='empty_value'>.none</em> </span>
		  </div>
		  <div class='archive_option'>
		    <ol id='system_breadcrumbs' typeof="BreadcrumbList" page='搜尋:' >
			  <li class='breadcrumb' > <a href='/'>首頁</a> </li>
			</ol>
			<div class="view_setting">
			  <label>排序依:</label>
              <span class='sort_type' name='desc'></span>
			  <select class='sort_field' name='<?php echo isset($sort_method) ? $sort_method:''; ?>'>
			    <option value='upload_time'	<?php echo 'upload_time'==$sort_target ? 'selected':''; ?> > 檔案名稱 </option>
			    <option value='file_name'	<?php echo 'file_name'==$sort_target ? 'selected':''; ?> > 上傳時間 </option>
			  </select>
			  <!--
			  <label> 圖片大小:</label>
			  <input id='size_set' type='range' min="100" max="300" value="200" step=10 />
			  <span  id='size_val'>200</span> 
			  --> 
			</div>
		  </div>
		  <div class='work_block'>
		    <div class='work_continder'>
			  <!-- each block  -->
			  <div class='archive_content' id='resultPage'  style='display:<?php echo $domDisplay['resultPage'];?>'>
			    <div class='record_field' >
				  <div>檔案描述</div>
				  <div>文件類型</div>
				  <div>資料年分</div>
				  <div>上傳資訊</div>
				</div>
				<?php foreach($server_result['result_data'] as $rno => $result): ?>
			    <div class='rslItem' no='<?php echo $result['identifier']; ?>' >	  
				  <div class='nameblock'>
				    <div class='fileinfo'>
				      <span class='rno' ><?php echo $rno; ?>.</span>
					  <span class='ricno' ><i class="fa fa-file-<?php echo $result['file_type']; ?>-o" aria-hidden="true"></i></span>
					  <span class='rtitle' ><div><?php echo array_shift($result['@title'])?> - <i class='stitle' ><?php echo join('/',$result['@title']);?> </i> </div></span>
				    </div>
					<ul class='contenttags'>
					<?php if($result['@rarea']): ?>
					<?php   foreach($result['@rarea'] as $area): ?>
					  <li class='rarea' ><?php echo $area; ?></li>
					<?php   endforeach; ?> 
					<?php endif; ?>  
					
					<?php if($result['@rdomain']): ?>
					<?php   foreach($result['@rdomain'] as $area): ?>
					  <li class='rdomain' title='研究主題'><?php echo $area; ?></li>
					<?php   endforeach; ?> 
					<?php endif; ?>  
					
					<?php if($result['@rmethod']): ?>
					<?php   foreach($result['@rmethod'] as $area): ?>
					  <li class='rmethod' title='研究方法'><?php echo $area; ?></li>
					<?php   endforeach; ?> 
					<?php endif; ?>  
					
					
					
					</ul>
					
				  </div>
				  <div class='typeblock'> <?php echo $result['doc_type']; ?> </div>
				  <div class='yearblock'> <?php echo $result['PQ_YearNum']; ?> </div>
				  <div class='ownerblock'>
				    <span class='utime'> <?php echo $result['upload_time']; ?> </span>
					<span class='uuser'> <?php echo $result['upload_user']; ?> </span>
				  </div>
				  
				</div>  
			    <?php endforeach; ?>
			    
				<div id="marker" slot='<?php echo ( isset($server_result['result_num']) && count($server_result['result_data']) < $server_result['result_num']) ? '1' : '-' ;?>' accno='<?php echo $server_result['access_num']; ?>'></div>
			  </div>
			  <div class='archive_content' id='initialPage' style='display:<?php echo $domDisplay['initialPage'];?>'>
			    查無資料，請重新搜尋
				<div class='rslItem' no='sample' >	  
				  
				   <div class='nameblock'>
				    <div class='fileinfo'>
				      <span class='rno' >0.</span>
					  <span class='ricno' ><i class="fa" aria-hidden="true"></i></span>
					  <span class='rtitle' ><div> </div></span>
				    </div>
					<ul class='contenttags'>
					</ul>
				  </div>
				  <div class='typeblock'>  </div>
				  <div class='yearblock'> </div>
				  <div class='ownerblock'>
				    <span class='utime'>   </span>
					<span class='uuser'>  </span>
				  </div>
				</div>
			  </div>
			</div>
		  </div>
		  <div class='info_block'>
		    <div class='info_area'>
			  <i class='mark16 pic_photo_task_alert'></i>
			  <ul id='task_info'></ul>
			</div>
		    <div class='select_option'>
			  <div class='user_select'>
			    <span id='user_selected_count'>0</span>
			    <span class='option' id='act_unselect' title='取消勾選'><i class='mark16 pic_close' ></i></span>
			  </div>
			  
			  <!--
			  <?php if(isset($_SESSION[_SYSTEM_NAME_SHORT]['PERMISSION']['archive-dele']) && intval($_SESSION[_SYSTEM_NAME_SHORT]['PERMISSION']['archive-dele'])!=0 ): ?>
			  <?php endif; ?>
			  <button type='button' id='act_function_key' class='option unselect' title=' 快速鍵'  ><i class='mark26 pic_functionkey' ></i></button> 
			  <button type='button' id='act_export_selected' class='option' title=' 打包下載'  ><i class='mark26 pic_photo_export' ></i></button> 
			  -->
			  
			  <button type='button' id='act_folder_selected' class='option unselect' title=' 加到資料夾'  ><i class='mark26 pic_photo_tofolder' ></i></button> 
			  <button type='button' id='act_edit_selected' class='option unselect' title=' 編輯勾選圖片'  ><i class='mark24 pic_photo_select_edit' ></i></button> 
			  <button type='button' id='act_delete_selected' class='option' title=' 批次刪除'  ><i class='mark26 pic_photo_delete' ></i></button> 
			</div>

			<div class='upload_option'>
			  <button type='button' id='act_open_upload' class='option unselect' title=' 上傳影像 '  ><i class='mark26 pic_photo_upload' ></i></button>
			</div>
		  
		  </div>
		</div>
	  </div>
	</div>
	
	<!-- 框架外結構  -->
	<div class='funckey_area'>
	  <h1> 鍵盤快速鍵 </h1>
	  <ul>
	    <li> <span class='keygroup'>Alt + S</span> : 勾選目前頁面資料 </li>
		<li> <span class='keygroup'>Alt + A + S</span> : 勾選所有已載入影像 </li>
	  </ul>
	</div>
	
	
	<!-- 勾選加到資料夾設定區塊  -->
	<div class='foldersel_area'>
      <div class='header_border'>
	    <h1> 加到資料夾 </h1>
	    <div class='area_option'>
		  <i class='mark16 pci_area_close_x option' id='act_foldersel_close' ></i>
		</div>
	  </div>
	  <div class='folder_setting'>
	    <div class='field_set'>
		  <label>設定要加入的資料夾</label>
		</div>
		<div class='' id='folder_put_templete' >
            <div class='folder_setter' > 
			  <select class='set_folder'>
			    <option value='' selected>新資料夾</option>
				<optgroup label="舊資料夾">
				<?php foreach($user_folders['user'] as $mf):?>
				  <option class='act_folder' value='<?php echo $mf['ufno']; ?>' ><?php echo $mf['name'];?></option>
				<?php endforeach;?>
				</optgroup>
			  </select>
			  <input type='text' class='set_value' value='' >
			  <span class='set_option'><i class='mark24 option pic_meta_field_add' id='act_add_put_folder' ></i></span>
			</div>
		</div>
		<div class='' id='folder_set_pool'>

		</div>
	  </div>
	  <div class='folder_option'>
	    <button type='button' class='cancel' id='act_folder_sel_reset'> 重設 </button>
	    <button type='button' class='active' id='act_folder_sel_active'> 加入 </button>
	  </div>
	</div>
	 
	 
	<!-- 檔案下載設定區塊  -->
	<div class='exportsel_area'>
      <div class='header_border'>
	    <h1> 匯出設定 </h1>
	    <div class='area_option'>
		  <i class='mark16 pci_area_close_x option' id='act_exportsel_close' ></i>
		</div>
	  </div>
	  <div class='export_setting'>
	    <div class='field_set'>
		  <label>匯出說明</label>
		  <textarea class='export_set' id='exp_descrip' name='desc' ></textarea>
		</div>
		<div class='field_set'>
		  <label>匯出類型</label>
		  <input type=radio name='exp_type' class='export_set' value='original' checked> 原始檔案
		  <input type=radio name='exp_type' class='export_set' value='system'> 系統縮圖
		  <input type=radio name='exp_type' class='export_set' value='alltype'> 全部類型
		</div>
	  </div>
	  <div class='export_option'>
	    <button type='button' class='cancel' id='act_reset_sel_exp'> 重設 </button>
	    <button type='button' class='active' id='act_active_sel_exp'> 匯出 </button>
	  </div>
	</div>
	
	
	<!-- 檔案編輯區塊  -->
	<div class='editsel_area'>
      <div class='header_border'>
	    <h1> 編輯選擇資料 </h1>
	    <div class='area_option'>
		  <i class='mark16 pci_area_close_x option' id='act_editsel_close' ></i>
		</div>
	  </div>
	  <div class='meta_setting'>
	    <div class='classify_config'>
		    <div class='field_set'>
			  <label>設定專題</label>
			  <div class='meta_pool _metaval _edit _suggest' id='mdf_classlevel' contenteditable="true"></div>
			  <div class='edit_option' >
			    <input type='radio' name='class_edit_by' value='+' checked> 加入
				<input type='radio' name='class_edit_by' value='-' > 移除
				<input type='checkbox' class='overwrite' name='class_unify' value=1 /> 是否複寫
		        <button type='button' class='active' id='act_upd_sel_cllv' data-update='classlevel'> 更新 </button>
			  </div>
			</div>
            <div class='field_set'>
			  <label>設定標籤</label>
			  <div class='meta_pool _metaval _edit _suggest' id='mdf_tags' value='' contenteditable="true"></div>
			  <div class='edit_option'>
			    <input type='radio' name='tag_edit_by' value='+' checked > 加入
				<input type='radio' name='tag_edit_by' value='-' > 移除
				<input type='checkbox' class='overwrite' name='tag_unify' value=1 /> 是否複寫
		        <button type='button' class='active' id='act_upd_sel_tags' data-update='tags'> 更新 </button>
			  </div>
			</div>		
		</div>
		<div class='meta_config'>
	      <?php if(isset($_SESSION[_SYSTEM_NAME_SHORT]['PERMISSION']['archive-update-fields']) && intval($_SESSION[_SYSTEM_NAME_SHORT]['PERMISSION']['archive-update-fields'])!=0 ): ?>
		  <div class='field_set'>
			  <label>設定其他欄位</label>
		  </div>
		  <div class='' id='meta_set_templete' >
            <div class='meta_setter' > 
			  <select class='set_field'>
			    <option value='' selected></option>
			    <option value='creater'>原作者</option>
				<option value='photo_descrip'>圖說</option>
			    <option value='photo_keywords'>關鍵字</option>
			    <option value='photo_date'>拍攝時間</option>
				<option value='photo_locat'>拍攝地點</option>
			  </select>
			  <input type='text' class='set_value' value='' >
			  <span class='set_option'><i class='mark24 option pic_meta_field_add' id='act_add_mdf_filed' ></i></span>
			</div>
		  </div>
		  <div class='' id='meta_set_pool'>
		  </div>
		  <div class='edit_option'>
		   - <button type='button' class='active' id='act_upd_sel_meta'> 更新 </button>
		  </div>
		  <?php endif; ?>
		</div>
	  </div>
	</div>
	
	
	<!-- 檔案上傳區塊  -->
	<div class='upload_area'>
      <div class='header_border'>
	    <h1> 檔案上傳 </h1>
	    <span id='progress_info'>
		  <span id='num_of_upload' >0</span> -
		  <span id='num_of_queue' >0</span> /
		  <span id='execute_timer' >0</span>
		  <span id='complete_time' > _ </span>
		</span>
		<div class='area_option'>
		  <i class='mark16 pci_area_mini option' ></i>
		  <i class='mark16 pci_area_close_x option' id='act_upload_close' ></i>
		</div>
	  </div>
	  <div class='upload_setting'>
	    <div class='upload_config'>
		    <div class='tr_like'>
			    <div class='field_set colset'><label>提供者</label><input type='text' id='upl_provider'  class='_metaval _edit' value='<?php echo $user_info['user']['user_name']; ?>'    readonly=true  ></div>
			    <div class='field_set colset'><label>原作者</label><input type='text' id='upl_creater'   class='_metaval _edit' value='<?php echo $user_info['user']['user_name']; ?>'   ></div>
			</div>
			<div class='field_set'>
			  <label>專案資料夾</label>
			  <input type='text' class=' _metaval _edit' id='upl_folder' data-cache='' value=''/>
			</div> 
			<div class='field_set'>
			  <label>單位分類</label>
			  <div class='meta_pool _metaval _edit _suggest' id='upl_classlevel' contenteditable="true"></div>
			</div> 
		</div>
		<div class='upload_action'>
		  <button type='button' class='select' id='act_select_file'> 新增檔案 </button>
		  <button type='button' class='active' id='act_active_upload' disabled=true  data-folder=''> 上傳 </button>
		  <button type='button' class='cancel' id='act_clean_upload'> 清空 </button>
		</div>
	  </div>
	  <div class='upload_list ' id='upload_dropzone'></div>
	</div>
	
	<!-- 照片顯示區塊 -->
    <div class='display_area' style='display:none;'>
		<div class='view_header'>
		  <i class='mark32 option pic_area_close ' id='close_setter'></i>
		</div>
		<div class='view_body'>
		  <div class='photo_area'>
			<div class='item_condinter'>
			  <div class='photo_block' id='photo_display' >
			  </div>
			  <!--
			  <div class='photo_change' id='act_photo_prev'><span class='option'><i class='mark16 pic_photo_up'></i></span></div>
			  <div class='photo_change' id='act_photo_next' ><span class='option'><i class='mark16 pic_photo_dw'></i></span></div>
			  -->
			</div>
			<div class='item_information'>
			  <div class='info_set'> <label>檔案類型</label> 
			    <div class='_metaval' data-field='doc_type'></div>
			  </div>
			  <div class='info_set'> <label>上傳資訊</label> 
			    <div class='_metaval' data-field='upload_time'></div>
				<div >by <span class='_metaval' data-field='upload_user'></span></div> 
			  </div>
			  <div class='info_set'> <label>原始檔案</label> 
			    <div class='_metaval' data-field='file_name'></div> 
			    <div ><span class='_metaval' data-field='file_type'></span></div> 
			  </div>
			  <div class='info_set'> 
			    <label>檔案標籤 
				  <i class='mark16 pic_photo_meta_tags option' id='act_edit_tags' title='編輯標籤'></i>
				</label> 
			    <input type='text' class='_meta _metaval _edit' data-field='tags' style='display:none;' />
				<ul class='user_tags'  id='tag_display'></ul>
				<div class='additional' id='tags_editer'>
				  <div class='header_border'>
					<h1> 標籤編輯器 </h1>
					<div class='area_option'>
					  <i class='mark16 pci_area_close_x option' id='act_tags_edit_close' ></i>
					</div>
				  </div>
				  <ul class='user_tags' id='tags_queue'></ul>
				  <div class='tags_select_block'>
				    <div>
					  <input type='text' id='tag_getter' placeholder='輸入篩選條件或直接填寫標籤' > <i class='mark24 option pic_photo_tag_add' id='act_add_new_tags'></i>
				    </div>
					<ul class='tags selecter' id='tags_suggest'></ul >
				    <h2>最近使用的標籤</h2>
					<ul class='tags selecter' id='tags_used'></ul>
				  </div>
				</div>
				
			  </div>
			</div>
		  </div>
		  <div class='meta_area'>
			<div class='photo_data'>
			  <div class='data_set'><label>編號</label>		<span class='_metaval' data-field='identifier' ></span></div>
			  <div class='data_set'><label>更新</label>		<span class='_metaval' data-field='update_date' ></span>  </div>
			  <div class='data_option'>
			    <div>
				  <i class='mark24 pic_photo_checked' id='act_photo_selected' ></i>
				  <a class='sysbtn' id='act_edit_on' ><i class='mark16 pic_photo_meta_edit' ></i></a>
				</div>
				<div class='meta_modify_option'>
				  <a class='sysbtn' id='act_meta_save' ><i class='mark16 pic_photo_meta_save' ></i></a>
				</div>
				<div class='meta_modify_option'>
				  <input type=checkbox value=0 id='act_delete_accept'>
				  <a class='sysbtn' id='act_meta_dele' on="0" ><i class='mark16 pic_delete' ></i></a>
			    </div> 
				
			  </div>
			  
			</div>
			<div class='photo_meta'>
			  <div class='field_set'>
			    <label>標題/副標題</label>
				<input type='text'  class='_meta _metaval _edit' value='' data-field='title_main'  disabled=true  >
				<input type='text'  class='_meta _metaval _edit' value='' data-field='title_second'  disabled=true  >
			  </div>
			  <div class='tr_like'>
			    <div class='field_set colset'><label>檔案類型</label><input type='text' data-field='doc_type'     class='_meta _metaval _edit' value='' disabled=true  ></div>
			    <div class='field_set colset'><label>執行年分</label><input type='text' data-field='PQ_YearNum'   class='_meta _metaval _edit' value='' disabled=true  ></div>
			  </div>
			  <div class='field_set'>
			    <label>專題分類</label>
			    <div class='meta_pool _meta _metaval _edit _suggest' id='meta_classlevel' data-field='classlevel'  contenteditable="false" ></div>
			  </div>
			  <div class='tr_like'>
			    <div class='field_set colset'><label>主管單位</label><input type='text' data-field='organ_main'  class='_meta _metaval _edit' value='' disabled=true  ></div>
			    <div class='field_set colset'><label>承辦單位</label><input type='text' data-field='organ_work'  class='_meta _metaval _edit' value='' disabled=true  ></div>
			  </div>
			  <div class='tr_like'>
			    <div class='field_set colset'><label>執行人</label><input type='text' data-field='execute_person'  class='_meta _metaval _edit' value='' disabled=true  ></div>
			    <div class='field_set colset'><label>連絡人</label><input type='text' data-field='contact_person'  class='_meta _metaval _edit' value='' disabled=true  ></div>
			  </div>
			  <div class='field_set'><label>關鍵字</label><input type='text' class='_meta _metaval _edit'  data-field='keywords' disabled=true /></div>
			  
			  <div class='field_set'><label>研究區域</label><input type='text' class='_meta _metaval _edit'  data-field='research_area' disabled=true /></div>
			  <div class='field_set'><label>研究領域</label><input type='text' class='_meta _metaval _edit'  data-field='research_domain' disabled=true /></div>
			  <div class='field_set'><label>研究方法</label><input type='text' class='_meta _metaval _edit'  data-field='research_method' disabled=true /></div>
			  
			  <div class='field_set'><label>摘要</label><textarea  class='_meta _metaval _edit _autoHeight'  data-field='abstract' disabled=true style='height:5em;'></textarea></div>
			  
			 
			</div>
			<div class='download_area tr_like'>
			  
			  <div class='field_set colset'>
			    <div  class='field_set' id='photo_download_count' >
				  <label> 下載次數 </label>
				  <span class='_metaval' data-field='download'></span>
				</div>
			  </div>
			  <div class='field_set colset'>
			    <button type="button" class='active' id='act_object_download' > 下載 </button>
			  </div>	
			  
			</div>
		  </div>
		</div>
		<!--<div class='view_footer'></div>-->
    </div>
	
	
	<!-- 輸入建議 -->
	<ul class='meta_sugg' id='meta_suggest' support=''></ul>
	
	
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