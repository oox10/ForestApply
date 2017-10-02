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
	<link rel="stylesheet" type="text/css" href="theme/css/css_book_reviewer.css" />
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_admin.js"></script>
	<script type="text/javascript" src="js_book_reviewer.js"></script>
	
	<!-- PHP -->
	<?php
	$user_info 		= isset($this->vars['server']['data']['user']) 		? $this->vars['server']['data']['user'] 	: array('user'=>array('user_name'=>'Anonymous'),'group'=>array());
	
	// 可接觸區域
	$data_area  	= isset($this->vars['server']['data']['areas'] ) 	? $this->vars['server']['data']['areas'] : array();
	 
	// 目前資料
	$data_list  	= isset($this->vars['server']['data']['records']['list']) 	? $this->vars['server']['data']['records']['list'] : array();
	$data_count 	= count($data_list);
	
	
	$data_list  	= isset($this->vars['server']['data']['records']['list']) 	? $this->vars['server']['data']['records']['list'] : array();
	
	
	$data_type  	= isset($this->vars['server']['data']['records']['type']) 	? $this->vars['server']['data']['records']['type'] : array('NP','NR','AP');
	$data_limit  	= isset($this->vars['server']['data']['records']['limit']) 	? $this->vars['server']['data']['records']['limit'] : '';
	$data_filter 	= isset($this->vars['server']['data']['records']['filter']) 	? $this->vars['server']['data']['records']['filter'] : array();
	
	$page_info 		= isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';  
	$user_roles     = array();
	
	
	?>
  </head>
  
  <body>
	<div class='system_main_area'>
	  
	  <div class='system_content_area'>
        <div class='tool_banner' >
		  <h1>
		    <img src='theme/image/mark_forest.png' />
			<div class='system_title'>
			  <?php echo _SYSTEM_HTML_TITLE;?> - 外審人員審查
			</div>
		  </h1>
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
		</div>
		
		<div class='topic_banner'>
		  <div class='record_filter'>
		    <ul class='filter_set' >
			  <li class='filter_group'>
			    <span>
				  <label>申請日期：</label>
				  <input type='text' id='filter_search_terms'  value='<?php echo isset($data_filter['apply_date']) ? $data_filter['apply_date']:''; ?>' placeholder='搜尋申請日期' />
			    </span>
			    <span>
				  <label>進入日期：</label>
				  <span class='input_date' ><input type='text' id='search_date_start' placeholder='日期-起' size='10' value='<?php echo isset($data_filter['range_start']) ? $data_filter['range_start'] : '';  ?>' /><i class="fa fa-calendar" aria-hidden="true"></i></span>
				  <span class='input_date' ><input type='text' id='search_date_end'   placeholder='日期-迄' size='10' value='<?php echo isset($data_filter['range_end']) ? $data_filter['range_end'] : '';  ?>' /><i class="fa fa-calendar" aria-hidden="true"></i></span> 
			    </span>
			  </li>
			</ul>
			<span class='filter_option' >  
			  <button id='filter_submit'  type='button' class='active'><i class="fa fa-search" aria-hidden="true"></i> 篩選 </button> 
			  <a id='reset_filter' class='option' > <i class="fa fa-refresh" aria-hidden="true"></i> 清空條件</a>
			</span>
		  </div> 
		</div>
		
		<div class='main_content' >
		  <!-- 資料列表區 -->
		  <div class='data_record_block' id='record_selecter' >
		    <div class='record_header'>
			  <span class='record_name'>申請清單 / 共 <span> <?php echo $data_count; ?></span>  筆</span>
			  <span class='record_option'>
                <button id='act_batch_accept'  type='button' class='active'> <i class="fa fa-check-square-o" aria-hidden="true"></i> 勾選批次同意申請 </button> 
			  </span>
			</div> 
			<div class='record_body'>
			  <table class='record_list'>
		        <tr class='data_field'>
				  <td title='checker'	><input type='checkbox' class='act_select_all'></td>
			      <td title='編號'		>no.</td>
				  <td title='申請日期'	>申請日期</td>
				  <td title='申請單號'	>申請單號</td>
				  <td title='申請區域'	>申請區域</td>
				  <td title='進入期間'	>進入期間</td>
				  <td title='申請人'	>申請人</td>
				  <td style='text-align:center;' title='人數'		>進入人數</td>
				  <td style='text-align:center;' >外審人員審核</td>
			    </tr>
			    
				<?php foreach($data_list as $i=> $data): ?>  
			    <tbody>  
				  <tr class='data_record ' no='<?php echo $data['r_apply_code'];?>' page='' >
                    <td field=''  ><input type='checkbox' class='act_select_one' value='<?php echo $data['r_apply_code'];?>' ></td>
					<td field=''  ><?php echo $i+1; ?> </td>
					<td field='' 	><?php echo $data['r_apply_date'];   ?></td>
				    <td field='' 	><?php echo $data['r_apply_code'];   ?></td>
				    <td field=''	><?php echo $data['r_apply_area'];   ?></td>
					<td field=''	><?php echo $data['r_apply_period']; ?></td>
					<td field=''	><?php echo $data['r_apply_user'];   ?></td>
				    <td field=''	>共<?php echo $data['r_countmbr'];   ?>人</td>
				    <td rowspan=2>
					  <div class='review_option'>
					    <button type='button' class='cancel act_apply_reject' ><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>不同意申請</button>
					    <button type='button' class='active act_apply_accept' ><i class="fa fa-check" aria-hidden="true"></i>同意申請</button>
					  </div>
					  <hr></hr>
					  <textarea class='apply_reject_reason'  placeholder='請述明不同意之理由' ></textarea>
					  <hr></hr>
					  <label>外審記錄:</label>
					  <ul class='apply_review_logs'>
					  <?php foreach($data['r_reviewlogs'] as $rlog):?>
                        <li >
						  <label><?php echo $rlog['time'];?></label>
						  <div><?php echo $rlog['status'];?></div>
						  <div><?php echo $rlog['note'];?></div>
						</li>
					  <?php endforeach; ?>
					  </ul>
					</td>
				  </tr>
                  <tr class='data_content ' >
                    <td field=''  ></td>
					<td field=''  colspan=7>
					  <h1>進入申請書</h1>
					  <table class='applyform'>
					    <tr><th>申請進入日期：</th><td><?php echo $data['r_apply_period'];?></td></tr>
					    <tr><th>申請進入範圍：</th><td><?php echo join(',',$data['r_application']['area']['inter']);?></td></tr>
					    <tr><th>預計進出入口：</th>
						    <td>
						      <span>預計抵達 : <?php echo $data['r_application']['area']['gate']['entr'];?>(<?php echo $data['r_application']['area']['gate']['entr_time'];?>)</span>
						      ；<span>預計離開 : <?php echo $data['r_application']['area']['gate']['exit'];?>(<?php echo $data['r_application']['area']['gate']['exit_time'];?>)</span>
						    </td>
						</tr>
					    <tr><th>申請理由：</th><td><?php echo  $data['r_application']['reason'][0]['item'];?></td></tr>
					    <tr><th colspan=2>其他申請內容：<th></tr>
						<?php foreach($data['r_application']['fields'] as $form):?>
						<tr><th colspan=2><?php echo $form['field'];?></th></tr>
					    <tr><td colspan=2><?php echo $form['value'];?></td><tr>
						<?php endforeach; ?>
						
					  </table>
					  <h1>成員清單</h1>
					  <table class='applyform'>
					    <tr><th>序號</th><th>角色</th><th>姓名</th><th>性別</th><th>地址</th></tr>
						<?php foreach($data['r_members'] as $i=> $mbr):?>
						<tr>
						  <td><?php echo ($i+1); ?></td>
						  <td><?php echo $mbr['role'];?></td>
						  <td><?php echo $mbr['name'];?></td>
						  <td><?php echo $mbr['sex'];?></td>
						  <td><?php echo $mbr['addr'];?>...</td>
						</tr>
						<?php endforeach; ?>
					  </table>
					</td>
				  </tr>
				
				</tbody>  				  
			    <?php endforeach; ?>
			      <tr class='data_field'>
			        <td title='checker'	><input type='checkbox' class='act_select_all'></td>
					<td title='編號'		>no.</td>
				    <td title='申請日期'	>申請日期</td>
					<td title='申請單號'	>申請單號</td>
					<td title='申請區域'	>申請區域</td>
				    <td title='進入期間'	>進入期間</td>
				    <td title='申請人'	>申請人</td>
				     <td style='text-align:center;' title='人數'		>進入人數</td>
				    <td title='狀態'	style='text-align:center;'>外審人員審核</td>
			      </tr> 
				
			  </table>
			  
		    </div>
		  </div>
		</div>
		
		
		
		
		</div> <!-- end of data_record_block -->
		
		
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